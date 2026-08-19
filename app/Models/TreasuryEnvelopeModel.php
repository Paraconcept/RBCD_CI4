<?php

namespace App\Models;

use CodeIgniter\Model;

class TreasuryEnvelopeModel extends Model
{
    protected $table      = 'treasury_envelopes';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'name', 'date', 'category', 'amount_calculated', 'amount_found',
        'amount_6pct', 'amount_12pct', 'amount_21pct_g', 'amount_sumup',
        'closed_by_member_id', 'encoded_by_member_id', 'modified_by_member_id', 'notes',
    ];

    // TVA : ventilation 6%/12%/21% (boissons) jusqu'au 30/06/2026, puis G (gougouilles) / B (bar) à 21% depuis le 01/07/2026.
    public const VAT_CUTOFF = '2026-07-01';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithCloser(?int $year = null, ?int $month = null): array
    {
        $builder = $this->db->table('treasury_envelopes te')
            ->select([
                'te.*',
                "CONCAT(mc.last_name, ' ', mc.first_name) AS closer_name",
                "CONCAT(me.last_name, ' ', me.first_name) AS encoder_name",
                "CONCAT(mm.last_name, ' ', mm.first_name) AS modifier_name",
            ])
            ->join('members mc', 'mc.id = te.closed_by_member_id', 'left')
            ->join('members me', 'me.id = te.encoded_by_member_id', 'left')
            ->join('members mm', 'mm.id = te.modified_by_member_id', 'left');

        if ($year)  { $builder->where('YEAR(te.date)',  $year);  }
        if ($month) { $builder->where('MONTH(te.date)', $month); }

        return $builder->orderBy('te.date', 'ASC')->get()->getResultObject();
    }
}
