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
        'frbb_paid',
        'frbb_paid_date',
    ];

    public function createForMember(int $memberId, ?int $startYear = null): void
    {
        $this->insert([
            'member_id' => $memberId,
            'year'      => $startYear ?? ANNEE_1,
        ]);
    }

    public function findForSeason(int $memberId, int $seasonYear): ?object
    {
        return $this->where('member_id', $memberId)->where('year', $seasonYear)->first();
    }

    public function getForMember(int $memberId): array
    {
        return $this->where('member_id', $memberId)
                    ->orderBy('year', 'DESC')
                    ->findAll();
    }
}
