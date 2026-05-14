<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TreasuryExpenseModel;
use App\Models\TreasuryRevenueModel;

class TreasuryBilanController extends BaseController
{
    public function index(): string
    {
        $expModel = new TreasuryExpenseModel();
        $revModel = new TreasuryRevenueModel();

        $year     = (int) ($this->request->getGet('year') ?? date('Y'));
        $prevYear = $year - 1;

        $totalRevN    = $revModel->getTotalByYear($year);
        $totalExpN    = $expModel->getTotalByYear($year);
        $totalRevNm1  = $revModel->getTotalByYear($prevYear);
        $totalExpNm1  = $expModel->getTotalByYear($prevYear);

        $revByMonthN   = $revModel->getMonthlyTotals($year);
        $expByMonthN   = $expModel->getMonthlyTotals($year);
        $revByMonthNm1 = $revModel->getMonthlyTotals($prevYear);
        $expByMonthNm1 = $expModel->getMonthlyTotals($prevYear);

        $revByCatN = $revModel->getTotalByYearAndCategory($year);
        $expByCatN = $expModel->getTotalByYearAndCategory($year);

        return view('admin/treasury_bilan/index', [
            'title'       => 'Bilan financier',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Bilan'],
            ],
            'year'          => $year,
            'prevYear'      => $prevYear,
            'years'         => $this->getAvailableYears(),
            'totalRevN'     => $totalRevN,
            'totalExpN'     => $totalExpN,
            'totalRevNm1'   => $totalRevNm1,
            'totalExpNm1'   => $totalExpNm1,
            'revByMonthN'   => $revByMonthN,
            'expByMonthN'   => $expByMonthN,
            'revByMonthNm1' => $revByMonthNm1,
            'expByMonthNm1' => $expByMonthNm1,
            'revByCatN'     => $revByCatN,
            'expByCatN'     => $expByCatN,
            'expCategories' => TreasuryExpenseModel::$categories,
            'revCategories' => TreasuryRevenueModel::$categories,
        ]);
    }

    private function getAvailableYears(): array
    {
        $db = \Config\Database::connect();

        $revYears = array_column(
            $db->table('treasury_revenues')
               ->select('YEAR(revenue_date) as y')->distinct()
               ->get()->getResultArray(),
            'y'
        );
        $expYears = array_column(
            $db->table('treasury_expenses')
               ->select('YEAR(expense_date) as y')->distinct()
               ->get()->getResultArray(),
            'y'
        );

        $years = array_unique(array_merge($revYears, $expYears));
        rsort($years);

        if (!in_array(date('Y'), $years)) {
            array_unshift($years, (int) date('Y'));
        }
        return $years;
    }
}
