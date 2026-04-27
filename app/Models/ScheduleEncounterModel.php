<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleEncounterModel extends Model
{
    protected $table         = 'schedule_encounters';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $allowedFields = [
        'encounter_type', 'rounds_count', 'match_date', 'match_time', 'is_home', 'venue',
        'competition', 'notes',
    ];

    public function getWeek(int $week, int $year): array
    {
        $firstDay = (new \DateTime())->setISODate($year, $week, 1)->format('Y-m-d');
        $lastDay  = (new \DateTime())->setISODate($year, $week, 7)->format('Y-m-d');

        return $this->where('match_date >=', $firstDay)
                    ->where('match_date <=', $lastDay)
                    ->orderBy('match_date', 'ASC')
                    ->orderBy('match_time', 'ASC')
                    ->findAll();
    }

    public function getWithPlayers(int $id): ?object
    {
        $encounter = $this->find($id);
        if (!$encounter) {
            return null;
        }

        $encounter->players = \Config\Database::connect()
            ->table('schedule_encounter_players sep')
            ->select('sep.id, sep.member_id, sep.player_home_name, sep.opponent_name, m.last_name, m.first_name')
            ->join('members m', 'm.id = sep.member_id', 'left')
            ->where('sep.encounter_id', $id)
            ->get()->getResultObject();

        return $encounter;
    }
}
