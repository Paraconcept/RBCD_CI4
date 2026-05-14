<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TreasuryRevenueModel;

class TreasuryRevenuesController extends BaseController
{
    private TreasuryRevenueModel $model;

    public function __construct()
    {
        $this->model = new TreasuryRevenueModel();
    }

    public function index(): string
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $rows       = $this->model->getByYear($year);
        $total      = $this->model->getTotalByYear($year);
        $byCategory = $this->model->getTotalByYearAndCategory($year);

        $years = $this->getAvailableYears();

        return view('admin/treasury_revenues/index', [
            'title'       => 'Recettes',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Recettes'],
            ],
            'rows'           => $rows,
            'total'          => $total,
            'byCategory'     => $byCategory,
            'year'           => $year,
            'years'          => $years,
            'categories'     => TreasuryRevenueModel::$categories,
            'paymentMethods' => TreasuryRevenueModel::$paymentMethods,
            'success'        => session()->getFlashdata('success'),
            'error'          => session()->getFlashdata('error'),
        ]);
    }

    public function create(): string
    {
        return view('admin/treasury_revenues/form', [
            'title'       => 'Nouvelle recette',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Recettes', 'url' => base_url('admin/treasury/revenues')],
                ['title' => 'Nouvelle recette'],
            ],
            'revenue'        => null,
            'categories'     => TreasuryRevenueModel::$categories,
            'paymentMethods' => TreasuryRevenueModel::$paymentMethods,
        ]);
    }

    public function store()
    {
        if (!$this->validateForm()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'revenue_date'   => $this->request->getPost('revenue_date'),
            'category'       => $this->request->getPost('category'),
            'description'    => $this->request->getPost('description'),
            'amount'         => (float) str_replace(',', '.', $this->request->getPost('amount')),
            'payment_method' => $this->request->getPost('payment_method'),
            'notes'          => $this->request->getPost('notes') ?: null,
            'admin_user_id'  => session()->get('admin_id') ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/treasury/revenues'))
                         ->with('success', 'Recette enregistrée.');
    }

    public function edit(int $id): string
    {
        $revenue = $this->model->find($id);
        if (!$revenue) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/treasury_revenues/form', [
            'title'       => 'Modifier la recette',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Recettes', 'url' => base_url('admin/treasury/revenues')],
                ['title' => 'Modifier'],
            ],
            'revenue'        => $revenue,
            'categories'     => TreasuryRevenueModel::$categories,
            'paymentMethods' => TreasuryRevenueModel::$paymentMethods,
        ]);
    }

    public function update(int $id)
    {
        $revenue = $this->model->find($id);
        if (!$revenue) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validateForm()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'revenue_date'   => $this->request->getPost('revenue_date'),
            'category'       => $this->request->getPost('category'),
            'description'    => $this->request->getPost('description'),
            'amount'         => (float) str_replace(',', '.', $this->request->getPost('amount')),
            'payment_method' => $this->request->getPost('payment_method'),
            'notes'          => $this->request->getPost('notes') ?: null,
        ]);

        $year = (int) (new \DateTime($this->request->getPost('revenue_date')))->format('Y');

        return redirect()->to(base_url("admin/treasury/revenues?year={$year}"))
                         ->with('success', 'Recette modifiée.');
    }

    public function delete(int $id)
    {
        $revenue = $this->model->find($id);
        if (!$revenue) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $year = (int) (new \DateTime($revenue->revenue_date))->format('Y');
        $this->model->delete($id);

        return redirect()->to(base_url("admin/treasury/revenues?year={$year}"))
                         ->with('success', 'Recette supprimée.');
    }

    private function validateForm(): bool
    {
        return $this->validate([
            'revenue_date' => 'required|valid_date[Y-m-d]',
            'category'     => 'required',
            'description'  => 'required|max_length[255]',
            'amount'       => 'required|decimal|greater_than[0]',
        ]);
    }

    private function getAvailableYears(): array
    {
        $db   = \Config\Database::connect();
        $rows = $db->table('treasury_revenues')
            ->select('YEAR(revenue_date) as y')
            ->distinct()
            ->orderBy('y', 'DESC')
            ->get()->getResultArray();

        $years = array_column($rows, 'y');
        if (!in_array(date('Y'), $years)) {
            array_unshift($years, (int) date('Y'));
        }
        return $years;
    }
}
