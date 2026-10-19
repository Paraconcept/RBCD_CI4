<?php

namespace App\Models;

use CodeIgniter\Model;

class BarTicketModel extends Model
{
    protected $table         = 'bar_tickets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'number', 'service_id', 'client_id', 'status', 'payment_method', 'total',
        'opened_at', 'paid_at', 'cancelled_at', 'cancelled_by_member_id', 'cancel_reason', 'updated_at',
    ];

    public static function formatNumber(int $number): string
    {
        return str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /** Tickets d'un service avec le nom du client, du plus ancien au plus récent. */
    public function forService(int $serviceId, ?array $statuses = null): array
    {
        $builder = $this->db->table('bar_tickets t')
            ->select('t.*, c.name AS client_name, c.member_id AS client_member_id')
            ->join('bar_clients c', 'c.id = t.client_id')
            ->where('t.service_id', $serviceId);
        if ($statuses) {
            $builder->whereIn('t.status', $statuses);
        }
        return $builder->orderBy('t.number')->get()->getResultObject();
    }

    public function withClient(int $id): ?object
    {
        return $this->db->table('bar_tickets t')
            ->select('t.*, c.name AS client_name')
            ->join('bar_clients c', 'c.id = t.client_id')
            ->where('t.id', $id)
            ->get()->getRowObject();
    }

    /** Numéro suivant, sans trou : à appeler dans la transaction qui insère le ticket. */
    public function nextNumber(): int
    {
        $row = $this->db->query('SELECT MAX(number) AS n FROM bar_tickets FOR UPDATE')->getRowArray();
        return (int) ($row['n'] ?? 0) + 1;
    }
}
