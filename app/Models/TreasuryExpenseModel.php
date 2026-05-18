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
        'amount', 'payment_method', 'notes', 'member_id', 'created_at',
    ];

    public static array $categories = [
        'enveloppes_prix'  => 'Primes des finalistes reçues',
        'achat_materiel'   => 'Achat matériel',
        'commune'          => 'Commune',
        'frais_sportifs'   => 'Frais sportifs',
        'proximus'         => 'Proximus',
        'cafe_liegeois'    => 'Café Liégeois',
        'boulanger'        => 'Boulanger - Brasseur',
        'colruyt'          => 'Colruyt',
        'intermarche'      => 'Intermarché',
        'bnp'              => 'BNP Paribas Fortis',
        'nicolay'          => 'Pierre Nicolay - vin',
        'gv_compta'        => 'GV Compta',
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
            ->select('te.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = te.member_id', 'left')
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

    public function getMonthlyTotals(int $year): array
    {
        $result = array_fill(1, 12, 0.0);
        $rows = $this->db->table('treasury_expenses')
            ->select('MONTH(expense_date) as m, SUM(amount) as total')
            ->where('YEAR(expense_date)', $year)
            ->groupBy('MONTH(expense_date)')
            ->get()->getResultArray();
        foreach ($rows as $row) {
            $result[(int) $row['m']] = (float) $row['total'];
        }
        return $result;
    }
}
