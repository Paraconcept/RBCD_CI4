<?php

namespace App\Models;

use CodeIgniter\Model;

class TreasuryEnvelopeModel extends Model
{
    protected $table      = 'treasury_envelopes';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'date', 'category', 'amount_calculated', 'amount_found',
        'closed_by_member_id', 'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithCloser(?int $year = null): array
    {
        $builder = $this->db->table('treasury_envelopes te')
            ->select([
                'te.*',
                "CONCAT(m.last_name, ' ', m.first_name) AS closer_name",
            ])
            ->join('members m', 'm.id = te.closed_by_member_id', 'left');

        if ($year) {
            $builder->where('YEAR(te.date)', $year);
        }

        return $builder->orderBy('te.date', 'DESC')->get()->getResultObject();
    }
}
