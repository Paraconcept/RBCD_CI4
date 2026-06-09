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
use Dompdf\Dompdf;
use Dompdf\Options;

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
            $sheet->getStyle("A{$r}")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'indent' => 2]]);
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

    public function exportMonth()
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $fullMonths = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $monthName  = $fullMonths[$month - 1];

        $revByDayN   = $this->getRevenuesByDay($year, $month);
        $cotisByDayN = $this->getCotisationsByDay($year, $month);
        $envDayN     = $this->getEnvelopesByDayAndVat($year, $month);
        $expByDayN   = $this->getExpensesByDay($year, $month);

        $totalRevManual = array_sum($revByDayN);
        $totalCotis     = array_sum($cotisByDayN);
        $total6         = array_sum($envDayN['6pct']);
        $total12        = array_sum($envDayN['12pct']);
        $total21        = array_sum($envDayN['21pct']);
        $totalEnv       = array_sum($envDayN['total']);
        $totalRev       = $totalRevManual + $totalCotis + $totalEnv;
        $totalExp       = array_sum($expByDayN);
        $solde          = $totalRev - $totalExp;

        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet();
        $sheet->setTitle("$monthName $year");

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sectionStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
        ];
        $boldStyle     = ['font' => ['bold' => true]];
        $rightAlign    = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
        $totalRowStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ];
        $greenText = ['font' => ['color' => ['rgb' => '1E7E34']]];
        $redText   = ['font' => ['color' => ['rgb' => 'C0392B']]];

        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';

        // Titre
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'BILAN JOURNALIER — RBC DISONAIS');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', "Mois : $monthName $year  |  Exporté le : " . date('d/m/Y'));
        $sheet->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '555555']]]);

        // En-têtes tableau
        $r = 4;
        $sheet->mergeCells("A{$r}:I{$r}");
        $sheet->setCellValue("A{$r}", 'ÉVOLUTION JOURNALIÈRE');
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($sectionStyle);

        $r++;
        foreach (['Jour', 'Rec. man.', 'Cotisations', 'Bar 6%', 'Bar 12%', 'Bar 21%', 'Total rec.', 'Dépenses', 'Solde'] as $ci => $h) {
            $sheet->setCellValue(chr(65 + $ci) . $r, $h);
        }
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($boldStyle);

        // Lignes journalières
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $r++;
            $rMan = $revByDayN[$d]        ?? 0;
            $rCot = $cotisByDayN[$d]      ?? 0;
            $rEnv = $envDayN['total'][$d] ?? 0;
            $r6   = $envDayN['6pct'][$d]  ?? 0;
            $r12  = $envDayN['12pct'][$d] ?? 0;
            $r21  = $envDayN['21pct'][$d] ?? 0;
            $rAll = $rMan + $rCot + $rEnv;
            $eD   = $expByDayN[$d]        ?? 0;
            $sD   = $rAll - $eD;

            $sheet->setCellValue("A{$r}", $d);
            $sheet->getStyle("A{$r}")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'indent' => 2]]);
            $sheet->setCellValue("B{$r}", $rMan > 0 ? $fmt($rMan) : '—');
            $sheet->setCellValue("C{$r}", $rCot > 0 ? $fmt($rCot) : '—');
            $sheet->setCellValue("D{$r}", $r6   > 0 ? $fmt($r6)   : '—');
            $sheet->setCellValue("E{$r}", $r12  > 0 ? $fmt($r12)  : '—');
            $sheet->setCellValue("F{$r}", $r21  > 0 ? $fmt($r21)  : '—');
            $sheet->setCellValue("G{$r}", $rAll > 0 ? $fmt($rAll) : '—');
            $sheet->setCellValue("H{$r}", $eD   > 0 ? $fmt($eD)   : '—');
            $sheet->setCellValue("I{$r}", ($rAll > 0 || $eD > 0) ? $fmt($sD) : '—');
            if ($rAll > 0) $sheet->getStyle("G{$r}")->applyFromArray($greenText);
            if ($eD   > 0) $sheet->getStyle("H{$r}")->applyFromArray($redText);
            if ($rAll > 0 || $eD > 0) $sheet->getStyle("I{$r}")->applyFromArray($sD >= 0 ? $greenText : $redText);
        }

        // Ligne TOTAL
        $r++;
        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->setCellValue("B{$r}", $fmt($totalRevManual));
        $sheet->setCellValue("C{$r}", $fmt($totalCotis));
        $sheet->setCellValue("D{$r}", $total6  > 0 ? $fmt($total6)  : '—');
        $sheet->setCellValue("E{$r}", $total12 > 0 ? $fmt($total12) : '—');
        $sheet->setCellValue("F{$r}", $fmt($total21));
        $sheet->setCellValue("G{$r}", $fmt($totalRev));
        $sheet->setCellValue("H{$r}", $fmt($totalExp));
        $sheet->setCellValue("I{$r}", $fmt($solde));
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($totalRowStyle);
        $sheet->getStyle("G{$r}")->applyFromArray($greenText);
        $sheet->getStyle("H{$r}")->applyFromArray($redText);
        $sheet->getStyle("I{$r}")->applyFromArray($solde >= 0 ? $greenText : $redText);

        foreach (['A' => 10, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 18, 'H' => 18, 'I' => 18] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $monthPad = str_pad($month, 2, '0', STR_PAD_LEFT);
        $filename = "bilan_journalier_{$year}_{$monthPad}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($sp))->save('php://output');
        exit;
    }

    public function exportPdf(): void
    {
        $expModel = new TreasuryExpenseModel();
        $revModel = new TreasuryRevenueModel();

        $year     = (int) ($this->request->getGet('year') ?? date('Y'));
        $prevYear = $year - 1;

        $totalRevManualN   = $revModel->getTotalByYear($year);
        $totalRevManualNm1 = $revModel->getTotalByYear($prevYear);
        $cotisMonthlyN     = $this->getCotisationsByMonth($year);
        $totalCotisN       = array_sum($cotisMonthlyN);
        $totalCotisNm1     = array_sum($this->getCotisationsByMonth($prevYear));
        $envMonthlyN       = $this->getEnvelopesByMonth($year);
        $totalEnvN         = array_sum($envMonthlyN);
        $totalEnvNm1       = array_sum($this->getEnvelopesByMonth($prevYear));
        $totalRevN         = $totalRevManualN + $totalCotisN + $totalEnvN;
        $totalRevNm1       = $totalRevManualNm1 + $totalCotisNm1 + $totalEnvNm1;
        $totalExpN         = $expModel->getTotalByYear($year);
        $totalExpNm1       = $expModel->getTotalByYear($prevYear);
        $soldeN            = $totalRevN - $totalExpN;
        $soldeNm1          = $totalRevNm1 - $totalExpNm1;
        $revByMonthN       = $revModel->getMonthlyTotals($year);
        $envVatN           = $this->getEnvelopesByMonthAndVat($year);
        $expByMonthN       = $expModel->getMonthlyTotals($year);
        $revByCatN         = $revModel->getTotalByYearAndCategory($year);
        $expByCatN         = $expModel->getTotalByYearAndCategory($year);
        $months = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';
        $fmtD = function(float $d) use ($fmt): string {
            if ($d > 0) return '+' . $fmt($d);
            if ($d < 0) return $fmt($d);
            return '=';
        };
        $gc = fn(float $v): string => $v >= 0 ? '#1E7E34' : '#C0392B';

        $css = 'body{font-family:DejaVu Sans,sans-serif;font-size:8pt;margin:0;padding:8px;color:#222;}'
             . 'table{width:100%;border-collapse:collapse;margin-bottom:10px;}'
             . 'td,th{padding:2px 4px;border:1px solid #ddd;}'
             . '.hdr{background:#1F3864;color:#fff;font-size:11pt;font-weight:bold;text-align:center;padding:6px;}'
             . '.sub{color:#555;font-style:italic;font-size:7.5pt;padding:2px 0 8px;}'
             . '.sec td{background:#2E75B6;color:#fff;font-weight:bold;border:none;padding:3px 6px;}'
             . '.tot td{background:#F2F2F2;font-weight:bold;}'
             . '.bld td{font-weight:bold;}'
             . '.r{text-align:right;}';

        $h = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>';
        $h .= '<div class="hdr">BILAN FINANCIER — RBC DISONAIS</div>';
        $h .= '<div class="sub">Année : ' . $year . '  |  Exporté le : ' . date('d/m/Y') . '</div>';

        // Récapitulatif
        $h .= '<table>';
        $h .= '<tr class="sec"><td colspan="4">RÉCAPITULATIF</td></tr>';
        $h .= '<tr class="bld"><td style="width:40%"></td><td class="r" style="width:20%">' . $year . '</td><td class="r" style="width:20%">' . $prevYear . '</td><td class="r" style="width:20%">Variation</td></tr>';
        $dRev = $totalRevN - $totalRevNm1;
        $h .= '<tr><td>Recettes totales</td><td class="r" style="color:#1E7E34">' . $fmt($totalRevN) . '</td><td class="r" style="color:#1E7E34">' . $fmt($totalRevNm1) . '</td><td class="r" style="color:' . $gc($dRev) . '">' . $fmtD($dRev) . '</td></tr>';
        $dExp = $totalExpNm1 - $totalExpN;
        $h .= '<tr><td>Dépenses totales</td><td class="r" style="color:#C0392B">' . $fmt($totalExpN) . '</td><td class="r" style="color:#C0392B">' . $fmt($totalExpNm1) . '</td><td class="r" style="color:' . $gc($dExp) . '">' . $fmtD($dExp) . '</td></tr>';
        $dSolde = $soldeN - $soldeNm1;
        $h .= '<tr class="bld"><td>Solde</td><td class="r" style="color:' . $gc($soldeN) . '">' . $fmt($soldeN) . '</td><td class="r" style="color:' . $gc($soldeNm1) . '">' . $fmt($soldeNm1) . '</td><td class="r" style="color:' . $gc($dSolde) . '">' . $fmtD($dSolde) . '</td></tr>';
        $h .= '</table>';

        // Sources des recettes
        $h .= '<table>';
        $h .= '<tr class="sec"><td colspan="3">SOURCES DES RECETTES — ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:60%">Source</td><td class="r" style="width:25%">Montant</td><td class="r" style="width:15%">%</td></tr>';
        foreach ([
            ['Recettes manuelles',         $totalRevManualN],
            ['Cotisations (RBCD + forfaits)', $totalCotisN],
            ['Bar / Enveloppes de caisse', $totalEnvN],
        ] as [$label, $amount]) {
            $pct = $totalRevN > 0 ? round($amount / $totalRevN * 100) : 0;
            $h .= '<tr><td>' . $label . '</td><td class="r" style="color:#1E7E34">' . $fmt($amount) . '</td><td class="r">' . $pct . ' %</td></tr>';
        }
        $h .= '<tr class="tot"><td>TOTAL RECETTES</td><td class="r" style="color:#1E7E34">' . $fmt($totalRevN) . '</td><td class="r">100 %</td></tr>';
        $h .= '</table>';

        // Évolution mensuelle
        $h .= '<table>';
        $h .= '<tr class="sec"><td colspan="9">ÉVOLUTION MENSUELLE</td></tr>';
        $h .= '<tr class="bld"><td style="width:12%">Mois</td><td class="r" style="width:11%">Rec. man.</td><td class="r" style="width:11%">Cotisations</td><td class="r" style="width:11%">Bar 6%</td><td class="r" style="width:11%">Bar 12%</td><td class="r" style="width:11%">Bar 21%</td><td class="r" style="width:11%;color:#1E7E34">Σ Recettes</td><td class="r" style="width:11%;color:#C0392B">Dépenses</td><td class="r" style="width:11%">Solde</td></tr>';
        $t6 = $t12 = $t21 = 0.0;
        for ($m = 1; $m <= 12; $m++) {
            $r6  = $envVatN['6pct'][$m]  ?? 0;
            $r12 = $envVatN['12pct'][$m] ?? 0;
            $r21 = $envVatN['21pct'][$m] ?? 0;
            $t6 += $r6; $t12 += $r12; $t21 += $r21;
            $rM = ($revByMonthN[$m] ?? 0) + ($cotisMonthlyN[$m] ?? 0) + ($envMonthlyN[$m] ?? 0);
            $eM = $expByMonthN[$m] ?? 0;
            $sM = $rM - $eM;
            $h .= '<tr>';
            $h .= '<td style="text-align:right;padding-right:8px">' . $months[$m - 1] . '</td>';
            $h .= '<td class="r">' . ($revByMonthN[$m] > 0 ? $fmt($revByMonthN[$m]) : '—') . '</td>';
            $h .= '<td class="r">' . ($cotisMonthlyN[$m] > 0 ? $fmt($cotisMonthlyN[$m]) : '—') . '</td>';
            $h .= '<td class="r">' . ($r6  > 0 ? $fmt($r6)  : '—') . '</td>';
            $h .= '<td class="r">' . ($r12 > 0 ? $fmt($r12) : '—') . '</td>';
            $h .= '<td class="r">' . ($r21 > 0 ? $fmt($r21) : '—') . '</td>';
            $h .= '<td class="r"' . ($rM > 0 ? ' style="color:#1E7E34"' : '') . '>' . ($rM > 0 ? $fmt($rM) : '—') . '</td>';
            $h .= '<td class="r"' . ($eM > 0 ? ' style="color:#C0392B"' : '') . '>' . ($eM > 0 ? $fmt($eM) : '—') . '</td>';
            $sStyle = ($rM > 0 || $eM > 0) ? ' style="color:' . $gc($sM) . '"' : '';
            $h .= '<td class="r"' . $sStyle . '>' . (($rM > 0 || $eM > 0) ? $fmt($sM) : '—') . '</td>';
            $h .= '</tr>';
        }
        $h .= '<tr class="tot"><td>TOTAL</td><td class="r">' . $fmt($totalRevManualN) . '</td><td class="r">' . $fmt($totalCotisN) . '</td>';
        $h .= '<td class="r">' . ($t6  > 0 ? $fmt($t6)  : '—') . '</td>';
        $h .= '<td class="r">' . ($t12 > 0 ? $fmt($t12) : '—') . '</td>';
        $h .= '<td class="r">' . $fmt($t21) . '</td>';
        $h .= '<td class="r" style="color:#1E7E34">' . $fmt($totalRevN) . '</td>';
        $h .= '<td class="r" style="color:#C0392B">' . $fmt($totalExpN) . '</td>';
        $h .= '<td class="r" style="color:' . $gc($soldeN) . '">' . $fmt($soldeN) . '</td></tr>';
        $h .= '</table>';

        // Recettes par catégorie
        $h .= '<table>';
        $h .= '<tr class="sec"><td colspan="3">RECETTES PAR CATÉGORIE — ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:60%">Catégorie</td><td class="r" style="width:25%">Montant</td><td class="r" style="width:15%">%</td></tr>';
        foreach (TreasuryRevenueModel::$categories as $key => $label) {
            $m = $revByCatN[$key] ?? 0;
            if ($m <= 0) continue;
            $pct = $totalRevManualN > 0 ? round($m / $totalRevManualN * 100) : 0;
            $h .= '<tr><td>' . esc($label) . '</td><td class="r" style="color:#1E7E34">' . $fmt($m) . '</td><td class="r">' . $pct . ' %</td></tr>';
        }
        $h .= '</table>';

        // Dépenses par catégorie
        $h .= '<table>';
        $h .= '<tr class="sec"><td colspan="3">DÉPENSES PAR CATÉGORIE — ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:60%">Catégorie</td><td class="r" style="width:25%">Montant</td><td class="r" style="width:15%">%</td></tr>';
        foreach (TreasuryExpenseModel::$categories as $key => $label) {
            $m = $expByCatN[$key] ?? 0;
            if ($m <= 0) continue;
            $pct = $totalExpN > 0 ? round($m / $totalExpN * 100) : 0;
            $h .= '<tr><td>' . esc($label) . '</td><td class="r" style="color:#C0392B">' . $fmt($m) . '</td><td class="r">' . $pct . ' %</td></tr>';
        }
        $h .= '</table></body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($h, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="bilan_rbcd_' . $year . '.pdf"');
        header('Cache-Control: max-age=0');
        echo $dompdf->output();
        exit;
    }

    public function exportMonthPdf(): void
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $fullMonths = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $monthName  = $fullMonths[$month - 1];

        $revByDayN   = $this->getRevenuesByDay($year, $month);
        $cotisByDayN = $this->getCotisationsByDay($year, $month);
        $envDayN     = $this->getEnvelopesByDayAndVat($year, $month);
        $expByDayN   = $this->getExpensesByDay($year, $month);

        $totalRevManual = array_sum($revByDayN);
        $totalCotis     = array_sum($cotisByDayN);
        $total6         = array_sum($envDayN['6pct']);
        $total12        = array_sum($envDayN['12pct']);
        $total21        = array_sum($envDayN['21pct']);
        $totalEnv       = array_sum($envDayN['total']);
        $totalRev       = $totalRevManual + $totalCotis + $totalEnv;
        $totalExp       = array_sum($expByDayN);
        $solde          = $totalRev - $totalExp;

        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';
        $gc  = fn(float $v): string => $v >= 0 ? '#1E7E34' : '#C0392B';

        $css = 'body{font-family:DejaVu Sans,sans-serif;font-size:8pt;margin:0;padding:8px;color:#222;}'
             . 'table{width:100%;border-collapse:collapse;margin-bottom:10px;}'
             . 'td,th{padding:2px 4px;border:1px solid #ddd;}'
             . '.hdr{background:#1F3864;color:#fff;font-size:11pt;font-weight:bold;text-align:center;padding:6px;}'
             . '.sub{color:#555;font-style:italic;font-size:7.5pt;padding:2px 0 8px;}'
             . '.sec td{background:#2E75B6;color:#fff;font-weight:bold;border:none;padding:3px 6px;}'
             . '.tot td{background:#F2F2F2;font-weight:bold;}'
             . '.bld td{font-weight:bold;}'
             . '.r{text-align:right;}';

        $h = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>';
        $h .= '<div class="hdr">BILAN JOURNALIER — RBC DISONAIS</div>';
        $h .= '<div class="sub">Mois : ' . $monthName . ' ' . $year . '  |  Exporté le : ' . date('d/m/Y') . '</div>';

        $h .= '<table>';
        $h .= '<tr class="sec"><td colspan="9">ÉVOLUTION JOURNALIÈRE — ' . $monthName . ' ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:8%">Jour</td><td class="r" style="width:12%">Rec. man.</td><td class="r" style="width:12%">Cotisations</td><td class="r" style="width:12%">Bar 6%</td><td class="r" style="width:12%">Bar 12%</td><td class="r" style="width:12%">Bar 21%</td><td class="r" style="width:12%;color:#1E7E34">Σ Recettes</td><td class="r" style="width:12%;color:#C0392B">Dépenses</td><td class="r" style="width:8%">Solde</td></tr>';

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $rMan = $revByDayN[$d]        ?? 0;
            $rCot = $cotisByDayN[$d]      ?? 0;
            $rEnv = $envDayN['total'][$d] ?? 0;
            $r6   = $envDayN['6pct'][$d]  ?? 0;
            $r12  = $envDayN['12pct'][$d] ?? 0;
            $r21  = $envDayN['21pct'][$d] ?? 0;
            $rAll = $rMan + $rCot + $rEnv;
            $eD   = $expByDayN[$d]        ?? 0;
            $sD   = $rAll - $eD;
            $h .= '<tr>';
            $h .= '<td class="r">' . $d . '</td>';
            $h .= '<td class="r">' . ($rMan > 0 ? $fmt($rMan) : '—') . '</td>';
            $h .= '<td class="r">' . ($rCot > 0 ? $fmt($rCot) : '—') . '</td>';
            $h .= '<td class="r">' . ($r6   > 0 ? $fmt($r6)   : '—') . '</td>';
            $h .= '<td class="r">' . ($r12  > 0 ? $fmt($r12)  : '—') . '</td>';
            $h .= '<td class="r">' . ($r21  > 0 ? $fmt($r21)  : '—') . '</td>';
            $h .= '<td class="r"' . ($rAll > 0 ? ' style="color:#1E7E34"' : '') . '>' . ($rAll > 0 ? $fmt($rAll) : '—') . '</td>';
            $h .= '<td class="r"' . ($eD   > 0 ? ' style="color:#C0392B"' : '') . '>' . ($eD > 0 ? $fmt($eD) : '—') . '</td>';
            $sStyle = ($rAll > 0 || $eD > 0) ? ' style="color:' . $gc($sD) . '"' : '';
            $h .= '<td class="r"' . $sStyle . '>' . (($rAll > 0 || $eD > 0) ? $fmt($sD) : '—') . '</td>';
            $h .= '</tr>';
        }

        $h .= '<tr class="tot"><td>TOTAL</td><td class="r">' . $fmt($totalRevManual) . '</td><td class="r">' . $fmt($totalCotis) . '</td>';
        $h .= '<td class="r">' . ($total6  > 0 ? $fmt($total6)  : '—') . '</td>';
        $h .= '<td class="r">' . ($total12 > 0 ? $fmt($total12) : '—') . '</td>';
        $h .= '<td class="r">' . $fmt($total21) . '</td>';
        $h .= '<td class="r" style="color:#1E7E34">' . $fmt($totalRev) . '</td>';
        $h .= '<td class="r" style="color:#C0392B">' . $fmt($totalExp) . '</td>';
        $h .= '<td class="r" style="color:' . $gc($solde) . '">' . $fmt($solde) . '</td></tr>';
        $h .= '</table></body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($h, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $monthPad = str_pad($month, 2, '0', STR_PAD_LEFT);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="bilan_journalier_' . $year . '_' . $monthPad . '.pdf"');
        header('Cache-Control: max-age=0');
        echo $dompdf->output();
        exit;
    }

    public function exportQuarter(): void
    {
        $expModel = new TreasuryExpenseModel();
        $revModel = new TreasuryRevenueModel();

        $year    = (int) ($this->request->getGet('year')    ?? date('Y'));
        $quarter = max(1, min(4, (int) ($this->request->getGet('quarter') ?? 1)));

        $qMonths = $this->quarterMonths($quarter);
        $qLabel  = "T{$quarter}";
        $qName   = $this->quarterLabel($quarter);

        $revByMonthN   = $revModel->getMonthlyTotals($year);
        $cotisMonthlyN = $this->getCotisationsByMonth($year);
        $envMonthlyN   = $this->getEnvelopesByMonth($year);
        $envVatN       = $this->getEnvelopesByMonthAndVat($year);
        $expByMonthN   = $expModel->getMonthlyTotals($year);

        $totalRevManualQ = $this->sumMonths($revByMonthN,   $qMonths);
        $totalCotisQ     = $this->sumMonths($cotisMonthlyN, $qMonths);
        $totalEnvQ       = $this->sumMonths($envMonthlyN,   $qMonths);
        $totalRevQ       = $totalRevManualQ + $totalCotisQ + $totalEnvQ;
        $totalExpQ       = $this->sumMonths($expByMonthN,   $qMonths);
        $soldeQ          = $totalRevQ - $totalExpQ;
        $revByCatQ       = $this->getRevCatByMonths($revModel, $year, $qMonths);
        $expByCatQ       = $this->getExpCatByMonths($expModel, $year, $qMonths);

        $fullMonths = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet();
        $sheet->setTitle("Bilan {$qLabel} {$year}");

        $headerStyle  = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $sectionStyle = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']]];
        $boldStyle    = ['font' => ['bold' => true]];
        $rightAlign   = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
        $totalRowStyle = ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]];
        $greenText    = ['font' => ['color' => ['rgb' => '1E7E34']]];
        $redText      = ['font' => ['color' => ['rgb' => 'C0392B']]];

        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', "BILAN FINANCIER — {$qLabel} {$year} — RBC DISONAIS");
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', "{$qName} {$year}  |  Exporté le : " . date('d/m/Y'));
        $sheet->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '555555']]]);

        // Récapitulatif
        $r = 4;
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", "RÉCAPITULATIF — {$qLabel} {$year}");
        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($sectionStyle);
        $r++;
        $sheet->setCellValue("A{$r}", 'Recettes totales'); $sheet->setCellValue("B{$r}", $fmt($totalRevQ));
        $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $greenText));
        $r++;
        $sheet->setCellValue("A{$r}", 'Dépenses totales'); $sheet->setCellValue("B{$r}", $fmt($totalExpQ));
        $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $redText));
        $r++;
        $sheet->setCellValue("A{$r}", 'Solde'); $sheet->setCellValue("B{$r}", $fmt($soldeQ));
        $sheet->getStyle("A{$r}:B{$r}")->applyFromArray($boldStyle);
        $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $soldeQ >= 0 ? $greenText : $redText));

        // Sources recettes
        $r += 2;
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", "SOURCES DES RECETTES — {$qLabel} {$year}");
        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray($sectionStyle);
        foreach ([['Recettes manuelles', $totalRevManualQ], ['Cotisations', $totalCotisQ], ['Bar / Enveloppes', $totalEnvQ]] as [$label, $amount]) {
            $r++;
            $sheet->setCellValue("A{$r}", $label); $sheet->setCellValue("B{$r}", $fmt($amount));
            $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $greenText));
        }
        $r++;
        $sheet->setCellValue("A{$r}", 'TOTAL'); $sheet->setCellValue("B{$r}", $fmt($totalRevQ));
        $sheet->getStyle("A{$r}:B{$r}")->applyFromArray(array_merge($totalRowStyle, ['font' => ['bold' => true, 'color' => ['rgb' => '1E7E34']]]));
        $sheet->getStyle("B{$r}")->applyFromArray($rightAlign);

        // Évolution mensuelle (3 mois)
        $r += 2;
        $sheet->mergeCells("A{$r}:I{$r}");
        $sheet->setCellValue("A{$r}", 'ÉVOLUTION MENSUELLE');
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($sectionStyle);
        $r++;
        foreach (['Mois', 'Rec. man.', 'Cotisations', 'Bar 6%', 'Bar 12%', 'Bar 21%', 'Total rec.', 'Dépenses', 'Solde'] as $ci => $h) {
            $sheet->setCellValue(chr(65 + $ci) . $r, $h);
        }
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($boldStyle);

        foreach ($qMonths as $m) {
            $r++;
            $r6  = $envVatN['6pct'][$m]  ?? 0;
            $r12 = $envVatN['12pct'][$m] ?? 0;
            $r21 = $envVatN['21pct'][$m] ?? 0;
            $rM  = ($revByMonthN[$m] ?? 0) + ($cotisMonthlyN[$m] ?? 0) + ($envMonthlyN[$m] ?? 0);
            $eM  = $expByMonthN[$m] ?? 0;
            $sM  = $rM - $eM;
            $sheet->setCellValue("A{$r}", $fullMonths[$m - 1]);
            $sheet->setCellValue("B{$r}", $revByMonthN[$m]   > 0 ? $fmt($revByMonthN[$m])   : '—');
            $sheet->setCellValue("C{$r}", $cotisMonthlyN[$m] > 0 ? $fmt($cotisMonthlyN[$m]) : '—');
            $sheet->setCellValue("D{$r}", $r6  > 0 ? $fmt($r6)  : '—');
            $sheet->setCellValue("E{$r}", $r12 > 0 ? $fmt($r12) : '—');
            $sheet->setCellValue("F{$r}", $r21 > 0 ? $fmt($r21) : '—');
            $sheet->setCellValue("G{$r}", $rM  > 0 ? $fmt($rM)  : '—');
            $sheet->setCellValue("H{$r}", $eM  > 0 ? $fmt($eM)  : '—');
            $sheet->setCellValue("I{$r}", ($rM > 0 || $eM > 0) ? $fmt($sM) : '—');
            if ($rM > 0) $sheet->getStyle("G{$r}")->applyFromArray($greenText);
            if ($eM > 0) $sheet->getStyle("H{$r}")->applyFromArray($redText);
            if ($rM > 0 || $eM > 0) $sheet->getStyle("I{$r}")->applyFromArray($sM >= 0 ? $greenText : $redText);
        }
        $r++;
        $t6 = array_sum(array_intersect_key($envVatN['6pct'],  array_flip($qMonths)));
        $t12 = array_sum(array_intersect_key($envVatN['12pct'], array_flip($qMonths)));
        $t21 = array_sum(array_intersect_key($envVatN['21pct'], array_flip($qMonths)));
        $sheet->setCellValue("A{$r}", 'TOTAL');
        $sheet->setCellValue("B{$r}", $fmt($totalRevManualQ)); $sheet->setCellValue("C{$r}", $fmt($totalCotisQ));
        $sheet->setCellValue("D{$r}", $t6  > 0 ? $fmt($t6)  : '—');
        $sheet->setCellValue("E{$r}", $t12 > 0 ? $fmt($t12) : '—');
        $sheet->setCellValue("F{$r}", $fmt($t21));
        $sheet->setCellValue("G{$r}", $fmt($totalRevQ)); $sheet->setCellValue("H{$r}", $fmt($totalExpQ)); $sheet->setCellValue("I{$r}", $fmt($soldeQ));
        $sheet->getStyle("A{$r}:I{$r}")->applyFromArray($totalRowStyle);
        $sheet->getStyle("G{$r}")->applyFromArray($greenText); $sheet->getStyle("H{$r}")->applyFromArray($redText);
        $sheet->getStyle("I{$r}")->applyFromArray($soldeQ >= 0 ? $greenText : $redText);

        // Catégories recettes
        $r += 2;
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", "RECETTES PAR CATÉGORIE — {$qLabel} {$year}");
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($sectionStyle);
        $r++;
        $sheet->setCellValue("A{$r}", 'Catégorie'); $sheet->setCellValue("B{$r}", 'Montant'); $sheet->setCellValue("C{$r}", '%');
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($boldStyle);
        foreach (TreasuryRevenueModel::$categories as $key => $label) {
            $am = $revByCatQ[$key] ?? 0;
            if ($am <= 0) continue;
            $r++;
            $pct = $totalRevManualQ > 0 ? round($am / $totalRevManualQ * 100) : 0;
            $sheet->setCellValue("A{$r}", $label); $sheet->setCellValue("B{$r}", $fmt($am)); $sheet->setCellValue("C{$r}", $pct . ' %');
            $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $greenText));
        }

        // Catégories dépenses
        $r += 2;
        $sheet->mergeCells("A{$r}:C{$r}");
        $sheet->setCellValue("A{$r}", "DÉPENSES PAR CATÉGORIE — {$qLabel} {$year}");
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($sectionStyle);
        $r++;
        $sheet->setCellValue("A{$r}", 'Catégorie'); $sheet->setCellValue("B{$r}", 'Montant'); $sheet->setCellValue("C{$r}", '%');
        $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($boldStyle);
        foreach (TreasuryExpenseModel::$categories as $key => $label) {
            $am = $expByCatQ[$key] ?? 0;
            if ($am <= 0) continue;
            $r++;
            $pct = $totalExpQ > 0 ? round($am / $totalExpQ * 100) : 0;
            $sheet->setCellValue("A{$r}", $label); $sheet->setCellValue("B{$r}", $fmt($am)); $sheet->setCellValue("C{$r}", $pct . ' %');
            $sheet->getStyle("B{$r}")->applyFromArray(array_merge($rightAlign, $redText));
        }

        foreach (['A' => 28, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 18, 'H' => 18, 'I' => 18] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = "bilan_rbcd_{$year}_{$qLabel}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($sp))->save('php://output');
        exit;
    }

    public function exportQuarterPdf(): void
    {
        $expModel = new TreasuryExpenseModel();
        $revModel = new TreasuryRevenueModel();

        $year    = (int) ($this->request->getGet('year')    ?? date('Y'));
        $quarter = max(1, min(4, (int) ($this->request->getGet('quarter') ?? 1)));

        $qMonths = $this->quarterMonths($quarter);
        $qLabel  = "T{$quarter}";
        $qName   = $this->quarterLabel($quarter);

        $revByMonthN   = $revModel->getMonthlyTotals($year);
        $cotisMonthlyN = $this->getCotisationsByMonth($year);
        $envMonthlyN   = $this->getEnvelopesByMonth($year);
        $envVatN       = $this->getEnvelopesByMonthAndVat($year);
        $expByMonthN   = $expModel->getMonthlyTotals($year);

        $totalRevManualQ = $this->sumMonths($revByMonthN,   $qMonths);
        $totalCotisQ     = $this->sumMonths($cotisMonthlyN, $qMonths);
        $totalEnvQ       = $this->sumMonths($envMonthlyN,   $qMonths);
        $totalRevQ       = $totalRevManualQ + $totalCotisQ + $totalEnvQ;
        $totalExpQ       = $this->sumMonths($expByMonthN,   $qMonths);
        $soldeQ          = $totalRevQ - $totalExpQ;
        $revByCatQ       = $this->getRevCatByMonths($revModel, $year, $qMonths);
        $expByCatQ       = $this->getExpCatByMonths($expModel, $year, $qMonths);

        $fullMonths = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';
        $gc  = fn(float $v): string => $v >= 0 ? '#1E7E34' : '#C0392B';

        $css = 'body{font-family:DejaVu Sans,sans-serif;font-size:8pt;margin:0;padding:8px;color:#222;}'
             . 'table{width:100%;border-collapse:collapse;margin-bottom:10px;}'
             . 'td,th{padding:2px 4px;border:1px solid #ddd;}'
             . '.hdr{background:#1F3864;color:#fff;font-size:11pt;font-weight:bold;text-align:center;padding:6px;}'
             . '.sub{color:#555;font-style:italic;font-size:7.5pt;padding:2px 0 8px;}'
             . '.sec td{background:#2E75B6;color:#fff;font-weight:bold;border:none;padding:3px 6px;}'
             . '.tot td{background:#F2F2F2;font-weight:bold;}'
             . '.bld td{font-weight:bold;}'
             . '.r{text-align:right;}';

        $h  = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>';
        $h .= '<div class="hdr">BILAN FINANCIER — ' . $qLabel . ' ' . $year . ' — RBC DISONAIS</div>';
        $h .= '<div class="sub">' . $qName . ' ' . $year . '  |  Exporté le : ' . date('d/m/Y') . '</div>';

        // Récapitulatif
        $h .= '<table><tr class="sec"><td colspan="2">RÉCAPITULATIF — ' . $qLabel . ' ' . $year . '</td></tr>';
        $h .= '<tr><td style="width:60%">Recettes totales</td><td class="r" style="color:#1E7E34">' . $fmt($totalRevQ) . '</td></tr>';
        $h .= '<tr><td>Dépenses totales</td><td class="r" style="color:#C0392B">' . $fmt($totalExpQ) . '</td></tr>';
        $h .= '<tr class="bld"><td>Solde</td><td class="r" style="color:' . $gc($soldeQ) . '">' . $fmt($soldeQ) . '</td></tr>';
        $h .= '</table>';

        // Sources recettes
        $h .= '<table><tr class="sec"><td colspan="3">SOURCES DES RECETTES — ' . $qLabel . ' ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:60%">Source</td><td class="r" style="width:25%">Montant</td><td class="r" style="width:15%">%</td></tr>';
        foreach ([['Recettes manuelles', $totalRevManualQ], ['Cotisations', $totalCotisQ], ['Bar / Enveloppes', $totalEnvQ]] as [$label, $amount]) {
            $pct = $totalRevQ > 0 ? round($amount / $totalRevQ * 100) : 0;
            $h .= '<tr><td>' . $label . '</td><td class="r" style="color:#1E7E34">' . $fmt($amount) . '</td><td class="r">' . $pct . ' %</td></tr>';
        }
        $h .= '<tr class="tot"><td>TOTAL RECETTES</td><td class="r" style="color:#1E7E34">' . $fmt($totalRevQ) . '</td><td class="r">100 %</td></tr>';
        $h .= '</table>';

        // Évolution mensuelle
        $h .= '<table><tr class="sec"><td colspan="9">ÉVOLUTION MENSUELLE — ' . $qLabel . ' ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:12%">Mois</td><td class="r" style="width:11%">Rec. man.</td><td class="r" style="width:11%">Cotisations</td><td class="r" style="width:11%">Bar 6%</td><td class="r" style="width:11%">Bar 12%</td><td class="r" style="width:11%">Bar 21%</td><td class="r" style="width:11%;color:#1E7E34">Σ Recettes</td><td class="r" style="width:11%;color:#C0392B">Dépenses</td><td class="r" style="width:11%">Solde</td></tr>';
        $tManual = $tCotis = $t6 = $t12 = $t21 = $tRev = $tExp = 0.0;
        foreach ($qMonths as $m) {
            $r6  = $envVatN['6pct'][$m]  ?? 0;
            $r12 = $envVatN['12pct'][$m] ?? 0;
            $r21 = $envVatN['21pct'][$m] ?? 0;
            $rM  = ($revByMonthN[$m] ?? 0) + ($cotisMonthlyN[$m] ?? 0) + ($envMonthlyN[$m] ?? 0);
            $eM  = $expByMonthN[$m] ?? 0;
            $sM  = $rM - $eM;
            $tManual += $revByMonthN[$m] ?? 0; $tCotis += $cotisMonthlyN[$m] ?? 0;
            $t6 += $r6; $t12 += $r12; $t21 += $r21; $tRev += $rM; $tExp += $eM;
            $h .= '<tr>';
            $h .= '<td style="text-align:right;padding-right:8px">' . $fullMonths[$m - 1] . '</td>';
            $h .= '<td class="r">' . ($revByMonthN[$m]   > 0 ? $fmt($revByMonthN[$m])   : '—') . '</td>';
            $h .= '<td class="r">' . ($cotisMonthlyN[$m] > 0 ? $fmt($cotisMonthlyN[$m]) : '—') . '</td>';
            $h .= '<td class="r">' . ($r6  > 0 ? $fmt($r6)  : '—') . '</td>';
            $h .= '<td class="r">' . ($r12 > 0 ? $fmt($r12) : '—') . '</td>';
            $h .= '<td class="r">' . ($r21 > 0 ? $fmt($r21) : '—') . '</td>';
            $h .= '<td class="r"' . ($rM > 0 ? ' style="color:#1E7E34"' : '') . '>' . ($rM > 0 ? $fmt($rM) : '—') . '</td>';
            $h .= '<td class="r"' . ($eM > 0 ? ' style="color:#C0392B"' : '') . '>' . ($eM > 0 ? $fmt($eM) : '—') . '</td>';
            $h .= '<td class="r"' . (($rM > 0 || $eM > 0) ? ' style="color:' . $gc($sM) . '"' : '') . '>' . (($rM > 0 || $eM > 0) ? $fmt($sM) : '—') . '</td>';
            $h .= '</tr>';
        }
        $tSolde = $tRev - $tExp;
        $h .= '<tr class="tot"><td>TOTAL</td><td class="r">' . $fmt($tManual) . '</td><td class="r">' . $fmt($tCotis) . '</td>';
        $h .= '<td class="r">' . ($t6  > 0 ? $fmt($t6)  : '—') . '</td>';
        $h .= '<td class="r">' . ($t12 > 0 ? $fmt($t12) : '—') . '</td>';
        $h .= '<td class="r">' . $fmt($t21) . '</td>';
        $h .= '<td class="r" style="color:#1E7E34">' . $fmt($tRev) . '</td>';
        $h .= '<td class="r" style="color:#C0392B">' . $fmt($tExp) . '</td>';
        $h .= '<td class="r" style="color:' . $gc($tSolde) . '">' . $fmt($tSolde) . '</td></tr>';
        $h .= '</table>';

        // Recettes par catégorie
        $h .= '<table><tr class="sec"><td colspan="3">RECETTES PAR CATÉGORIE — ' . $qLabel . ' ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:60%">Catégorie</td><td class="r" style="width:25%">Montant</td><td class="r" style="width:15%">%</td></tr>';
        foreach (TreasuryRevenueModel::$categories as $key => $label) {
            $am = $revByCatQ[$key] ?? 0;
            if ($am <= 0) continue;
            $pct = $totalRevManualQ > 0 ? round($am / $totalRevManualQ * 100) : 0;
            $h .= '<tr><td>' . esc($label) . '</td><td class="r" style="color:#1E7E34">' . $fmt($am) . '</td><td class="r">' . $pct . ' %</td></tr>';
        }
        $h .= '</table>';

        // Dépenses par catégorie
        $h .= '<table><tr class="sec"><td colspan="3">DÉPENSES PAR CATÉGORIE — ' . $qLabel . ' ' . $year . '</td></tr>';
        $h .= '<tr class="bld"><td style="width:60%">Catégorie</td><td class="r" style="width:25%">Montant</td><td class="r" style="width:15%">%</td></tr>';
        foreach (TreasuryExpenseModel::$categories as $key => $label) {
            $am = $expByCatQ[$key] ?? 0;
            if ($am <= 0) continue;
            $pct = $totalExpQ > 0 ? round($am / $totalExpQ * 100) : 0;
            $h .= '<tr><td>' . esc($label) . '</td><td class="r" style="color:#C0392B">' . $fmt($am) . '</td><td class="r">' . $pct . ' %</td></tr>';
        }
        $h .= '</table></body></html>';

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($h, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="bilan_rbcd_' . $year . '_' . $qLabel . '.pdf"');
        header('Cache-Control: max-age=0');
        echo $dompdf->output();
        exit;
    }

    // ── Helpers privés

    private function quarterMonths(int $quarter): array
    {
        return match($quarter) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
            default => [],
        };
    }

    private function quarterLabel(int $quarter): string
    {
        return match($quarter) {
            1 => '1er trimestre (Jan — Mar)',
            2 => '2e trimestre (Avr — Jun)',
            3 => '3e trimestre (Jul — Sep)',
            4 => '4e trimestre (Oct — Déc)',
            default => '',
        };
    }

    private function sumMonths(array $monthly, array $months): float
    {
        return (float) array_sum(array_intersect_key($monthly, array_flip($months)));
    }

    private function getRevCatByMonths(TreasuryRevenueModel $model, int $year, array $months): array
    {
        $rows = $model->db->table('treasury_revenues')
            ->select('category, SUM(amount) as total')
            ->where('YEAR(revenue_date)', $year)
            ->whereIn('MONTH(revenue_date)', $months)
            ->groupBy('category')
            ->get()->getResultObject();
        $result = [];
        foreach ($rows as $r) {
            $result[$r->category] = (float) $r->total;
        }
        return $result;
    }

    private function getExpCatByMonths(TreasuryExpenseModel $model, int $year, array $months): array
    {
        $rows = $model->db->table('treasury_expenses')
            ->select('category, SUM(amount) as total')
            ->where('YEAR(expense_date)', $year)
            ->whereIn('MONTH(expense_date)', $months)
            ->groupBy('category')
            ->get()->getResultObject();
        $result = [];
        foreach ($rows as $r) {
            $result[$r->category] = (float) $r->total;
        }
        return $result;
    }

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

    private function getRevenuesByDay(int $year, int $month): array
    {
        $result = array_fill(1, cal_days_in_month(CAL_GREGORIAN, $month, $year), 0.0);
        $rows = $this->db->table('treasury_revenues')
            ->select('DAY(revenue_date) as d, SUM(amount) as total')
            ->where('YEAR(revenue_date)', $year)->where('MONTH(revenue_date)', $month)
            ->groupBy('DAY(revenue_date)')->get()->getResultArray();
        foreach ($rows as $row) $result[(int)$row['d']] = (float)$row['total'];
        return $result;
    }

    private function getCotisationsByDay(int $year, int $month): array
    {
        $result = array_fill(1, cal_days_in_month(CAL_GREGORIAN, $month, $year), 0.0);
        $rows = $this->db->table('member_payments')
            ->select('DAY(rbcd_paid_date) as d, COUNT(*) as cnt')
            ->where('rbcd_paid', 1)->where('rbcd_paid_date IS NOT NULL')
            ->where('YEAR(rbcd_paid_date)', $year)->where('MONTH(rbcd_paid_date)', $month)
            ->groupBy('DAY(rbcd_paid_date)')->get()->getResultArray();
        foreach ($rows as $row) $result[(int)$row['d']] += (int)$row['cnt'] * $this->cotisAmount;

        foreach (['forfait_f1_paid_date' => 'forfait_f1_paid', 'forfait_f2_paid_date' => 'forfait_f2_paid'] as $dateCol => $paidCol) {
            $rows = $this->db->table('member_payments')
                ->select("DAY($dateCol) as d, COUNT(*) as cnt")
                ->where($paidCol, 1)->where("$dateCol IS NOT NULL")
                ->where("YEAR($dateCol)", $year)->where("MONTH($dateCol)", $month)
                ->groupBy("DAY($dateCol)")->get()->getResultArray();
            foreach ($rows as $row) $result[(int)$row['d']] += (int)$row['cnt'] * $this->forfaitAmount;
        }
        return $result;
    }

    private function getEnvelopesByDayAndVat(int $year, int $month): array
    {
        $n     = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $total = array_fill(1, $n, 0.0);
        $pct6  = array_fill(1, $n, 0.0);
        $pct12 = array_fill(1, $n, 0.0);
        $pct21 = array_fill(1, $n, 0.0);

        $rows = $this->db->table('treasury_envelopes')
            ->select('DAY(date) as d, SUM(amount_found) as tot, SUM(IFNULL(amount_6pct,0)) as s6, SUM(IFNULL(amount_12pct,0)) as s12')
            ->where('YEAR(date)', $year)->where('MONTH(date)', $month)
            ->groupBy('DAY(date)')->get()->getResultArray();

        foreach ($rows as $row) {
            $d = (int)$row['d'];
            $s6 = (float)$row['s6']; $s12 = (float)$row['s12']; $tot = (float)$row['tot'];
            $total[$d] = $tot; $pct6[$d] = $s6; $pct12[$d] = $s12; $pct21[$d] = $tot - $s6 - $s12;
        }
        return ['total' => $total, '6pct' => $pct6, '12pct' => $pct12, '21pct' => $pct21];
    }

    private function getExpensesByDay(int $year, int $month): array
    {
        $result = array_fill(1, cal_days_in_month(CAL_GREGORIAN, $month, $year), 0.0);
        $rows = $this->db->table('treasury_expenses')
            ->select('DAY(expense_date) as d, SUM(amount) as total')
            ->where('YEAR(expense_date)', $year)->where('MONTH(expense_date)', $month)
            ->groupBy('DAY(expense_date)')->get()->getResultArray();
        foreach ($rows as $row) $result[(int)$row['d']] = (float)$row['total'];
        return $result;
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
