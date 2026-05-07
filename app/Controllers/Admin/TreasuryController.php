<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function export()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'Nom');
        $sheet->setCellValue('B1', 'Prénom');
        $sheet->setCellValue('C1', 'Fédéré');
        $sheet->setCellValue('D1', 'RBCD payé');
        $sheet->setCellValue('E1', 'Date RBCD');
        $sheet->setCellValue('F1', 'FRBB payé');
        $sheet->setCellValue('G1', 'Date FRBB');
        $sheet->setCellValue('H1', 'Forfait F1 choix');
        $sheet->setCellValue('I1', 'Forfait F1 payé');
        $sheet->setCellValue('J1', 'Date F1');
        $sheet->setCellValue('K1', 'Forfait F2 choix');
        $sheet->setCellValue('L1', 'Forfait F2 payé');
        $sheet->setCellValue('M1', 'Date F2');

        $row = 2;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $row, $r->last_name);
            $sheet->setCellValue('B' . $row, $r->first_name);
            $sheet->setCellValue('C' . $row, $r->is_federated ? 'Oui' : 'Non');
            $sheet->setCellValue('D' . $row, $r->rbcd_paid ? 'Oui' : 'Non');
            $sheet->setCellValue('E' . $row, $r->rbcd_paid_date ?? '');
            $sheet->setCellValue('F' . $row, $r->frbb_paid ? 'Oui' : 'Non');
            $sheet->setCellValue('G' . $row, $r->frbb_paid_date ?? '');
            $sheet->setCellValue('H' . $row, $r->forfait_f1_choice ? 'Oui' : 'Non');
            $sheet->setCellValue('I' . $row, $r->forfait_f1_paid ? 'Oui' : 'Non');
            $sheet->setCellValue('J' . $row, $r->forfait_f1_paid_date ?? '');
            $sheet->setCellValue('K' . $row, $r->forfait_f2_choice ? 'Oui' : 'Non');
            $sheet->setCellValue('L' . $row, $r->forfait_f2_paid ? 'Oui' : 'Non');
            $sheet->setCellValue('M' . $row, $r->forfait_f2_paid_date ?? '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "paiements_membres_{$year}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
