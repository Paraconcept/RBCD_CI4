<?php

namespace App\Models;

use CodeIgniter\Model;

class BarTicketPaymentModel extends Model
{
    protected $table         = 'bar_ticket_payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['ticket_id', 'method', 'amount'];

    public const METHODS = ['cash' => 'espèces', 'bancontact' => 'Bancontact'];

    /** Parts de plusieurs tickets, rangées par ticket, dans l'ordre de saisie. */
    public function forTickets(array $ticketIds): array
    {
        if (!$ticketIds) {
            return [];
        }
        $byTicket = [];
        foreach ($this->whereIn('ticket_id', $ticketIds)->orderBy('id')->findAll() as $p) {
            $byTicket[(int) $p->ticket_id][] = $p;
        }
        return $byTicket;
    }

    /** « 30,00 € en espèces + 15,00 € en Bancontact » : total par mode, espèces d'abord. */
    public static function describe(array $payments): string
    {
        $byMethod = [];
        foreach ($payments as $p) {
            $byMethod[$p->method] = ($byMethod[$p->method] ?? 0) + (float) $p->amount;
        }
        $parts = [];
        foreach (self::METHODS as $m => $label) {
            if (isset($byMethod[$m])) {
                $parts[] = number_format($byMethod[$m], 2, ',', ' ') . ' € en ' . $label;
            }
        }
        return implode(' + ', $parts);
    }
}
