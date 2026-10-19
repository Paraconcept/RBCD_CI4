<?php

namespace App\Models;

use CodeIgniter\Model;

class BarTicketLineModel extends Model
{
    protected $table         = 'bar_ticket_lines';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['ticket_id', 'product_id', 'product_name', 'unit_price', 'vat_group', 'quantity'];

    /** Lignes de plusieurs tickets, rangées par ticket. */
    public function forTickets(array $ticketIds): array
    {
        if (!$ticketIds) {
            return [];
        }
        $byTicket = [];
        foreach ($this->whereIn('ticket_id', $ticketIds)->orderBy('id')->findAll() as $l) {
            $byTicket[(int) $l->ticket_id][] = $l;
        }
        return $byTicket;
    }
}
