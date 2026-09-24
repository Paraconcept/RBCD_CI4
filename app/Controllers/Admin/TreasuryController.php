<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberClubFeeModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TreasuryController extends BaseController
{
    public function index(): string
    {
        $year   = (int) ($this->request->getGet('year') ?? date('Y'));
        $season = MemberClubFeeModel::frbbSeasonFor($year);
        $rows   = $this->fetchRows($year, $season);

        $stats = [
            'total'      => count($rows),
            'frbbTotal'  => 0, 'frbbPaid' => 0,
            'rbcdH1Paid' => 0, 'rbcdH2Paid' => 0,
            'f1Total'    => 0, 'f1Paid'   => 0,
            'f2Total'    => 0, 'f2Paid'   => 0,
        ];

        foreach ($rows as $r) {
            if ($r->is_federated)      { $stats['frbbTotal']++; if ($r->frbb_paid) $stats['frbbPaid']++; }
            if ($r->rbcd_h1_paid)        $stats['rbcdH1Paid']++;
            if ($r->rbcd_h2_paid)        $stats['rbcdH2Paid']++;
            if ($r->forfait_h1_choice) { $stats['f1Total']++; if ($r->forfait_h1_paid) $stats['f1Paid']++; }
            if ($r->forfait_h2_choice) { $stats['f2Total']++; if ($r->forfait_h2_paid) $stats['f2Paid']++; }
        }

        return view('admin/treasury/dashboard', [
            'title'       => 'Trésorerie',
            'breadcrumbs' => [['title' => 'Trésorerie']],
            'rows'        => $rows,
            'year'        => $year,
            'season'      => $season,
            'years'       => $this->availableYears(),
            'stats'       => $stats,
        ]);
    }

    public function export()
    {
        $year   = (int) ($this->request->getGet('year') ?? date('Y'));
        $season = MemberClubFeeModel::frbbSeasonFor($year);
        $rows   = $this->fetchRows($year, $season);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Nom', 'Prénom', 'Fédéré',
            "FRBB {$season}-" . ($season + 1) . ' payé', 'Date FRBB',
            'RBCD 1 payé', 'Date RBCD 1',
            'RBCD 2 payé', 'Date RBCD 2',
            'Effectif 1 choix', 'Effectif 1 payé', 'Date Effectif 1',
            'Effectif 2 choix', 'Effectif 2 payé', 'Date Effectif 2',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $yn  = fn($v) => $v ? 'Oui' : 'Non';
        $row = 2;
        foreach ($rows as $r) {
            $sheet->fromArray([
                $r->last_name, $r->first_name, $yn($r->is_federated),
                $yn($r->frbb_paid), $r->frbb_paid_date ?? '',
                $yn($r->rbcd_h1_paid), $r->rbcd_h1_paid_date ?? '',
                $yn($r->rbcd_h2_paid), $r->rbcd_h2_paid_date ?? '',
                $yn($r->forfait_h1_choice), $yn($r->forfait_h1_paid), $r->forfait_h1_paid_date ?? '',
                $yn($r->forfait_h2_choice), $yn($r->forfait_h2_paid), $r->forfait_h2_paid_date ?? '',
            ], null, 'A' . $row);
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

    private function fetchRows(int $year, int $season): array
    {
        return \Config\Database::connect()->table('members m')
            ->select([
                'm.id',
                'm.first_name',
                'm.last_name',
                'm.is_federated',
                'mp.frbb_paid',
                'mp.frbb_paid_date',
                'cf.rbcd_h1_paid',
                'cf.rbcd_h1_paid_date',
                'cf.rbcd_h2_paid',
                'cf.rbcd_h2_paid_date',
                'cf.forfait_h1_choice',
                'cf.forfait_h1_paid',
                'cf.forfait_h1_paid_date',
                'cf.forfait_h2_choice',
                'cf.forfait_h2_paid',
                'cf.forfait_h2_paid_date',
            ])
            ->join('member_payments mp', "mp.member_id = m.id AND mp.year = {$season}", 'left')
            ->join('member_club_fees cf', "cf.member_id = m.id AND cf.year = {$year}", 'left')
            ->where('m.is_active', 1)
            ->orderBy('m.last_name')->orderBy('m.first_name')
            ->get()->getResultObject();
    }

    private function availableYears(): array
    {
        $db    = \Config\Database::connect();
        $years = array_merge(
            array_column($db->table('member_club_fees')->select('year')->distinct()->get()->getResultArray(), 'year'),
            array_column($db->table('member_payments')->select('year')->distinct()->get()->getResultArray(), 'year'),
            [(int) date('Y')]
        );
        $years = array_unique(array_map('intval', $years));
        rsort($years);
        return $years;
    }

    public function settings(): string
    {
        $settings = (new \App\Models\TreasurySettingModel())->first();

        return view('admin/treasury/settings', [
            'title'       => 'Paramètres financiers',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Paramètres financiers'],
            ],
            'settings' => $settings,
        ]);
    }

    public function saveSettings()
    {
        $model    = new \App\Models\TreasurySettingModel();
        $settings = $model->first();

        $data = [
            'annual_cotisation' => (float) str_replace(',', '.', $this->request->getPost('annual_cotisation')),
            'semester_cotisation' => (float) str_replace(',', '.', $this->request->getPost('semester_cotisation')),
            'forfait_price'     => (float) str_replace(',', '.', $this->request->getPost('forfait_price')),
            'lesson_price'      => (float) str_replace(',', '.', $this->request->getPost('lesson_price')),
            'hourly_price'      => (float) str_replace(',', '.', $this->request->getPost('hourly_price')),
        ];

        if ($settings) {
            $model->update($settings->id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to(base_url('admin/treasury/settings'))
                         ->with('success', 'Paramètres financiers mis à jour.');
    }
}
