<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TreasuryController extends BaseController
{
    public function index(): string
    {
        $year = (int) ($this->request->getGet('year') ?? ANNEE_1);

        $db = \Config\Database::connect();

        $rows = $db->table('members m')
            ->select([
                'm.id',
                'm.first_name',
                'm.last_name',
                'm.is_federated',
                'mp.id          AS payment_id',
                'mp.rbcd_paid',
                'mp.rbcd_paid_date',
                'mp.frbb_paid',
                'mp.frbb_paid_date',
                'mp.forfait_h1_choice',
                'mp.forfait_h1_paid',
                'mp.forfait_h1_paid_date',
                'mp.forfait_h2_choice',
                'mp.forfait_h2_paid',
                'mp.forfait_h2_paid_date',
            ])
            ->join('member_payments mp', "mp.member_id = m.id AND mp.year = {$year}", 'left')
            ->where('m.is_active', 1)
            ->orderBy('m.last_name')->orderBy('m.first_name')
            ->get()->getResultObject();

        // Stats globales
        $total       = count($rows);
        $rbcdPaid    = 0;
        $frbbTotal   = 0; $frbbPaid = 0;
        $h1Total     = 0; $h1Paid   = 0;
        $h2Total     = 0; $h2Paid   = 0;

        foreach ($rows as $r) {
            if ($r->rbcd_paid)           $rbcdPaid++;
            if ($r->is_federated)      { $frbbTotal++; if ($r->frbb_paid) $frbbPaid++; }
            if ($r->forfait_h1_choice) { $h1Total++;   if ($r->forfait_h1_paid) $h1Paid++; }
            if ($r->forfait_h2_choice) { $h2Total++;   if ($r->forfait_h2_paid) $h2Paid++; }
        }

        // Années disponibles pour le sélecteur
        $years = $db->table('member_payments')
            ->select('year')->distinct()
            ->orderBy('year', 'DESC')
            ->get()->getResultArray();
        $years = array_column($years, 'year');

        return view('admin/treasury/dashboard', [
            'title'       => 'Trésorerie',
            'breadcrumbs' => [['title' => 'Trésorerie']],
            'rows'        => $rows,
            'year'        => $year,
            'years'       => $years,
            'stats'       => [
                'total'     => $total,
                'rbcdPaid'  => $rbcdPaid,
                'frbbTotal' => $frbbTotal,
                'frbbPaid'  => $frbbPaid,
                'h1Total'   => $h1Total,
                'h1Paid'    => $h1Paid,
                'h2Total'   => $h2Total,
                'h2Paid'    => $h2Paid,
            ],
        ]);
    }
}
