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
                'mp.forfait_f1_choice',
                'mp.forfait_f1_paid',
                'mp.forfait_f1_paid_date',
                'mp.forfait_f2_choice',
                'mp.forfait_f2_paid',
                'mp.forfait_f2_paid_date',
            ])
            ->join('member_payments mp', "mp.member_id = m.id AND mp.year = {$year}", 'left')
            ->where('m.is_active', 1)
            ->orderBy('m.last_name')->orderBy('m.first_name')
            ->get()->getResultObject();

        // Stats globales
        $total       = count($rows);
        $rbcdPaid    = 0;
        $frbbTotal   = 0; $frbbPaid = 0;
        $f1Total     = 0; $f1Paid   = 0;
        $f2Total     = 0; $f2Paid   = 0;

        foreach ($rows as $r) {
            if ($r->rbcd_paid)           $rbcdPaid++;
            if ($r->is_federated)      { $frbbTotal++; if ($r->frbb_paid) $frbbPaid++; }
            if ($r->forfait_f1_choice) { $f1Total++;   if ($r->forfait_f1_paid) $f1Paid++; }
            if ($r->forfait_f2_choice) { $f2Total++;   if ($r->forfait_f2_paid) $f2Paid++; }
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
                'f1Total'   => $f1Total,
                'f1Paid'    => $f1Paid,
                'f2Total'   => $f2Total,
                'f2Paid'    => $f2Paid,
            ],
        ]);
    }
}
