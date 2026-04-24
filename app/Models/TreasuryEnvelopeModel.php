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
        'closed_by_member_id', 'encoded_by_member_id', 'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithCloser(?int $year = null): array
    {
        $builder = $this->db->table('treasury_envelopes te')
            ->select([
                'te.*',
                "CONCAT(mc.last_name, ' ', mc.first_name) AS closer_name",
                "CONCAT(me.last_name, ' ', me.first_name) AS encoder_name",
            ])
            ->join('members mc', 'mc.id = te.closed_by_member_id', 'left')
            ->join('members me', 'me.id = te.encoded_by_member_id', 'left');

        if ($year) {
            $builder->where('YEAR(te.date)', $year);
        }

        return $builder->orderBy('te.date', 'DESC')->get()->getResultObject();
    }
}
