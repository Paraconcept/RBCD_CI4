<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\BarRegister;
use App\Libraries\BarRegisterException;
use App\Models\BarServiceModel;

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
     * Les deux modes d'emploi en PDF : celui du comité (admin) et celui du barman (caisse, imprimé et posé près de la tablette).
     * Sources et génération : docs-sources/caisse (voir docs-sources/README.md).
     */
    public function howto(string $which)
    {
        $file = $which === 'caisse' ? 'Caisse_RBCD_mode_emploi_barman.pdf' : 'Caisse_RBCD_guide_admin.pdf';
        $path = APPPATH . 'Docs/' . $file;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $file . '"')
            ->setBody(file_get_contents($path));
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
