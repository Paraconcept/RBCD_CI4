<?php

namespace App\Models;

use CodeIgniter\Model;

class TreasuryRevenueModel extends Model
{
    protected $table         = 'treasury_revenues';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'revenue_date', 'category', 'description',
        'amount', 'payment_method', 'notes', 'member_id', 'created_at',
    ];

    public static array $categories = [
        'subside'      => 'Subside',
        'sponsor'      => 'Sponsor / Mécénat',
        'remise_prix'  => 'Primes des finalistes reçues',
        'divers'       => 'Divers',
    ];

    public static array $paymentMethods = [
        'caisse'   => 'Liquide',
        'virement' => 'Virement',
    ];

    public function getByYear(int $year): array
    {
        return $this->db->table('treasury_revenues tr')
            ->select('tr.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = tr.member_id', 'left')
            ->where('YEAR(tr.revenue_date)', $year)
            ->orderBy('tr.revenue_date', 'DESC')
            ->get()->getResultObject();
    }

    public function getTotalByYear(int $year): float
    {
        $result = $this->db->table('treasury_revenues')
            ->selectSum('amount', 'total')
            ->where('YEAR(revenue_date)', $year)
            ->get()->getRowObject();
        return (float) ($result->total ?? 0);
    }

    public function getTotalByYearAndCategory(int $year): array
    {
        $rows = $this->db->table('treasury_revenues')
            ->select('category, SUM(amount) as total')
            ->where('YEAR(revenue_date)', $year)
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
        $rows = $this->db->table('treasury_revenues')
            ->select('MONTH(revenue_date) as m, SUM(amount) as total')
            ->where('YEAR(revenue_date)', $year)
            ->groupBy('MONTH(revenue_date)')
            ->get()->getResultArray();
        foreach ($rows as $row) {
            $result[(int) $row['m']] = (float) $row['total'];
        }
        return $result;
    }
}
