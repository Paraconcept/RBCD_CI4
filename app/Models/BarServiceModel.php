<?php

namespace App\Models;

use CodeIgniter\Model;

class BarServiceModel extends Model
{
    public const PERIODS = ['am' => 'Après-midi', 'soir' => 'Soir'];

    // Après cette heure, la caisse propose le service du soir par défaut
    public const EVENING_FROM = '17:00';

    protected $table         = 'bar_services';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'service_date', 'period', 'number', 'envelope_code', 'status', 'bartender_member_id',
        'opened_at', 'closed_at', 'closed_by_member_id', 'paid_count', 'cancelled_count',
        'cash_total', 'bancontact_total', 'vat_b_total', 'vat_g_total',
    ];

    /** Même nomenclature que Trésorerie › Enveloppes : E + JJ.MM. + numéro du service dans la journée. */
    public static function envelopeCode(string $date, int $number): string
    {
        return 'E' . date('d.m.', strtotime($date)) . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    }

    public static function numberFor(string $period): int
    {
        return $period === 'am' ? 1 : 2;
    }

    public static function periodLabel(string $period): string
    {
        return $period === 'am' ? "service de l'après-midi" : 'service du soir';
    }

    public function getOpen(): ?object
    {
        return $this->withNames()->where('s.status', 'open')->get()->getRowObject();
    }

    public function getWithNames(int $id): ?object
    {
        return $this->withNames()->where('s.id', $id)->get()->getRowObject();
    }

    public function getLastClosed(): ?object
    {
        return $this->withNames()->where('s.status', 'closed')->orderBy('s.closed_at', 'DESC')->limit(1)->get()->getRowObject();
    }

    public function getForAdmin(?int $year = null): array
    {
        $builder = $this->withNames();
        if ($year) {
            $builder->where('YEAR(s.service_date)', $year);
        }
        return $builder->orderBy('s.service_date', 'DESC')->orderBy('s.number', 'DESC')->get()->getResultObject();
    }

    private function withNames()
    {
        return $this->db->table('bar_services s')
            ->select("s.*, CONCAT(b.last_name, ' ', b.first_name) AS bartender_name, CONCAT(cl.last_name, ' ', cl.first_name) AS closer_name")
            ->join('members b', 'b.id = s.bartender_member_id', 'left')
            ->join('members cl', 'cl.id = s.closed_by_member_id', 'left');
    }
}
