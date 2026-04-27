<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleArbitrageModel extends Model
{
    protected $table         = 'schedule_arbitrage';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $allowedFields = [
        'encounter_id', 'member_id', 'round', 'admin_user_id', 'assignment_type',
        'confirmed', 'confirmed_at',
    ];

    // Returns [encounter_id => [row, row, ...]] — multiple rows per encounter for finales
    public function getForEncounters(array $encounterIds): array
    {
        if (empty($encounterIds)) {
            return [];
        }

        $rows = $this->db->table('schedule_arbitrage sa')
            ->select('sa.*, COALESCE(m.last_name, au.last_name) AS last_name, COALESCE(m.first_name, au.first_name) AS first_name')
            ->join('members m',     'm.id = sa.member_id',     'left')
            ->join('admin_users au','au.id = sa.admin_user_id','left')
            ->whereIn('sa.encounter_id', $encounterIds)
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->encounter_id][] = $row;
        }
        return $indexed;
    }

    // Get single user's signup for an encounter (UNIQUE enc+user guarantees at most 1)
    public function getUserSignup(int $encounterId, int $userId): ?object
    {
        return $this->where('encounter_id', $encounterId)
                    ->where('admin_user_id', $userId)
                    ->first();
    }
}
