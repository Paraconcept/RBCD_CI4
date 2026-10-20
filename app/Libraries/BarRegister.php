<?php

namespace App\Libraries;

use App\Models\BarClientModel;
use App\Models\BarProductModel;
use App\Models\BarServiceModel;
use App\Models\BarTicketLineModel;
use App\Models\BarTicketModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Règles de la caisse du bar. Un service = une enveloppe ; une fois clôturé, plus rien n'y change.
 * Les tickets ne sont jamais supprimés (annulés et tracés) et leurs numéros se suivent sans trou ;
 * seule exception : le dernier service, effacé en entier s'il n'était qu'un essai.
 * Chaque erreur prévue est une BarRegisterException au message prêt à afficher au barman.
 */
class BarRegister
{
    private BaseConnection $db;
    private BarServiceModel $services;
    private BarTicketModel $tickets;
    private BarTicketLineModel $lines;
    private BarClientModel $clients;
    private BarProductModel $products;

    public function __construct()
    {
        $this->db       = db_connect();
        $this->services = new BarServiceModel();
        $this->tickets  = new BarTicketModel();
        $this->lines    = new BarTicketLineModel();
        $this->clients  = new BarClientModel();
        $this->products = new BarProductModel();
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    // ------------------------------------------------------------------ services

    public function openService(string $period, int $bartenderId, ?string $date = null): object
    {
        if (!isset(BarServiceModel::PERIODS[$period])) {
            throw new BarRegisterException('Choisissez le service : après-midi ou soir.');
        }
        if (!$this->db->table('members')->where('id', $bartenderId)->countAllResults()) {
            throw new BarRegisterException('Choisissez le barman dans la liste.');
        }

        $open = $this->services->getOpen();
        if ($open) {
            throw new BarRegisterException('Le ' . BarServiceModel::periodLabel($open->period) . ' du ' . date('d/m/Y', strtotime($open->service_date))
                . ' (' . $open->envelope_code . ') n\'est pas clôturé : clôturez-le avant d\'en ouvrir un autre.');
        }

        $date   = $date ?? date('Y-m-d');
        $number = $this->services->nextNumberFor($date);
        if ($number > BarServiceModel::MAX_PER_DAY) {
            throw new BarRegisterException('Déjà ' . BarServiceModel::MAX_PER_DAY . ' services aujourd\'hui : impossible d\'en ouvrir un de plus.');
        }

        try {
            $id = $this->services->insert([
                'service_date'        => $date,
                'period'              => $period,
                'number'              => $number,
                'envelope_code'       => BarServiceModel::envelopeCode($date, $number),
                'status'              => 'open',
                'bartender_member_id' => $bartenderId,
                'opened_at'           => self::now(),
            ]);
        } catch (\Throwable $e) {
            // Index unique « un seul service ouvert » : une autre tablette a ouvert un service au même instant
            throw new BarRegisterException('Un service vient d\'être ouvert sur un autre appareil. Rechargez la page.');
        }

        return $this->services->getWithNames((int) $id);
    }

    /** Chiffres du service, recalculés à partir des tickets (écran de clôture et récapitulatif). */
    public function summary(int $serviceId): array
    {
        $byStatus = $this->db->table('bar_tickets')
            ->select('status, payment_method, COUNT(*) AS n, COALESCE(SUM(total), 0) AS amount, MIN(opened_at) AS first_at, MAX(COALESCE(paid_at, cancelled_at, opened_at)) AS last_at')
            ->where('service_id', $serviceId)
            ->groupBy(['status', 'payment_method'])
            ->get()->getResultObject();

        $s = ['open' => 0, 'paid' => 0, 'cancelled' => 0, 'cash' => 0.0, 'cash_count' => 0, 'bancontact' => 0.0, 'bancontact_count' => 0,
              'first_at' => null, 'last_at' => null];
        foreach ($byStatus as $r) {
            $s[$r->status] += (int) $r->n;
            if ($r->status === 'paid') {
                $s[$r->payment_method] += (float) $r->amount;
                $s[$r->payment_method . '_count'] += (int) $r->n;
            }
            $s['first_at'] = $s['first_at'] === null ? $r->first_at : min($s['first_at'], $r->first_at);
            $s['last_at']  = max((string) $s['last_at'], (string) $r->last_at) ?: null;
        }
        $s['total'] = round($s['cash'] + $s['bancontact'], 2);

        $vat = $this->db->table('bar_ticket_lines l')
            ->select('l.vat_group, SUM(l.unit_price * l.quantity) AS amount')
            ->join('bar_tickets t', 't.id = l.ticket_id')
            ->where('t.service_id', $serviceId)->where('t.status', 'paid')
            ->groupBy('l.vat_group')
            ->get()->getResultObject();
        $s['vat_b'] = $s['vat_g'] = 0.0;
        foreach ($vat as $v) {
            $s['vat_' . strtolower($v->vat_group)] = round((float) $v->amount, 2);
        }

        $range = $this->db->table('bar_tickets')->select('MIN(number) AS lo, MAX(number) AS hi')->where('service_id', $serviceId)->get()->getRowObject();
        $s['first_number'] = $range->lo ? (int) $range->lo : null;
        $s['last_number']  = $range->hi ? (int) $range->hi : null;

        return $s;
    }

    /** Ventes payées du service par produit, dans l'ordre des catégories. */
    public function salesByProduct(int $serviceId): array
    {
        return $this->db->table('bar_ticket_lines l')
            ->select("l.product_name, l.unit_price, l.vat_group, SUM(l.quantity) AS quantity, SUM(l.unit_price * l.quantity) AS amount, COALESCE(c.name, 'Divers') AS category", false)
            ->join('bar_tickets t', 't.id = l.ticket_id')
            ->join('bar_products p', 'p.id = l.product_id', 'left')
            ->join('bar_categories c', 'c.id = p.category_id', 'left')
            ->where('t.service_id', $serviceId)->where('t.status', 'paid')
            ->groupBy(['l.product_name', 'l.unit_price', 'l.vat_group', 'c.name', 'c.sort_order'])
            ->orderBy('c.sort_order')->orderBy('l.product_name')
            ->get()->getResultObject();
    }

    public function closeService(int $serviceId, ?int $closerId = null): object
    {
        $service = $this->openServiceOrFail($serviceId);

        $this->db->transStart();
        $s = $this->summary($serviceId);
        if ($s['open'] > 0) {
            $this->db->transRollback();
            throw new BarRegisterException($s['open'] === 1
                ? 'Il reste 1 ticket ouvert : réglez-le ou annulez-le avant de clôturer.'
                : 'Il reste ' . $s['open'] . ' tickets ouverts : réglez-les ou annulez-les avant de clôturer.');
        }
        $this->services->update($serviceId, [
            'status'              => 'closed',
            'closed_at'           => self::now(),
            'closed_by_member_id' => $closerId ?: $service->bartender_member_id,
            'paid_count'          => $s['paid'],
            'cancelled_count'     => $s['cancelled'],
            'cash_total'          => $s['cash'],
            'bancontact_total'    => $s['bancontact'],
            'vat_b_total'         => $s['vat_b'],
            'vat_g_total'         => $s['vat_g'],
        ]);
        $this->db->transComplete();

        return $this->services->getWithNames($serviceId);
    }

    /** Dernier service enregistré : le seul qu'on peut supprimer. */
    public function lastServiceId(): ?int
    {
        $row = $this->services->selectMax('id')->first();
        return $row && $row->id ? (int) $row->id : null;
    }

    /**
     * Efface un service d'essai avec ses tickets, ainsi que les clients créés pour l'occasion.
     * Seulement le dernier service : les numéros de ticket et d'enveloppe repartent alors sans trou.
     */
    public function deleteService(int $serviceId): object
    {
        $service = $this->services->getWithNames($serviceId);
        if (!$service) {
            throw new BarRegisterException('Service introuvable.');
        }
        if ($serviceId !== $this->lastServiceId()) {
            throw new BarRegisterException('Seul le dernier service peut être supprimé : supprimez d\'abord les services plus récents.');
        }

        $ticketIds = array_map('intval', array_column(
            $this->db->table('bar_tickets')->select('id')->where('service_id', $serviceId)->get()->getResultArray(), 'id'
        ));
        $clientIds = array_map('intval', array_column(
            $this->db->table('bar_tickets')->distinct()->select('client_id')->where('service_id', $serviceId)->get()->getResultArray(), 'client_id'
        ));

        $this->db->transStart();
        if ($ticketIds) {
            $this->db->table('bar_ticket_lines')->whereIn('ticket_id', $ticketIds)->delete();
            $this->db->table('bar_tickets')->whereIn('id', $ticketIds)->delete();
        }
        $this->db->table('bar_services')->where('id', $serviceId)->delete();

        foreach ($clientIds as $clientId) {
            $client = $this->clients->find($clientId);
            if (!$client) {
                continue;
            }
            $last = $this->db->table('bar_tickets')->selectMax('opened_at')->where('client_id', $clientId)->get()->getRowObject();
            $lastVisit = $last->opened_at ?? null;
            // Un nom tapé pendant l'essai, qui n'a servi qu'à lui, disparaît ; un membre reste, avec sa vraie dernière visite
            if ($lastVisit === null && $client->member_id === null && $client->created_at >= $service->opened_at) {
                $this->clients->delete($clientId);
            } else {
                $this->clients->update($clientId, ['last_visit_at' => $lastVisit]);
            }
        }
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new BarRegisterException('La suppression a échoué : rien n\'a été effacé.');
        }

        return $service;
    }

    // ------------------------------------------------------------------ tickets

    /** Ouvre un ticket au nom d'un client existant ($clientId) ou d'un nouveau client ($name). */
    public function createTicket(int $serviceId, ?int $clientId, ?string $name = null): object
    {
        $this->openServiceOrFail($serviceId);

        if ($clientId) {
            $client = $this->clients->find($clientId);
        } elseif (mb_strlen(BarClientModel::normalize((string) $name)) >= 2) {
            $client = $this->clients->findOrCreate((string) $name);
        } else {
            $client = null;
        }
        if (!$client) {
            throw new BarRegisterException('Choisissez un client ou tapez son nom (2 lettres au moins).');
        }

        $now = self::now();
        $this->db->transStart();
        $number = $this->tickets->nextNumber();
        $id = $this->tickets->insert([
            'number'     => $number,
            'service_id' => $serviceId,
            'client_id'  => $client->id,
            'status'     => 'open',
            'total'      => 0,
            'opened_at'  => $now,
            'updated_at' => $now,
        ]);
        $this->clients->update($client->id, ['last_visit_at' => $now]);
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new BarRegisterException('Le ticket n\'a pas pu être créé. Réessayez.');
        }

        return $this->tickets->withClient((int) $id);
    }

    /** Ajoute (+1) ou retire (−1) un produit ; une ligne tombée à zéro disparaît du ticket ouvert. */
    public function changeLine(int $ticketId, int $productId, int $delta): object
    {
        $ticket = $this->openTicketOrFail($ticketId);
        $delta  = $delta >= 0 ? 1 : -1;

        $this->db->transStart();
        $line = $this->lines->where('ticket_id', $ticketId)->where('product_id', $productId)->first();
        if ($line) {
            $qty = (int) $line->quantity + $delta;
            if ($qty <= 0) {
                $this->lines->delete($line->id);
            } else {
                $this->lines->update($line->id, ['quantity' => $qty]);
            }
        } elseif ($delta > 0) {
            $product = $this->products->find($productId);
            if (!$product || !$product->is_active) {
                $this->db->transRollback();
                throw new BarRegisterException('Ce produit n\'est plus en vente.');
            }
            $this->lines->insert([
                'ticket_id'    => $ticketId,
                'product_id'   => $productId,
                'product_name' => $product->name,
                'unit_price'   => $product->price,
                'vat_group'    => $product->vat_group,
                'quantity'     => 1,
            ]);
        }
        $this->refreshTotal($ticketId);
        $this->db->transComplete();

        return $this->tickets->withClient((int) $ticket->id);
    }

    public function pay(int $ticketId, string $method): object
    {
        $this->openTicketOrFail($ticketId);
        if (!in_array($method, ['cash', 'bancontact'], true)) {
            throw new BarRegisterException('Choisissez le mode de paiement : espèces ou Bancontact.');
        }
        if (!$this->lines->where('ticket_id', $ticketId)->countAllResults()) {
            throw new BarRegisterException('Le ticket est vide : ajoutez une consommation ou annulez-le.');
        }

        $this->db->transStart();
        $this->refreshTotal($ticketId);
        $this->tickets->update($ticketId, ['status' => 'paid', 'payment_method' => $method, 'paid_at' => self::now(), 'updated_at' => self::now()]);
        $this->db->transComplete();

        return $this->tickets->withClient($ticketId);
    }

    /** Annulation tracée ; l'opérateur est le barman du service. */
    public function cancel(int $ticketId, ?string $reason = null): object
    {
        $ticket  = $this->openTicketOrFail($ticketId);
        $service = $this->services->find($ticket->service_id);
        $reason  = trim((string) $reason);

        $this->tickets->update($ticketId, [
            'status'                 => 'cancelled',
            'cancelled_at'           => self::now(),
            'cancelled_by_member_id' => $service->bartender_member_id,
            'cancel_reason'          => $reason !== '' ? mb_substr($reason, 0, 255) : null,
            'updated_at'             => self::now(),
        ]);

        return $this->tickets->withClient($ticketId);
    }

    private function refreshTotal(int $ticketId): void
    {
        $row = $this->db->table('bar_ticket_lines')->select('COALESCE(SUM(unit_price * quantity), 0) AS total')->where('ticket_id', $ticketId)->get()->getRowObject();
        $this->tickets->update($ticketId, ['total' => round((float) $row->total, 2), 'updated_at' => self::now()]);
    }

    private function openServiceOrFail(int $serviceId): object
    {
        $service = $this->services->find($serviceId);
        if (!$service) {
            throw new BarRegisterException('Service introuvable.');
        }
        if ($service->status !== 'open') {
            throw new BarRegisterException('Ce service est clôturé : plus aucun ticket ne peut y être ajouté ou modifié.');
        }
        return $service;
    }

    private function openTicketOrFail(int $ticketId): object
    {
        $ticket = $this->tickets->find($ticketId);
        if (!$ticket) {
            throw new BarRegisterException('Ticket introuvable.');
        }
        $this->openServiceOrFail((int) $ticket->service_id);
        if ($ticket->status !== 'open') {
            throw new BarRegisterException('Le ticket n° ' . BarTicketModel::formatNumber((int) $ticket->number)
                . ($ticket->status === 'paid' ? ' est déjà réglé.' : ' est annulé.'));
        }
        return $ticket;
    }
}
