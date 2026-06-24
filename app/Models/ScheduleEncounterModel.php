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
        'encounter_type', 'rounds_count', 'requires_arbitrage', 'requires_marquage', 'match_date', 'match_time',
        'is_home', 'venue', 'competition', 'notes',
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

    public function getNextActiveDay(): array
    {
        $today = date('Y-m-d');

        $row = $this->db->table('schedule_encounters')
            ->selectMin('match_date', 'next_date')
            ->where('match_date >=', $today)
            ->get()->getRowObject();

        if (!$row || !$row->next_date) return [];

        $encounters = $this->where('match_date', $row->next_date)
            ->orderBy('match_time', 'ASC')
            ->findAll();

        if (empty($encounters)) return [];

        $ids     = array_map(fn($e) => $e->id, $encounters);
        $players = $this->db->table('schedule_encounter_players sep')
            ->select('sep.encounter_id, sep.member_id, sep.player_home_name, sep.opponent_name, m.last_name, m.first_name')
            ->join('members m', 'm.id = sep.member_id', 'left')
            ->whereIn('sep.encounter_id', $ids)
            ->get()->getResultObject();

        $byEnc = [];
        foreach ($players as $p) {
            $byEnc[$p->encounter_id][] = $p;
        }
        foreach ($encounters as $enc) {
            $enc->players = $byEnc[$enc->id] ?? [];
        }

        return ['date' => $row->next_date, 'isToday' => $row->next_date === $today, 'encounters' => $encounters];
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
