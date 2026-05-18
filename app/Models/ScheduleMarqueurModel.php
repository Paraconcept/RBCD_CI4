<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleMarqueurModel extends Model
{
    protected $table         = 'schedule_marqueurs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $allowedFields = ['encounter_id', 'member_id', 'round'];

    public function getForEncounters(array $encounterIds): array
    {
        if (empty($encounterIds)) {
            return [];
        }

        $rows = $this->db->table('schedule_marqueurs sm')
            ->select('sm.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = sm.member_id', 'left')
            ->whereIn('sm.encounter_id', $encounterIds)
            ->where('sm.member_id IS NOT NULL')
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->encounter_id][] = $row;
        }
        return $indexed;
    }

    public function getUserSignup(int $encounterId, int $memberId): ?object
    {
        return $this->where('encounter_id', $encounterId)
                    ->where('member_id', $memberId)
                    ->first();
    }
}
