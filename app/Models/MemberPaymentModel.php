<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberPaymentModel extends Model
{
    protected $table      = 'member_payments';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'member_id',
        'year',
        'rbcd_paid',
        'rbcd_paid_date',
        'frbb_paid',
        'frbb_paid_date',
        'forfait_f1_choice',
        'forfait_f1_paid',
        'forfait_f1_paid_date',
        'forfait_f2_choice',
        'forfait_f2_paid',
        'forfait_f2_paid_date',
    ];

    public function createForMember(int $memberId, ?int $startYear = null): void
    {
        $this->insert([
            'member_id' => $memberId,
            'year'      => $startYear ?? ANNEE_1,
        ]);
    }

    public function getForMember(int $memberId): array
    {
        return $this->where('member_id', $memberId)
                    ->orderBy('year', 'DESC')
                    ->findAll();
    }
}
