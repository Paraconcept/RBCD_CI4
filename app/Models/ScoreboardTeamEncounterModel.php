<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardTeamEncounterModel extends Model
{
    public const COMPETITIONS = [
        'CDR' => 'Coupe des Régions',
    ];

    // Mode de jeu des équipes CDR → discipline et format du marquoir
    private const CDR_GAME_MODES = [
        'Libre PF'    => ['libre', 'PB'],
        'Libre GF'    => ['libre', 'GB'],
        '3 Bandes PF' => ['3bandes', 'PB'],
        '3 Bandes GF' => ['3bandes', 'GB'],
    ];

    protected $table      = 'scoreboard_team_encounters';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'code', 'competition', 'team_id', 'team_name', 'opponent_club',
        'scheduled_at', 'discipline', 'format', 'notes', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Fiches avec l'avancement de leurs matchs (total, terminés, en cours).
     */
    public function withProgress()
    {
        return $this->select("scoreboard_team_encounters.*,
                COUNT(m.id) AS matches_total,
                SUM(m.status = 'finished') AS matches_finished,
                SUM(m.status = 'live') AS matches_live")
            ->join('scoreboard_matches m', "m.team_encounter_id = scoreboard_team_encounters.id AND m.status <> 'cancelled'", 'left')
            ->groupBy('scoreboard_team_encounters.id');
    }

    public function getMatches(int $id): array
    {
        return (new ScoreboardMatchModel())->withTable()
            ->where('team_encounter_id', $id)
            ->where('status <>', 'cancelled')
            ->orderBy('scheduled_at', 'ASC')
            ->orderBy('scoreboard_matches.id', 'ASC')
            ->findAll();
    }

    /**
     * Équipes CDR proposées à l'encodage, avec leur composition (membres).
     */
    public function getCdrTeams(): array
    {
        $teams = $this->db->table('cdr_teams')->orderBy('season', 'DESC')->orderBy('name', 'ASC')->get()->getResultObject();
        $ids = [];
        foreach ($teams as $t) {
            array_push($ids, $t->player1_id, $t->player2_id, $t->player3_id);
        }
        $ids = array_filter(array_unique($ids));
        $members = [];
        if ($ids) {
            foreach ($this->db->table('members')->select('id, last_name, first_name')->whereIn('id', $ids)->get()->getResultObject() as $m) {
                $members[(int) $m->id] = $m;
            }
        }

        foreach ($teams as $t) {
            [$t->discipline, $t->format] = self::CDR_GAME_MODES[$t->game_mode] ?? ['libre', 'PB'];
            $t->roster = [];
            foreach ([$t->player1_id, $t->player2_id, $t->player3_id] as $pid) {
                if ($pid && isset($members[(int) $pid])) {
                    $t->roster[] = $members[(int) $pid];
                }
            }
        }
        return $teams;
    }

    public function getRoster(?int $teamId): array
    {
        foreach ($teamId ? $this->getCdrTeams() : [] as $t) {
            if ((int) $t->id === $teamId) {
                return $t->roster;
            }
        }
        return [];
    }

    public static function label(object $e): string
    {
        return $e->competition . ' · ' . $e->code;
    }
}
