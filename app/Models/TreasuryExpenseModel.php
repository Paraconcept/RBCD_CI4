<?php

namespace App\Models;

use CodeIgniter\Model;

class TreasuryExpenseModel extends Model
{
    protected $table         = 'treasury_expenses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'expense_date', 'category', 'description',
        'amount', 'payment_method', 'notes', 'admin_user_id', 'created_at',
    ];

    public static array $categories = [
        'enveloppes_prix'  => 'Enveloppes / Prix',
        'achat_materiel'   => 'Achat matériel',
        'frais_admin'      => 'Frais administratifs',
        'frais_sportifs'   => 'Frais sportifs',
        'divers'           => 'Divers',
    ];

    public static array $paymentMethods = [
        'caisse'   => 'Caisse',
        'virement' => 'Virement',
        'carte'    => 'Carte',
    ];

    public function getByYear(int $year): array
    {
        return $this->db->table('treasury_expenses te')
            ->select('te.*, au.last_name, au.first_name')
            ->join('admin_users au', 'au.id = te.admin_user_id', 'left')
            ->where('YEAR(te.expense_date)', $year)
            ->orderBy('te.expense_date', 'DESC')
            ->get()->getResultObject();
    }

    public function getTotalByYear(int $year): float
    {
        $result = $this->db->table('treasury_expenses')
            ->selectSum('amount', 'total')
            ->where('YEAR(expense_date)', $year)
            ->get()->getRowObject();
        return (float) ($result->total ?? 0);
    }

    public function getTotalByYearAndCategory(int $year): array
    {
        $rows = $this->db->table('treasury_expenses')
            ->select('category, SUM(amount) as total')
            ->where('YEAR(expense_date)', $year)
            ->groupBy('category')
            ->get()->getResultObject();

        $result = [];
        foreach ($rows as $r) {
            $result[$r->category] = (float) $r->total;
        }
        return $result;
    }
}
