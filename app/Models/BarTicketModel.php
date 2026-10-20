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
    /** Réglage Admin › Caisse › Paramètres : numéro du premier ticket, pour suivre les tickets papier. */
    public const START_SETTING = 'bar_ticket_start';

    public static function startNumber(): int
    {
        return max(1, (int) (new SiteSettingModel())->getSetting(self::START_SETTING, 1));
    }

    /** Dernier numéro attribué (0 s'il n'y a encore aucun ticket). */
    public function lastNumber(): int
    {
        $row = $this->db->query('SELECT MAX(number) AS n FROM bar_tickets')->getRowArray();
        return (int) ($row['n'] ?? 0);
    }

    /**
     * Le numéro de départ ne peut jamais faire reculer la numérotation : un numéro déjà utilisé n'est pas réattribué.
     * $lock : à l'intérieur de la transaction qui crée le ticket, pour que deux appareils n'obtiennent pas le même numéro.
     */
    public function nextNumber(bool $lock = true): int
    {
        $row = $this->db->query('SELECT MAX(number) AS n FROM bar_tickets' . ($lock ? ' FOR UPDATE' : ''))->getRowArray();
        return max((int) ($row['n'] ?? 0) + 1, self::startNumber());
    }
}
