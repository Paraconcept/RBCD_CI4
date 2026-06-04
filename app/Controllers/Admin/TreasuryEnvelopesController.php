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

        $monthNames = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Enveloppes $month-$year");

        $fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $colHeaderStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $totalRowStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
        ];
        $rightAlign = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];
        $greenText  = ['font' => ['color' => ['rgb' => '1E7E34']]];
        $redText    = ['font' => ['color' => ['rgb' => 'C0392B']]];

        // ── Titre
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'ENVELOPPES DE CAISSE — RBC DISONAIS');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Sous-titre
        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', $monthNames[$month] . ' ' . $year . '  |  Exporté le : ' . date('d/m/Y'));
        $sheet->getStyle('A2')->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '555555']]]);

        // ── En-têtes colonnes (ligne 4)
        foreach ([
            'A' => 'Nom', 'B' => 'Date', 'C' => 'Catégorie',
            'D' => 'Calculé', 'E' => '6%', 'F' => '12%', 'G' => '21%',
            'H' => 'SumUp', 'I' => 'Écart',
            'J' => 'Fermé par', 'K' => 'Encodé par', 'L' => 'Notes',
        ] as $col => $label) {
            $sheet->setCellValue($col . '4', $label);
        }
        $sheet->getStyle('A4:L4')->applyFromArray($colHeaderStyle);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Données
        $row = 5;
        $totCalc = $tot6 = $tot12 = $tot21 = $totSumup = $totEcart = 0.0;

        foreach ($rows as $r) {
            $r6    = (float) ($r->amount_6pct  ?? 0);
            $r12   = (float) ($r->amount_12pct ?? 0);
            $r21   = (float) $r->amount_found - $r6 - $r12;
            $ecart = (float) $r->amount_found + (float) $r->amount_sumup - (float) $r->amount_calculated;
            $totCalc  += (float) $r->amount_calculated;
            $tot6     += $r6; $tot12 += $r12; $tot21 += $r21;
            $totSumup += (float) $r->amount_sumup;
            $totEcart += $ecart;

            $sheet->setCellValue('A' . $row, $r->name);
            $sheet->setCellValue('B' . $row, $r->date);
            $sheet->setCellValue('C' . $row, $r->category);
            $sheet->setCellValue('D' . $row, $fmt((float) $r->amount_calculated));
            $sheet->setCellValue('E' . $row, $r6  > 0 ? $fmt($r6)  : '');
            $sheet->setCellValue('F' . $row, $r12 > 0 ? $fmt($r12) : '');
            $sheet->setCellValue('G' . $row, $fmt($r21));
            $sheet->setCellValue('H' . $row, $fmt((float) $r->amount_sumup));
            $sheet->setCellValue('I' . $row, ($ecart >= 0 ? '+' : '') . number_format($ecart, 2, ',', '.') . ' €');
            $sheet->setCellValue('J' . $row, $r->closer_name  ?? '');
            $sheet->setCellValue('K' . $row, $r->encoder_name ?? '');
            $sheet->setCellValue('L' . $row, $r->notes ?? '');
            $sheet->getStyle("D{$row}:I{$row}")->applyFromArray($rightAlign);
            $sheet->getStyle("I{$row}")->applyFromArray($ecart >= 0 ? $greenText : $redText);
            $row++;
        }

        // ── Total
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->setCellValue("D{$row}", $fmt($totCalc));
        $sheet->setCellValue("E{$row}", $tot6  > 0 ? $fmt($tot6)  : '—');
        $sheet->setCellValue("F{$row}", $tot12 > 0 ? $fmt($tot12) : '—');
        $sheet->setCellValue("G{$row}", $fmt($tot21));
        $sheet->setCellValue("H{$row}", $fmt($totSumup));
        $sign = $totEcart >= 0 ? '+' : '';
        $sheet->setCellValue("I{$row}", $sign . number_format($totEcart, 2, ',', '.') . ' €');
        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray($totalRowStyle);
        $sheet->getStyle("D{$row}:I{$row}")->applyFromArray($rightAlign);
        $sheet->getStyle("I{$row}")->applyFromArray($totEcart >= 0 ? $greenText : $redText);

        // ── Largeurs de colonnes
        foreach (['A' => 14, 'B' => 13, 'C' => 13, 'D' => 15, 'E' => 13, 'F' => 13,
                  'G' => 15, 'H' => 15, 'I' => 14, 'J' => 22, 'K' => 22, 'L' => 30] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = "enveloppes_{$year}_{$month}.xlsx";
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
