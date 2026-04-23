<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberPaymentModel extends Model
{
    protected $table      = 'member_payments';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'member_id',
        'year',
        'rbcd_paid',
        'rbcd_paid_date',
        'frbb_paid',
        'frbb_paid_date',
        'forfait_h1_paid',
        'forfait_h1_paid_date',
        'forfait_h2_paid',
        'forfait_h2_paid_date',
    ];

    public function createForMember(int $memberId, ?int $year = null): void
    {
        $this->insert([
            'member_id' => $memberId,
            'year'      => $year ?? (int) date('Y'),
        ]);
    }

    public function getForMember(int $memberId): array
    {
        return $this->where('member_id', $memberId)
                    ->orderBy('year', 'DESC')
                    ->findAll();
    }
}
