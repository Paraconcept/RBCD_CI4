<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TreasuryExpenseModel;
use App\Models\TreasuryRevenueModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TreasuryBilanController extends BaseController
{
    private \CodeIgniter\Database\BaseConnection $db;
    private float $cotisAmount;
    private float $forfaitAmount;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $settings = $this->db->table('treasury_settings')->get()->getRowObject();
        $this->cotisAmount   = (float) ($settings->annual_cotisation ?? 50);
        $this->forfaitAmount = (float) ($settings->forfait_price ?? 75);
    }

    public function index(): string
    {
        $expModel = new TreasuryExpenseModel();
        $revModel = new TreasuryRevenueModel();

        $year     = (int) ($this->request->getGet('year') ?? date('Y'));
        $prevYear = $year - 1;

        // Recettes manuelles
        $totalRevManualN   = $revModel->getTotalByYear($year);
        $totalRevManualNm1 = $revModel->getTotalByYear($prevYear);
        $revManualByMonthN = $revModel->getMonthlyTotals($year);

        // Cotisations (RBCD + forfaits), classées par mois de paiement
        $cotisMonthlyN = $this->getCotisationsByMonth($year);
        $totalCotisN   = array_sum($cotisMonthlyN);
        $totalCotisNm1 = array_sum($this->getCotisationsByMonth($prevYear));

        // Enveloppes de caisse (bar) — total + ventilation TVA
        $envMonthlyN  = $this->getEnvelopesByMonth($year);
        $envVatN      = $this->getEnvelopesByMonthAndVat($year);
        $totalEnvN    = array_sum($envMonthlyN);
        $totalEnvNm1  = array_sum($this->getEnvelopesByMonth($prevYear));

        // Totaux globaux recettes
        $totalRevAllN   = $totalRevManualN + $totalCotisN + $totalEnvN;
        $totalRevAllNm1 = $totalRevManualNm1 + $totalCotisNm1 + $totalEnvNm1;

        // Dépenses
        $totalExpN   = $expModel->getTotalByYear($year);
        $totalExpNm1 = $expModel->getTotalByYear($prevYear);
        $expByMonthN = $expModel->getMonthlyTotals($year);

        return view('admin/treasury_bilan/index', [
            'title'       => 'Bilan financier',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Bilan'],
            ],
            'year'              => $year,
            'prevYear'          => $prevYear,
            'years'             => $this->getAvailableYears(),
            'totalRevManualN'   => $totalRevManualN,
            'totalRevManualNm1' => $totalRevManualNm1,
            'totalCotisN'       => $totalCotisN,
            'totalCotisNm1'     => $totalCotisNm1,
            'totalEnvN'         => $totalEnvN,
            'totalEnvNm1'       => $totalEnvNm1,
            'totalRevAllN'      => $totalRevAllN,
            'totalRevAllNm1'    => $totalRevAllNm1,
            'totalExpN'         => $totalExpN,
            'totalExpNm1'       => $totalExpNm1,
            'revManualByMonthN' => $revManualByMonthN,
            'cotisMonthlyN'     => $cotisMonthlyN,
            'envMonthlyN'       => $envMonthlyN,
            'env6ByMonthN'      => $envVatN['6pct'],
            'env12ByMonthN'     => $envVatN['12pct'],
            'env21ByMonthN'     => $envVatN['21pct'],
            'expByMonthN'       => $expByMonthN,
            'revByCatN'         => $revModel->getTotalByYearAndCategory($year),
            'expByCatN'         => $expModel->getTotalByYearAndCategory($year),
            'expCategories'     => TreasuryExpenseModel::$categories,
            'revCategories'     => TreasuryRevenueModel::$categories,
            'cotisAmount'       => $this->cotisAmount,
            'forfaitAmount'     => $this->forfaitAmount,
        ]);
    }

    public function export()
    {
        $expModel = new TreasuryExpenseModel();
        $revModel = new TreasuryRevenueModel();

        $year     = (int) ($this->request->getGet('year') ?? date('Y'));
        $prevYear = $year - 1;

        $totalRevManualN  = $revModel->getTotalByYear($year);
        $totalRevManualNm1 = $revModel->getTotalByYear($prevYear);
        $totalCotisN      = array_sum($this->getCotisationsByMonth($year));
        $totalCotisNm1    = array_sum($this->getCotisationsByMonth($prevYear));
        $totalEnvN        = array_sum($this->getEnvelopesByMonth($year));
        $totalEnvNm1      = array_sum($this->getEnvelopesByMonth($prevYear));
        $totalRevN        = $totalRevManualN + $totalCotisN + $totalEnvN;
        $totalRevNm1      = $totalRevManualNm1 + $totalCotisNm1 + $totalEnvNm1;
        $totalExpN        = $expModel->getTotalByYear($year);
        $totalExpNm1      = $expModel->getTotalByYear($prevYear);
        $soldeN           = $totalRevN - $totalExpN;
        $soldeNm1         = $totalRevNm1 - $totalExpNm1;

        $revByMonthN   = $revModel->getMonthlyTotals($year);
        $cotisMonthlyN = $this->getCotisationsByMonth($year);
        $envMonthlyN   = $this->getEnvelopesByMonth($year);
        $envVatN       = $this->getEnvelopesByMonthAndVat($year);
        $expByMonthN   = $expModel->getMonthlyTotals($year);

        $revByCatN  = $revModel->getTotalByYearAndCategory($year);
        $expByCatN  = $expModel->getTotalByYearAndCategory($year);
        $months     = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet();
        $sheet->setTitle("Bilan $year");

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sectionStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
        ];
        $boldStyle   = ['font' => ['bold' => true]];
        $rightAlign  = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
        $totalRowStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ];
        $greenText = ['font' => ['color' => ['rgb' => '1E7E34']]];
        $redText   = ['font' => ['color' => ['rgb' => 'C0392B']]];

        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';
        $fmtDelta = function(float $d) use ($fmt): string {
            if ($d > 0) return '+' . $fmt($d);
            if ($d < 0) return $fmt($d);
            return '=';
        };

        // ── Titre
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'BILAN FINANCIER — RBC DISONAIS');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', "Année : $year  |  Exporté le : " . date('d/m/Y'));
        $sheet->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '555555']]]);

        // ── Récapitulatif
        $r = 4;
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", 'RÉCAPITULATIF');
        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($sectionStyle);

        $r++;
        foreach ([['', $year, $prevYear, 'Variation']] as $row) {
            $sheet->setCellValue("A{$r}", ''); $sheet->setCellValue("B{$r}", $year);
            $sheet->setCellValue("C{$r}", $prevYear); $sheet->setCellValue("D{$r}", 'Variation');
            $sheet->getStyle("A{$r}:D{$r}")->applyFromArray($boldStyle);
        }

        $r++;
        $sheet->setCellValue("A{$r}", 'Recettes totales');
        $sheet->setCellValue("B{$r}", $fmt($totalRevN));
        $sheet->setCellValue("C{$r}", $fmt($totalRevNm1));
        $sheet->setCellValue("D{$r}", $fmtDelta($totalRevN - $totalRevNm1));
        $sheet->getStyle("B{$r}:D{$r}")->applyFromArray(array_merge($rightAlign, $greenText));

        $r++;
        $sheet->setCellValue("A{$r}", 'Dépenses totales');
        $sheet->setCellValue("B{$r}", $fmt($totalExpN));
        $sheet->setCellValue("C{$r}", $fmt($totalExpNm1));
        $sheet->setCellValue("D{$r}", $fmtDelta($totalExpNm1 - $totalExpN));
        $sheet->getStyle("B{$r}:D{$r}")->applyFromArray(array_merge($rightAlign, $redText));

        $r++;
        $sheet->setCellValue("A{$r}", 'Solde');
        $sheet->setCellValue("B{$r}", $fmt($soldeN));
        $sheet->setCellValue("C{$r}", $fmt($soldeNm1));
        $sheet->setCellValue("D{$r}", $fmtDelta($soldeN - $soldeNm1));
        $soldeColor = $soldeN >= 0 ? $greenText : $redText;
        $sheet->getStyle("B{$r}:D{$r}")->applyFromArray(array_merge($rightAlign, $soldeColor));
        $sheet->getStyle("A{$r}:D{$r}")->applyFromArray($boldStyle);

        // ── Sources des recettes
        $r += 2;
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", "SOURCES DES RECETTES — $year");
        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($sectionStyle);

        foreach ([
            ['Recettes manuelles', $totalRevManualN],
            ['Cotisations (RBCD + forfaits)', $totalCotisN],
            ['Bar / Enveloppes de caisse', $totalEnvN],
        ] as [$label, $amount]) {
            $r++;
            $sheet->setCellValue("A{$r}", $label);
            $sheet->setCellValue("B{$r}", $fmt($amount));
            $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $greenText));
        }

        $r++;
        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->setCellValue("B{$r}", $fmt($totalRevN));
        $sheet->getStyle("A{$r}:B{$r}")->applyFromArray(array_merge($totalRowStyle, ['font' => ['bold' => true, 'color' => ['rgb' => '1E7E34']]]));
        $sheet->getStyle("B{$r}")->applyFromArray($rightAlign);

        // ── Évolution mensuelle
        $r += 2;
        $sheet->mergeCells("A{$r}:I{$r}");
        $sheet->setCellValue("A{$r}", 'ÉVOLUTION MENSUELLE');
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($sectionStyle);

        $r++;
        foreach (['Mois', 'Rec. man.', 'Cotisations', 'Bar 6%', 'Bar 12%', 'Bar 21%', 'Total rec.', 'Dépenses', 'Solde'] as $ci => $h) {
            $sheet->setCellValue(chr(65 + $ci) . $r, $h);
        }
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($boldStyle);

        $total6 = $total12 = $total21 = 0.0;
        for ($m = 1; $m <= 12; $m++) {
            $r++;
            $r6  = $envVatN['6pct'][$m]  ?? 0;
            $r12 = $envVatN['12pct'][$m] ?? 0;
            $r21 = $envVatN['21pct'][$m] ?? 0;
            $total6  += $r6; $total12 += $r12; $total21 += $r21;
            $rM = ($revByMonthN[$m] ?? 0) + ($cotisMonthlyN[$m] ?? 0) + ($envMonthlyN[$m] ?? 0);
            $eM = $expByMonthN[$m] ?? 0;
            $sM = $rM - $eM;
            $sheet->setCellValue("A{$r}", $months[$m - 1]);
            $sheet->getStyle("A{$r}")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]]);
            $sheet->setCellValue("B{$r}", $revByMonthN[$m] > 0 ? $fmt($revByMonthN[$m]) : '—');
            $sheet->setCellValue("C{$r}", $cotisMonthlyN[$m] > 0 ? $fmt($cotisMonthlyN[$m]) : '—');
            $sheet->setCellValue("D{$r}", $r6  > 0 ? $fmt($r6)  : '—');
            $sheet->setCellValue("E{$r}", $r12 > 0 ? $fmt($r12) : '—');
            $sheet->setCellValue("F{$r}", $r21 > 0 ? $fmt($r21) : '—');
            $sheet->setCellValue("G{$r}", $rM > 0 ? $fmt($rM) : '—');
            $sheet->setCellValue("H{$r}", $eM > 0 ? $fmt($eM) : '—');
            $sheet->setCellValue("I{$r}", ($rM > 0 || $eM > 0) ? $fmt($sM) : '—');
            if ($rM > 0) $sheet->getStyle("G{$r}")->applyFromArray($greenText);
            if ($eM > 0) $sheet->getStyle("H{$r}")->applyFromArray($redText);
            if ($rM > 0 || $eM > 0) $sheet->getStyle("I{$r}")->applyFromArray($sM >= 0 ? $greenText : $redText);
        }

        $r++;
        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->setCellValue("B{$r}", $fmt($totalRevManualN));
        $sheet->setCellValue("C{$r}", $fmt($totalCotisN));
        $sheet->setCellValue("D{$r}", $total6  > 0 ? $fmt($total6)  : '—');
        $sheet->setCellValue("E{$r}", $total12 > 0 ? $fmt($total12) : '—');
        $sheet->setCellValue("F{$r}", $fmt($total21));
        $sheet->setCellValue("G{$r}", $fmt($totalRevN));
        $sheet->setCellValue("H{$r}", $fmt($totalExpN));
        $sheet->setCellValue("I{$r}", $fmt($soldeN));
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($totalRowStyle);
        $sheet->getStyle("G{$r}")->applyFromArray($greenText);
        $sheet->getStyle("H{$r}")->applyFromArray($redText);
        $sheet->getStyle("I{$r}")->applyFromArray($soldeN >= 0 ? $greenText : $redText);

        // ── Catégories recettes
        $r += 2;
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", "RECETTES PAR CATÉGORIE — $year");
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($sectionStyle);
        $r++;
        $sheet->setCellValue("A{$r}", 'Catégorie');
        $sheet->setCellValue("B{$r}", 'Montant');
        $sheet->setCellValue("C{$r}", '%');
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($boldStyle);

        foreach (TreasuryRevenueModel::$categories as $key => $label) {
            $m = $revByCatN[$key] ?? 0;
            if ($m <= 0) continue;
            $r++;
            $pct = $totalRevManualN > 0 ? round($m / $totalRevManualN * 100) : 0;
            $sheet->setCellValue("A{$r}", $label);
            $sheet->setCellValue("B{$r}", $fmt($m));
            $sheet->setCellValue("C{$r}", $pct . ' %');
            $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $greenText));
        }

        // ── Catégories dépenses
        $r += 2;
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", "DÉPENSES PAR CATÉGORIE — $year");
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($sectionStyle);
        $r++;
        $sheet->setCellValue("A{$r}", 'Catégorie');
        $sheet->setCellValue("B{$r}", 'Montant');
        $sheet->setCellValue("C{$r}", '%');
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($boldStyle);

        foreach (TreasuryExpenseModel::$categories as $key => $label) {
            $m = $expByCatN[$key] ?? 0;
            if ($m <= 0) continue;
            $r++;
            $pct = $totalExpN > 0 ? round($m / $totalExpN * 100) : 0;
            $sheet->setCellValue("A{$r}", $label);
            $sheet->setCellValue("B{$r}", $fmt($m));
            $sheet->setCellValue("C{$r}", $pct . ' %');
            $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $redText));
        }

        // Largeurs de colonnes
        foreach (['A' => 28, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 18, 'H' => 18, 'I' => 18] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = "bilan_rbcd_{$year}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($sp))->save('php://output');
        exit;
    }

    // ── Helpers privés

    private function getCotisationsByMonth(int $year): array
    {
        $result = array_fill(1, 12, 0.0);

        $rows = $this->db->table('member_payments')
            ->select('MONTH(rbcd_paid_date) as m, COUNT(*) as cnt')
            ->where('rbcd_paid', 1)->where('rbcd_paid_date IS NOT NULL')
            ->where('YEAR(rbcd_paid_date)', $year)
            ->groupBy('MONTH(rbcd_paid_date)')
            ->get()->getResultArray();
        foreach ($rows as $row) {
            $result[(int) $row['m']] += (int) $row['cnt'] * $this->cotisAmount;
        }

        foreach (['forfait_f1_paid_date' => 'forfait_f1_paid', 'forfait_f2_paid_date' => 'forfait_f2_paid'] as $dateCol => $paidCol) {
            $rows = $this->db->table('member_payments')
                ->select("MONTH($dateCol) as m, COUNT(*) as cnt")
                ->where($paidCol, 1)->where("$dateCol IS NOT NULL")
                ->where("YEAR($dateCol)", $year)
                ->groupBy("MONTH($dateCol)")
                ->get()->getResultArray();
            foreach ($rows as $row) {
                $result[(int) $row['m']] += (int) $row['cnt'] * $this->forfaitAmount;
            }
        }

        return $result;
    }

    private function getEnvelopesByMonth(int $year): array
    {
        $result = array_fill(1, 12, 0.0);
        $rows = $this->db->table('treasury_envelopes')
            ->select('MONTH(date) as m, SUM(amount_found) as total')
            ->where('YEAR(date)', $year)
            ->groupBy('MONTH(date)')
            ->get()->getResultArray();
        foreach ($rows as $row) {
            $result[(int) $row['m']] = (float) $row['total'];
        }
        return $result;
    }

    private function getEnvelopesByMonthAndVat(int $year): array
    {
        $pct6  = array_fill(1, 12, 0.0);
        $pct12 = array_fill(1, 12, 0.0);
        $pct21 = array_fill(1, 12, 0.0);

        $rows = $this->db->table('treasury_envelopes')
            ->select('MONTH(date) as m, SUM(amount_found) as total, SUM(IFNULL(amount_6pct, 0)) as s6, SUM(IFNULL(amount_12pct, 0)) as s12')
            ->where('YEAR(date)', $year)
            ->groupBy('MONTH(date)')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $m = (int) $row['m'];
            $s6  = (float) $row['s6'];
            $s12 = (float) $row['s12'];
            $pct6[$m]  = $s6;
            $pct12[$m] = $s12;
            $pct21[$m] = (float) $row['total'] - $s6 - $s12;
        }

        return ['6pct' => $pct6, '12pct' => $pct12, '21pct' => $pct21];
    }

    private function getAvailableYears(): array
    {
        $a = array_column($this->db->table('treasury_revenues')->select('YEAR(revenue_date) as y')->distinct()->get()->getResultArray(), 'y');
        $b = array_column($this->db->table('treasury_expenses')->select('YEAR(expense_date) as y')->distinct()->get()->getResultArray(), 'y');
        $c = array_column($this->db->table('member_payments')->select('year as y')->distinct()->get()->getResultArray(), 'y');

        $years = array_unique(array_merge($a, $b, $c));
        rsort($years);
        if (!in_array(date('Y'), $years)) array_unshift($years, (int) date('Y'));
        return $years;
    }
}
