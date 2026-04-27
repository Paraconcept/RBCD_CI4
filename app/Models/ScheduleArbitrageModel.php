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
        'encounter_id', 'admin_user_id', 'assignment_type',
        'confirmed', 'confirmed_at',
    ];

    public function getForEncounter(int $encounterId): ?object
    {
        return $this->db->table('schedule_arbitrage sa')
            ->select('sa.*, au.last_name, au.first_name, au.id as user_id')
            ->join('admin_users au', 'au.id = sa.admin_user_id')
            ->where('sa.encounter_id', $encounterId)
            ->get()->getRowObject();
    }

    public function getForEncounters(array $encounterIds): array
    {
        if (empty($encounterIds)) {
            return [];
        }

        $rows = $this->db->table('schedule_arbitrage sa')
            ->select('sa.*, au.last_name, au.first_name')
            ->join('admin_users au', 'au.id = sa.admin_user_id')
            ->whereIn('sa.encounter_id', $encounterIds)
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->encounter_id] = $row;
        }
        return $indexed;
    }
}
