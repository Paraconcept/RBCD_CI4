<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BarProductModel;
use App\Models\BarTicketModel;
use App\Models\SiteSettingModel;

/**
 * Réglages de la caisse du bar : numéro du premier ticket, pour continuer la numérotation des tickets papier,
 * et photos des produits sur les touches.
 */
class BarSettingsController extends BaseController
{
    private const MAX_NUMBER = 99999999;

    public function index(): string
    {
        $tickets = new BarTicketModel();

        return view('admin/bar_settings/index', [
            'title'       => 'Caisse — Paramètres',
            'breadcrumbs' => [['title' => 'Caisse'], ['title' => 'Paramètres']],
            'start'       => BarTicketModel::startNumber(),
            'last'        => $tickets->lastNumber(),
            'next'        => $tickets->nextNumber(false),
            'showImages'  => BarProductModel::showImages(),
        ]);
    }

    public function save()
    {
        $raw = trim((string) $this->request->getPost('ticket_start'));
        if (!ctype_digit($raw) || (int) $raw < 1 || (int) $raw > self::MAX_NUMBER) {
            return redirect()->back()->withInput()->with('error', 'Le numéro du premier ticket doit être un nombre entier entre 1 et ' . number_format(self::MAX_NUMBER, 0, ',', '.') . '.');
        }

        $start = (int) $raw;
        (new SiteSettingModel())->setSetting(BarTicketModel::START_SETTING, $start);

        $tickets = new BarTicketModel();
        $last    = $tickets->lastNumber();
        $next    = BarTicketModel::formatNumber($tickets->nextNumber(false));
        if ($start <= $last) {
            return redirect()->to(base_url('admin/caisse/parametres'))->with('warning',
                'Enregistré, mais les numéros jusqu\'à ' . BarTicketModel::formatNumber($last) . ' sont déjà utilisés : le prochain ticket sera le n° ' . $next . '.');
        }

        return redirect()->to(base_url('admin/caisse/parametres'))->with('success', 'Enregistré : le prochain ticket sera le n° ' . $next . '.');
    }

    public function saveImages()
    {
        $show = $this->request->getPost('show_images') ? 1 : 0;
        (new SiteSettingModel())->setSetting(BarProductModel::IMAGES_SETTING, $show);

        return redirect()->to(base_url('admin/caisse/parametres'))->with('success',
            $show ? 'Les photos des produits sont affichées sur les touches de la caisse.' : 'Les touches de la caisse sont affichées sans photo.');
    }
}
