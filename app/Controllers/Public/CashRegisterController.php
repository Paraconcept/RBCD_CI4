<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Libraries\BarRegister;
use App\Libraries\BarRegisterException;
use App\Models\BarCategoryModel;
use App\Models\BarClientModel;
use App\Models\BarProductModel;
use App\Models\BarServiceModel;
use App\Models\BarTicketLineModel;
use App\Models\BarTicketModel;
use App\Models\MemberKeyModel;
use App\Models\ScheduleBarDutyModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Caisse tactile du bar : une seule page pilotée en JavaScript (caisse.js) et une petite API JSON.
 * Accès : membres du comité connectés et appareils du club autorisés (ScoreboardDeviceFilter), comme le marquoir.
 */
class CashRegisterController extends BaseController
{
    // Un ticket ouvert depuis plus longtemps est signalé dans « Tickets en cours »
    public const LATE_AFTER_MINUTES = 60;

    // Pas encore d'imprimante à tickets (80 mm) au bar : les boutons d'impression du ticket client restent grisés
    public const TICKET_PRINTER = false;

    private BarRegister $register;
    private BarServiceModel $services;
    private BarTicketModel $tickets;

    public function __construct()
    {
        $this->register = new BarRegister();
        $this->services = new BarServiceModel();
        $this->tickets  = new BarTicketModel();
    }

    public function index(): string
    {
        $products = (new BarProductModel())->getActiveByCategory();
        $catalog  = [];
        foreach ((new BarCategoryModel())->getActive() as $c) {
            $catalog[] = [
                'id'       => (int) $c->id,
                'name'     => $c->name,
                'products' => array_map(static fn ($p) => [
                    'id' => (int) $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'position' => (int) $p->position,
                ], $products[(int) $c->id] ?? []),
            ];
        }

        return view('public/cash_register/index', [
            'config' => [
                'catalog'     => $catalog,
                'slots'       => BarProductModel::SLOTS,
                'lateAfter'   => self::LATE_AFTER_MINUTES,
                'ticketPrinter' => self::TICKET_PRINTER,
                'eveningFrom' => BarServiceModel::EVENING_FROM,
                'openingFloat' => BarServiceModel::OPENING_FLOAT,
                'api'         => base_url('caisse/api/'),
                'print'       => base_url('caisse/'),
                'bar'         => [
                    'state'       => base_url('marquoir/api/bar'),
                    'serve'       => base_url('marquoir/api/bar/servi/'),
                    'orangeAfter' => BarController::ORANGE_AFTER,
                    'redAfter'    => BarController::RED_AFTER,
                    'beepEvery'   => BarController::BEEP_EVERY,
                ],
            ],
        ]);
    }

    // ------------------------------------------------------------------ API

    public function state()
    {
        $service = $this->services->getOpen();
        $data    = ['nowTs' => time(), 'service' => null, 'tickets' => [], 'nextNumber' => $this->nextNumberPreview()];

        if ($service) {
            $data['service'] = $this->serviceJson($service);
            $open  = $this->tickets->forService((int) $service->id, ['open']);
            $lines = (new BarTicketLineModel())->forTickets(array_map(static fn ($t) => (int) $t->id, $open));
            $data['tickets'] = array_map(fn ($t) => $this->ticketJson($t, $lines[(int) $t->id] ?? []), $open);
        } else {
            $data['nextEnvelope'] = BarServiceModel::envelopeCode(date('Y-m-d'), $this->services->nextNumberFor(date('Y-m-d')));
            $last = $this->services->getLastClosed();
            $data['lastClosed'] = $last ? [
                'envelope'  => $last->envelope_code,
                'label'     => BarServiceModel::periodLabel($last->period),
                'date'      => $last->service_date,
                'closedAt'  => substr((string) $last->closed_at, 11, 5),
                'closer'    => $last->closer_name ? mb_strtoupper($last->closer_name, 'UTF-8') : null,
                'today'     => $last->service_date === date('Y-m-d'),
            ] : null;
        }

        return $this->response->setJSON($data);
    }

    public function clients()
    {
        $rows = (new BarClientModel())->search((string) $this->request->getGet('q'));

        return $this->response->setJSON(array_map(static fn ($c) => [
            'id'       => (int) $c->id,
            'name'     => $c->name,
            'member'   => $c->member_id !== null,
            'lastSeen' => $c->last_visit_at ? substr((string) $c->last_visit_at, 0, 10) : null,
        ], $rows));
    }

    /**
     * Avant de créer un nouveau client : clients qui lui ressemblent, nom remis dans l'ordre « NOM PRÉNOM »
     * s'il a été tapé à l'envers (ou les deux ordres si on ne peut pas savoir), et nom d'un seul mot. La caisse demande alors confirmation au barman.
     */
    public function checkClient()
    {
        $model = new BarClientModel();
        $name  = BarClientModel::normalize((string) $this->request->getGet('name'));
        $swap  = $model->nameFirst($name);
        $other = $swap === null ? $model->otherOrder($name) : null;
        $suspects = array_map(static fn ($c) => [
            'id'     => (int) $c->id,
            'name'   => $c->name,
            'member' => $c->member_id !== null,
            'reason' => $c->reason,
        ], $model->suspects($name));

        return $this->response->setJSON([
            'suspects' => $suspects,
            // Inutile de proposer de créer « DUPONT JEAN » s'il est déjà dans les clients proposés
            'swapped'  => $swap !== null && !in_array($swap, array_column($suspects, 'name'), true) ? $swap : null,
            // Ordre ambigu (« PIERRE MARTIN ») : le barman choisit lequel est le nom de famille
            'ambiguous' => $other !== null && !in_array($other, array_column($suspects, 'name'), true) ? $other : null,
            'single'   => !str_contains($name, ' '),
        ]);
    }

    /** Barmen possibles : détenteurs d'une clé ; celui du planning des permanences en premier. */
    public function bartenders()
    {
        $period  = $this->request->getGet('period') === 'am' ? 'am' : 'soir';
        $planned = (new ScheduleBarDutyModel())->getForDates([date('Y-m-d')])[date('Y-m-d')][$period] ?? null;
        $planId  = $planned && $planned->member_id ? (int) $planned->member_id : null;

        $list = [];
        foreach ((new MemberKeyModel())->getActiveKeyHolders() as $m) {
            $list[] = ['id' => (int) $m->id, 'name' => BarClientModel::normalize($m->last_name . ' ' . $m->first_name), 'planned' => (int) $m->id === $planId];
        }
        usort($list, static fn ($a, $b) => [$b['planned'], $a['name']] <=> [$a['planned'], $b['name']]);

        return $this->response->setJSON($list);
    }

    public function openService()
    {
        return $this->run(fn () => ['service' => $this->serviceJson(
            $this->register->openService((string) $this->input('period'), (int) $this->input('member_id'),
                is_numeric($this->input('opening_float')) ? (float) $this->input('opening_float') : null)
        )]);
    }

    public function summary()
    {
        $service = $this->services->getOpen();
        if (!$service) {
            return $this->fail('Aucun service ouvert.');
        }
        return $this->response->setJSON(['service' => $this->serviceJson($service), 'summary' => $this->register->summary((int) $service->id)]);
    }

    public function closeService()
    {
        $service = $this->services->getOpen();
        if (!$service) {
            return $this->fail('Aucun service ouvert.');
        }
        $closerId = (int) $this->input('member_id');
        if (!$closerId) {
            return $this->fail('Choisissez dans la liste la personne qui clôture.');
        }
        if (!is_numeric($this->input('closing_float'))) {
            return $this->fail('Comptez le fond de caisse et encodez le montant trouvé.');
        }
        return $this->run(fn () => ['service' => $this->serviceJson(
            $this->register->closeService((int) $service->id, $closerId, (float) $this->input('closing_float'))
        )]);
    }

    public function history()
    {
        $service = $this->services->getOpen();
        if (!$service) {
            return $this->fail('Aucun service ouvert.');
        }
        $done  = $this->tickets->forService((int) $service->id, ['paid', 'cancelled']);
        $lines = (new BarTicketLineModel())->forTickets(array_map(static fn ($t) => (int) $t->id, $done));

        return $this->response->setJSON(['tickets' => array_reverse(array_map(fn ($t) => $this->ticketJson($t, $lines[(int) $t->id] ?? []), $done))]);
    }

    public function createTicket()
    {
        $service = $this->services->getOpen();
        if (!$service) {
            return $this->fail('Aucun service ouvert : ouvrez d\'abord le service.');
        }
        $clientId = (int) $this->input('client_id') ?: null;

        return $this->run(fn () => ['ticket' => $this->ticketJson(
            $this->register->createTicket((int) $service->id, $clientId, (string) $this->input('name')), []
        )]);
    }

    public function changeLine(int $ticketId)
    {
        return $this->run(fn () => ['ticket' => $this->fullTicket(
            $this->register->changeLine($ticketId, (int) $this->input('product_id'), (int) $this->input('delta'))
        )]);
    }

    public function pay(int $ticketId)
    {
        return $this->run(fn () => ['ticket' => $this->fullTicket($this->register->pay($ticketId, (string) $this->input('method')))]);
    }

    public function cancel(int $ticketId)
    {
        return $this->run(fn () => ['ticket' => $this->fullTicket($this->register->cancel($ticketId, (string) $this->input('reason')))]);
    }

    // ------------------------------------------------------------------ impressions

    /** Récapitulatif A4 à glisser dans l'enveloppe (imprimante Wi-Fi du club). */
    public function serviceRecap(int $serviceId): string
    {
        $service = $this->services->getWithNames($serviceId);
        if (!$service) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('public/cash_register/recap', [
            'service' => $service,
            'summary' => $this->register->summary($serviceId),
            'sales'   => $this->register->salesByProduct($serviceId),
            'tickets' => $this->tickets->forService($serviceId),
        ]);
    }

    /** Ticket client, imprimé seulement à la demande. */
    public function ticketPrint(int $ticketId): string
    {
        $ticket = $this->tickets->withClient($ticketId);
        if (!$ticket) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('public/cash_register/ticket', [
            'ticket'  => $ticket,
            'lines'   => (new BarTicketLineModel())->where('ticket_id', $ticketId)->orderBy('id')->findAll(),
            'service' => $this->services->getWithNames((int) $ticket->service_id),
        ]);
    }

    // ------------------------------------------------------------------ outils

    private function input(string $key): mixed
    {
        $json = $this->request->getJSON(true);
        return is_array($json) && array_key_exists($key, $json) ? $json[$key] : $this->request->getPost($key);
    }

    private function run(callable $action)
    {
        try {
            return $this->response->setJSON($action());
        } catch (BarRegisterException $e) {
            return $this->fail($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', '[Caisse] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erreur inattendue : réessayez. Si le problème persiste, prévenez le comité.']);
        }
    }

    private function fail(string $message)
    {
        return $this->response->setStatusCode(422)->setJSON(['error' => $message]);
    }

    private function nextNumberPreview(): string
    {
        return BarTicketModel::formatNumber($this->tickets->nextNumber(false));
    }

    private function serviceJson(object $s): array
    {
        return [
            'id'        => (int) $s->id,
            'date'      => $s->service_date,
            'period'    => $s->period,
            'label'     => BarServiceModel::periodLabel($s->period),
            'envelope'  => $s->envelope_code,
            'status'    => $s->status,
            'bartender' => $s->bartender_name ? mb_strtoupper($s->bartender_name, 'UTF-8') : null,
            'bartenderId' => $s->bartender_member_id ? (int) $s->bartender_member_id : null,
            'closer'    => $s->closer_name ? mb_strtoupper($s->closer_name, 'UTF-8') : null,
            'openedAt'  => substr((string) $s->opened_at, 11, 5),
            'closedAt'  => $s->closed_at ? substr((string) $s->closed_at, 11, 5) : null,
        ];
    }

    private function fullTicket(object $t): array
    {
        return $this->ticketJson($t, (new BarTicketLineModel())->where('ticket_id', $t->id)->orderBy('id')->findAll());
    }

    private function ticketJson(object $t, array $lines): array
    {
        return [
            'id'          => (int) $t->id,
            'number'      => BarTicketModel::formatNumber((int) $t->number),
            'client'      => $t->client_name,
            'status'      => $t->status,
            'method'      => $t->payment_method,
            'total'       => (float) $t->total,
            'openedAt'    => substr((string) $t->opened_at, 11, 5),
            'openedTs'    => strtotime((string) $t->opened_at),
            'paidAt'      => $t->paid_at ? substr((string) $t->paid_at, 11, 5) : null,
            'cancelledAt' => $t->cancelled_at ? substr((string) $t->cancelled_at, 11, 5) : null,
            'reason'      => $t->cancel_reason,
            'lines'       => array_map(static fn ($l) => [
                'productId' => $l->product_id !== null ? (int) $l->product_id : null,
                'name'      => $l->product_name,
                'price'     => (float) $l->unit_price,
                'qty'       => (int) $l->quantity,
            ], $lines),
        ];
    }
}
