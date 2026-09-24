<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberClubFeeModel extends Model
{
    protected $table      = 'member_club_fees';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'member_id',
        'year',
        'rbcd_h1_paid',
        'rbcd_h1_paid_date',
        'rbcd_h1_amount',
        'rbcd_h2_paid',
        'rbcd_h2_paid_date',
        'rbcd_h2_amount',
        'forfait_h1_choice',
        'forfait_h1_paid',
        'forfait_h1_paid_date',
        'forfait_h2_choice',
        'forfait_h2_paid',
        'forfait_h2_paid_date',
    ];

    public function getForMember(int $memberId): array
    {
        return $this->where('member_id', $memberId)
                    ->orderBy('year', 'DESC')
                    ->findAll();
    }

    public function findForYear(int $memberId, int $year): ?object
    {
        return $this->where('member_id', $memberId)->where('year', $year)->first();
    }

    /**
     * Saison FRBB affichée en regard d'une année civile : la saison en cours pour
     * l'année actuelle (bascule en août), sinon celle qui a démarré cette année-là.
     */
    public static function frbbSeasonFor(int $year): int
    {
        return min($year, ANNEE_1);
    }
}
