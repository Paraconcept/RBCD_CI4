<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleEncounterPlayerModel extends Model
{
    protected $table         = 'schedule_encounter_players';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $returnType    = 'object';
    protected $createdField  = 'created_at';

    protected $allowedFields = [
        'encounter_id', 'member_id', 'opponent_name', 'created_at',
    ];

    public function deleteByEncounter(int $encounterId): void
    {
        $this->where('encounter_id', $encounterId)->delete();
    }
}
