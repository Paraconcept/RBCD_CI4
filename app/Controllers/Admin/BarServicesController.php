<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\BarRegister;
use App\Libraries\BarRegisterException;
use App\Models\BarServiceModel;
use App\Models\BarTicketLineModel;
use App\Models\BarTicketModel;
use App\Models\BarTicketPaymentModel;

/**
 * Services de la caisse (un par enveloppe) : chiffres de chaque service et récapitulatif imprimable.
 */
class BarServicesController extends BaseController
{
    public function index(): string
    {
        $model = new BarServiceModel();
        $years = array_map('intval', array_column(
            db_connect()->query('SELECT DISTINCT YEAR(service_date) AS y FROM bar_services ORDER BY y DESC')->getResultArray(), 'y'
        ));
        $year = (int) ($this->request->getGet('year') ?: date('Y'));
        if (!in_array((int) date('Y'), $years, true)) {
            array_unshift($years, (int) date('Y'));
        }

        $services = $model->getForAdmin($year);
        $register = new BarRegister();
        // Un service encore ouvert n'a pas ses totaux figés : on les calcule à la volée
        foreach ($services as $s) {
            if ($s->status === 'open') {
                $sum = $register->summary((int) $s->id);
                $s->paid_count       = $sum['paid'];
                $s->cancelled_count  = $sum['cancelled'];
                $s->cash_total       = $sum['cash'];
                $s->bancontact_total = $sum['bancontact'];
                $s->open_tickets     = $sum['open'];
            }
        }

        return view('admin/bar_services/index', [
            'title'       => 'Caisse — Services',
            'breadcrumbs' => [['title' => 'Caisse'], ['title' => 'Services']],
            'services'    => $services,
            'years'       => $years,
            'year'        => $year,
            'lastId'      => $register->lastServiceId(),
        ]);
    }

    /**
     * Les modes d'emploi en PDF : celui du comité (admin), celui du barman (caisse) et sa version pas à pas en grands caractères,
     * imprimés et posés près de la tablette.
     * Sources et génération : docs-sources/caisse (voir docs-sources/README.md).
     */
    public function howto(string $which)
    {
        $file = match ($which) {
            'caisse'     => 'Caisse_RBCD_mode_emploi_barman.pdf',
            'pas-a-pas'  => 'Caisse_RBCD_pas_a_pas.pdf',
            default      => 'Caisse_RBCD_guide_admin.pdf',
        };
        $path = APPPATH . 'Docs/' . $file;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $file . '"')
            ->setBody(file_get_contents($path));
    }

    /** Tickets d'un service, pour vérifier et corriger un paiement mal encodé. */
    public function show(int $id): string
    {
        $service = (new BarServiceModel())->getWithNames($id);
        if (!$service) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $tickets = (new BarTicketModel())->forService($id);
        $ids     = array_map(static fn ($t) => (int) $t->id, $tickets);
        $correctors = [];
        $byIds = array_filter(array_unique(array_map(static fn ($t) => (int) $t->payment_corrected_by_member_id, $tickets)));
        if ($byIds) {
            foreach (db_connect()->table('members')->select('id, first_name, last_name')->whereIn('id', $byIds)->get()->getResultObject() as $m) {
                $correctors[(int) $m->id] = mb_strtoupper($m->last_name, 'UTF-8') . ' ' . $m->first_name;
            }
        }

        return view('admin/bar_services/show', [
            'title'       => 'Caisse — Service ' . $service->envelope_code,
            'breadcrumbs' => [['title' => 'Caisse'], ['title' => 'Services', 'url' => base_url('admin/caisse/services?year=' . substr($service->service_date, 0, 4))], ['title' => $service->envelope_code]],
            'service'     => $service,
            'summary'     => (new BarRegister())->summary($id),
            'tickets'     => $tickets,
            'lines'       => (new BarTicketLineModel())->forTickets($ids),
            'payments'    => (new BarTicketPaymentModel())->forTickets($ids),
            'correctors'  => $correctors,
        ]);
    }

    /** Correction du mode de paiement d'un ticket payé (parts en espèces / Bancontact) ; recalcule les totaux d'un service clôturé. */
    public function correctPayment(int $ticketId)
    {
        $methods = (array) $this->request->getPost('method');
        $amounts = (array) $this->request->getPost('amount');
        $parts   = [];
        foreach ($methods as $i => $m) {
            $parts[] = ['method' => $m, 'amount' => str_replace(',', '.', trim((string) ($amounts[$i] ?? '')))];
        }
        try {
            $ticket = (new BarRegister())->correctPayment($ticketId, $parts, (int) session()->get('member_id') ?: null);
        } catch (BarRegisterException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('admin/caisse/services/' . $ticket->service_id) . '#t' . $ticket->id)
            ->with('success', 'Paiement du ticket n° ' . BarTicketModel::formatNumber((int) $ticket->number) . ' corrigé : '
                . BarTicketPaymentModel::describe((new BarTicketPaymentModel())->where('ticket_id', $ticketId)->findAll()) . '.');
    }

    /** Clôture d'un service oublié, par un membre du comité. */
    public function close(int $id)
    {
        try {
            $service = (new BarRegister())->closeService($id, (int) session()->get('member_id') ?: null);
        } catch (BarRegisterException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('admin/caisse/services'))->with('success', 'Service ' . $service->envelope_code . ' clôturé.');
    }

    /** Suppression d'un service d'essai (démonstration, test d'une tablette). */
    public function delete(int $id)
    {
        try {
            $service = (new BarRegister())->deleteService($id);
        } catch (BarRegisterException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('admin/caisse/services?year=' . substr($service->service_date, 0, 4)))
            ->with('success', 'Service ' . $service->envelope_code . ' supprimé, avec ses tickets.');
    }
}
