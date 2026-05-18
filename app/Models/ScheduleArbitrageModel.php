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
        'encounter_id', 'member_id', 'round', 'assignment_type',
        'confirmed', 'confirmed_at',
    ];

    // Returns [encounter_id => [row, row, ...]] — multiple rows per encounter for finales
    public function getForEncounters(array $encounterIds): array
    {
        if (empty($encounterIds)) {
            return [];
        }

        $rows = $this->db->table('schedule_arbitrage sa')
            ->select('sa.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = sa.member_id', 'left')
            ->whereIn('sa.encounter_id', $encounterIds)
            ->where('sa.member_id IS NOT NULL')
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->encounter_id][] = $row;
        }
        return $indexed;
    }

    // Get single member's volunteer signup for an encounter
    public function getUserSignup(int $encounterId, int $memberId): ?object
    {
        return $this->where('encounter_id', $encounterId)
                    ->where('member_id', $memberId)
                    ->where('assignment_type', 'volunteer')
                    ->first();
    }
}
