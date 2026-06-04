<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TreasuryEnvelopeModel;
use App\Models\MemberKeyModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TreasuryEnvelopesController extends BaseController
{
    private TreasuryEnvelopeModel $model;
    private MemberKeyModel $keyModel;

    public function __construct()
    {
        $this->model    = new TreasuryEnvelopeModel();
        $this->keyModel = new MemberKeyModel();
    }

    public function index(): string
    {
        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('n');

        $year  = (int) ($this->request->getGet('year')  ?? $currentYear);
        $month = (int) ($this->request->getGet('month') ?? $currentMonth);

        $maxMonth = ($year >= $currentYear) ? $currentMonth : 12;
        if ($month < 1 || $month > $maxMonth) {
            $month = $maxMonth;
        }

        $rows = $this->model->getWithCloser($year, $month);

        $monthNames = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $byMonth = [];
        foreach ($rows as $r) {
            $m   = (int) date('n', strtotime($r->date));
            $key = date('Y-m', strtotime($r->date));
            if (!isset($byMonth[$key])) {
                $byMonth[$key] = [
                    'label'      => $monthNames[$m] . ' ' . date('Y', strtotime($r->date)),
                    'calculated' => 0.0,
                    'found'      => 0.0,
                    'pct6'       => 0.0,
                    'pct12'      => 0.0,
                    'sumup'      => 0.0,
                    'rows'       => [],
                ];
            }
            $byMonth[$key]['calculated'] += (float) $r->amount_calculated;
            $byMonth[$key]['found']      += (float) $r->amount_found;
            $byMonth[$key]['pct6']       += (float) ($r->amount_6pct  ?? 0);
            $byMonth[$key]['pct12']      += (float) ($r->amount_12pct ?? 0);
            $byMonth[$key]['sumup']      += (float) $r->amount_sumup;
            $byMonth[$key]['rows'][]      = $r;
        }

        $db    = \Config\Database::connect();
        $years = $db->table('treasury_envelopes')
            ->select('YEAR(date) AS y')->distinct()
            ->orderBy('y', 'DESC')
            ->get()->getResultArray();
        $years = array_column($years, 'y');
        if (!in_array((string) $year, $years)) {
            array_unshift($years, (string) $year);
        }

        $currentMemberId = (int) session()->get('member_id');

        return view('admin/treasury_envelopes/index', [
            'title'       => 'Enveloppes de caisse',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Enveloppes'],
            ],
            'byMonth'         => $byMonth,
            'year'            => $year,
            'years'           => $years,
            'month'           => $month,
            'maxMonth'        => $maxMonth,
            'monthNames'      => $monthNames,
            'currentYear'     => $currentYear,
            'currentMonth'    => $currentMonth,
            'currentMemberId' => $currentMemberId,
        ]);
    }

    public function create(): string
    {
        return view('admin/treasury_envelopes/form', [
            'title'       => 'Nouvelle enveloppe',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Enveloppes', 'url' => base_url('admin/treasury/envelopes')],
                ['title' => 'Nouvelle'],
            ],
            'envelope'   => null,
            'keyHolders' => $this->keyModel->getActiveKeyHolders(),
            'formAction' => base_url('admin/treasury/envelopes'),
            'usedNames'  => $this->getUsedNames(),
        ]);
    }

    public function store()
    {
        if (!$this->validate([
            'date'                => 'required|valid_date',
            'name_seq'            => 'required|in_list[01,02,03,04,05]',
            'amount_calculated'   => 'required|decimal|greater_than_equal_to[0]',
            'amount_found'        => 'required|decimal|greater_than_equal_to[0]',
            'amount_6pct'         => 'permit_empty|decimal|greater_than_equal_to[0]',
            'amount_12pct'        => 'permit_empty|decimal|greater_than_equal_to[0]',
            'amount_sumup'        => 'required|decimal|greater_than_equal_to[0]',
            'closed_by_member_id' => 'required|is_natural_no_zero',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $name = 'E' . date('d.m.', strtotime($post['date'])) . $post['name_seq'];

        if ($this->model->where('name', $name)->countAllResults() > 0) {
            return redirect()->back()->withInput()
                ->with('errors', ['name' => "L'enveloppe <strong>{$name}</strong> existe déjà."]);
        }

        $data = $this->collectData();
        $data['name']                 = $name;
        $data['encoded_by_member_id'] = (int) session()->get('member_id') ?: null;

        $this->model->insert($data);

        return redirect()->to(base_url('admin/treasury/envelopes'))->with('success', 'Enveloppe enregistrée.');
    }

    public function edit(int $id): string
    {
        $envelope = $this->model->find($id);
        if (!$envelope) {
            return redirect()->to(base_url('admin/treasury/envelopes'))->with('error', 'Enveloppe introuvable.');
        }

        return view('admin/treasury_envelopes/form', [
            'title'       => 'Modifier l\'enveloppe',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Enveloppes', 'url' => base_url('admin/treasury/envelopes')],
                ['title' => 'Modifier'],
            ],
            'envelope'   => $envelope,
            'keyHolders' => $this->keyModel->getActiveKeyHolders(),
            'formAction' => base_url('admin/treasury/envelopes/' . $id . '/update'),
        ]);
    }

    public function update(int $id)
    {
        if (!$this->model->find($id)) {
            return redirect()->to(base_url('admin/treasury/envelopes'))->with('error', 'Enveloppe introuvable.');
        }

        if (!$this->validate([
            'closed_by_member_id' => 'required|is_natural_no_zero',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $this->model->update($id, [
            'closed_by_member_id'   => $post['closed_by_member_id'] ?: null,
            'notes'                 => $post['notes'] ?: null,
            'modified_by_member_id' => (int) session()->get('member_id') ?: null,
        ]);

        return redirect()->to(base_url('admin/treasury/envelopes'))->with('success', 'Enveloppe mise à jour.');
    }

    public function delete(int $id)
    {
        $envelope = $this->model->find($id);
        if (!$envelope) {
            return redirect()->to(base_url('admin/treasury/envelopes'))->with('error', 'Enveloppe introuvable.');
        }

        $currentMemberId = (int) session()->get('member_id');

        if ($envelope->encoded_by_member_id !== null && (int) $envelope->encoded_by_member_id !== $currentMemberId) {
            return redirect()->to(base_url('admin/treasury/envelopes'))->with('error', 'Vous ne pouvez supprimer que les enveloppes que vous avez encodées.');
        }

        $this->model->delete($id);
        return redirect()->to(base_url('admin/treasury/envelopes'))->with('success', 'Enveloppe supprimée.');
    }

    public function export()
    {
        $year  = (int) ($this->request->getGet('year')  ?? date('Y'));
        $month = (int) ($this->request->getGet('month') ?? date('n'));
        $rows  = $this->model->getWithCloser($year, $month);
        usort($rows, fn($a, $b) => $a->date !== $b->date
            ? strcmp($a->date, $b->date)
            : strcmp($a->name ?? '', $b->name ?? ''));

        $monthAbbr  = ['','janv','févr','mars','avr','mai','juin','juil','août','sept','oct','nov','déc'];
        $monthLabel = $monthAbbr[$month] . '-' . substr((string) $year, 2, 2);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Recettes $monthLabel");

        $fmt = fn(float $v): string => number_format($v, 2, ',', ' ');

        $titleStyle = [
            'font'      => ['bold' => true, 'underline' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $subtitleStyle = [
            'font'      => ['bold' => true, 'underline' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $colHeaderStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $totalRowStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
        ];
        $subRowStyle = [
            'font' => ['italic' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
        ];
        $rightAlign = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];

        // ── Titre
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Recettes caisse');
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $sheet->getRowDimension(1)->setRowHeight(20);

        // ── Sous-titre mois
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', $monthLabel);
        $sheet->getStyle('A2')->applyFromArray($subtitleStyle);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ── En-têtes (ligne 4)
        foreach (['A' => 'Date', 'B' => 'LIBELLÉ', 'C' => 'Total', 'D' => '6%', 'E' => '12%', 'F' => '21%'] as $col => $label) {
            $sheet->setCellValue($col . '4', $label);
        }
        $sheet->getStyle('A4:F4')->applyFromArray($colHeaderStyle);
        $sheet->getRowDimension(4)->setRowHeight(18);

        // ── Données
        $row = 5;
        $sumTotal = $sum6 = $sum12 = $sum21 = 0.0;

        foreach ($rows as $r) {
            $r6    = (float) ($r->amount_6pct  ?? 0);
            $r12   = (float) ($r->amount_12pct ?? 0);
            $r21   = (float) $r->amount_found - $r6 - $r12;
            $total = (float) $r->amount_found;
            $sumTotal += $total; $sum6 += $r6; $sum12 += $r12; $sum21 += $r21;

            $sheet->setCellValue('A' . $row, date('d-m-Y', strtotime($r->date)));
            $sheet->setCellValue('B' . $row, $r->name ?? '');
            $sheet->setCellValue('C' . $row, $fmt($total));
            $sheet->setCellValue('D' . $row, $r6  > 0 ? $fmt($r6)  : '');
            $sheet->setCellValue('E' . $row, $r12 > 0 ? $fmt($r12) : '');
            $sheet->setCellValue('F' . $row, $fmt($r21));
            $sheet->getStyle("C{$row}:F{$row}")->applyFromArray($rightAlign);
            $row++;
        }

        $row++; // ligne vide

        // ── TOTAL TVA C
        $sheet->setCellValue("A{$row}", 'TOTAL TVA C');
        $sheet->setCellValue("C{$row}", $fmt($sumTotal));
        $sheet->setCellValue("D{$row}", $sum6  > 0 ? $fmt($sum6)  : '');
        $sheet->setCellValue("E{$row}", $sum12 > 0 ? $fmt($sum12) : '');
        $sheet->setCellValue("F{$row}", $fmt($sum21));
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($totalRowStyle);
        $sheet->getStyle("C{$row}:F{$row}")->applyFromArray($rightAlign);
        $row++;

        // ── TOTAL HTVA
        $htva6     = $sum6  > 0 ? round($sum6  / 1.06, 2) : 0.0;
        $htva12    = $sum12 > 0 ? round($sum12 / 1.12, 2) : 0.0;
        $htva21    = round($sum21 / 1.21, 2);
        $htvaTotal = round($htva6 + $htva12 + $htva21, 2);

        $sheet->setCellValue("A{$row}", 'TOTAL HTVA');
        $sheet->setCellValue("C{$row}", $fmt($htvaTotal));
        $sheet->setCellValue("D{$row}", $sum6  > 0 ? $fmt($htva6)  : '');
        $sheet->setCellValue("E{$row}", $sum12 > 0 ? $fmt($htva12) : '');
        $sheet->setCellValue("F{$row}", $fmt($htva21));
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($subRowStyle);
        $sheet->getStyle("C{$row}:F{$row}")->applyFromArray($rightAlign);
        $row++;

        // ── TVA
        $tva6     = round($sum6  - $htva6,  2);
        $tva12    = round($sum12 - $htva12, 2);
        $tva21    = round($sum21 - $htva21, 2);
        $tvaTotal = round($tva6 + $tva12 + $tva21, 2);

        $sheet->setCellValue("A{$row}", 'TVA');
        $sheet->setCellValue("C{$row}", $fmt($tvaTotal));
        $sheet->setCellValue("D{$row}", $sum6  > 0 ? $fmt($tva6)  : '');
        $sheet->setCellValue("E{$row}", $sum12 > 0 ? $fmt($tva12) : '');
        $sheet->setCellValue("F{$row}", $fmt($tva21));
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($subRowStyle);
        $sheet->getStyle("C{$row}:F{$row}")->applyFromArray($rightAlign);
        $row += 2; // ligne vide

        // ── Vérification
        $sheet->setCellValue("A{$row}", 'Vérification');
        $sheet->setCellValue("C{$row}", $fmt($sumTotal));
        $sheet->getStyle("A{$row}")->applyFromArray(['font' => ['italic' => true]]);
        $sheet->getStyle("C{$row}")->applyFromArray($rightAlign);

        // ── Largeurs de colonnes
        foreach (['A' => 13, 'B' => 18, 'C' => 14, 'D' => 12, 'E' => 12, 'F' => 14] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = "recettes_caisse_{$year}_{$month}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ----------------------------------------------------------------

    private function getUsedNames(): array
    {
        $rows = $this->model->select('name')->where('name IS NOT NULL')->findAll();
        return array_column(array_map(fn($r) => (array)$r, $rows), 'name');
    }

    private function collectData(): array
    {
        $post = $this->request->getPost();
        return [
            'date'                => $post['date'],
            'category'            => 'bar',
            'amount_calculated'   => (float) $post['amount_calculated'],
            'amount_found'        => (float) $post['amount_found'],
            'amount_6pct'         => ($post['amount_6pct']  !== '' && $post['amount_6pct']  !== null) ? (float) $post['amount_6pct']  : null,
            'amount_12pct'        => ($post['amount_12pct'] !== '' && $post['amount_12pct'] !== null) ? (float) $post['amount_12pct'] : null,
            'amount_sumup'        => (float) $post['amount_sumup'],
            'closed_by_member_id' => ($post['closed_by_member_id'] ?: null),
            'notes'               => $post['notes'] ?: null,
        ];
    }
}
