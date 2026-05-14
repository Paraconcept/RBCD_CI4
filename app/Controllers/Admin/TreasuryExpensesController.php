<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TreasuryExpenseModel;

class TreasuryExpensesController extends BaseController
{
    private TreasuryExpenseModel $model;

    public function __construct()
    {
        $this->model = new TreasuryExpenseModel();
    }

    public function index(): string
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));

        $rows       = $this->model->getByYear($year);
        $total      = $this->model->getTotalByYear($year);
        $byCategory = $this->model->getTotalByYearAndCategory($year);

        $years = $this->getAvailableYears();

        return view('admin/treasury_expenses/index', [
            'title'       => 'Dépenses',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Dépenses'],
            ],
            'rows'       => $rows,
            'total'      => $total,
            'byCategory' => $byCategory,
            'year'       => $year,
            'years'      => $years,
            'categories' => TreasuryExpenseModel::$categories,
            'paymentMethods' => TreasuryExpenseModel::$paymentMethods,
            'success'    => session()->getFlashdata('success'),
            'error'      => session()->getFlashdata('error'),
        ]);
    }

    public function create(): string
    {
        return view('admin/treasury_expenses/form', [
            'title'       => 'Nouvelle dépense',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Dépenses', 'url' => base_url('admin/treasury/expenses')],
                ['title' => 'Nouvelle dépense'],
            ],
            'expense'        => null,
            'categories'     => TreasuryExpenseModel::$categories,
            'paymentMethods' => TreasuryExpenseModel::$paymentMethods,
        ]);
    }

    public function store()
    {
        if (!$this->validateForm()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'expense_date'   => $this->request->getPost('expense_date'),
            'category'       => $this->request->getPost('category'),
            'description'    => $this->request->getPost('description'),
            'amount'         => (float) str_replace(',', '.', $this->request->getPost('amount')),
            'payment_method' => $this->request->getPost('payment_method'),
            'notes'          => $this->request->getPost('notes') ?: null,
            'admin_user_id'  => session()->get('admin_id') ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/treasury/expenses'))
                         ->with('success', 'Dépense enregistrée.');
    }

    public function edit(int $id): string
    {
        $expense = $this->model->find($id);
        if (!$expense) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/treasury_expenses/form', [
            'title'       => 'Modifier la dépense',
            'breadcrumbs' => [
                ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')],
                ['title' => 'Dépenses', 'url' => base_url('admin/treasury/expenses')],
                ['title' => 'Modifier'],
            ],
            'expense'        => $expense,
            'categories'     => TreasuryExpenseModel::$categories,
            'paymentMethods' => TreasuryExpenseModel::$paymentMethods,
        ]);
    }

    public function update(int $id)
    {
        $expense = $this->model->find($id);
        if (!$expense) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validateForm()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'expense_date'   => $this->request->getPost('expense_date'),
            'category'       => $this->request->getPost('category'),
            'description'    => $this->request->getPost('description'),
            'amount'         => (float) str_replace(',', '.', $this->request->getPost('amount')),
            'payment_method' => $this->request->getPost('payment_method'),
            'notes'          => $this->request->getPost('notes') ?: null,
        ]);

        $year = (int) (new \DateTime($this->request->getPost('expense_date')))->format('Y');

        return redirect()->to(base_url("admin/treasury/expenses?year={$year}"))
                         ->with('success', 'Dépense modifiée.');
    }

    public function delete(int $id)
    {
        $expense = $this->model->find($id);
        if (!$expense) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $year = (int) (new \DateTime($expense->expense_date))->format('Y');
        $this->model->delete($id);

        return redirect()->to(base_url("admin/treasury/expenses?year={$year}"))
                         ->with('success', 'Dépense supprimée.');
    }

    private function validateForm(): bool
    {
        return $this->validate([
            'expense_date' => 'required|valid_date[Y-m-d]',
            'category'     => 'required',
            'description'  => 'required|max_length[255]',
            'amount'       => 'required|decimal|greater_than[0]',
        ]);
    }

    private function getAvailableYears(): array
    {
        $rows = $this->db->table('treasury_expenses')
            ->select('YEAR(expense_date) as y')
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
