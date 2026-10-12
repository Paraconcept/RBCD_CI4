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
        'code', 'competition', 'team_id', 'team_name', 'team_club', 'team_number', 'opponent_club', 'opponent_number',
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
            foreach ($this->db->table('members')->select('id, last_name, first_name, frbb_license')->whereIn('id', $ids)->get()->getResultObject() as $m) {
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

    /** « RBC Disonais 1 » */
    public static function homeLabel(object $e): string
    {
        return trim($e->team_club . ' ' . ($e->team_number ?? ''));
    }

    /** « B.C. Grâce-Hollogne 3 » */
    public static function awayLabel(object $e): string
    {
        return trim($e->opponent_club . ' ' . ($e->opponent_number ?? ''));
    }

    /** Saison sportive de la rencontre (juillet → juin), ex. « 2026 - 2027 ». */
    public static function season(object $e): string
    {
        $t     = strtotime($e->scheduled_at ?? 'now');
        $start = (int) date('n', $t) >= 7 ? (int) date('Y', $t) : (int) date('Y', $t) - 1;
        return $start . ' - ' . ($start + 1);
    }

    /** « Libre P.F. », « 3 Bandes G.F. » */
    public static function gameModeLabel(object $e): string
    {
        return (ScoreboardMatchModel::DISCIPLINES[$e->discipline] ?? $e->discipline) . ' ' . ($e->format === 'GB' ? 'G.F.' : 'P.F.');
    }

    /**
     * Côté (1 ou 2) de notre joueur dans un match : le joueur qui commence passe en 1,
     * on se repère donc aux clubs, puis au membre du club.
     */
    public static function homeSide(object $e, object $m): int
    {
        $norm = static fn (?string $s): string => preg_replace('/[^a-z0-9]/', '', strtolower(
            (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $s)));
        $away = $norm($e->opponent_club);
        $home = $norm($e->team_club);

        if ($away !== '' && str_contains($norm($m->p2_club), $away)) return 1;
        if ($away !== '' && str_contains($norm($m->p1_club), $away)) return 2;
        if ($home !== '' && str_contains($norm($m->p1_club), $home)) return 1;
        if ($home !== '' && str_contains($norm($m->p2_club), $home)) return 2;
        if (!$m->p1_member_id && $m->p2_member_id) return 2;
        return 1;
    }

    /**
     * Résultat de la rencontre selon les règles de la Coupe des Régions :
     * 2 points de match pour une victoire, 1 chacun pour un nul (les deux ont fini), 0 pour une défaite.
     * Équipe : somme des points et des reprises, moyenne = points / reprises, série = meilleure des joueurs.
     */
    public static function computeResult(object $e, array $matches): array
    {
        $teams = [];
        foreach (['home', 'away'] as $side) {
            $teams[$side] = ['rows' => [], 'points' => 0, 'innings' => 0, 'high_run' => 0, 'match_points' => 0];
        }

        foreach ($matches as $m) {
            $homeSide = self::homeSide($e, $m);
            $finished = $m->status === 'finished';
            foreach (['home' => $homeSide, 'away' => 3 - $homeSide] as $side => $n) {
                $p = 'p' . $n;
                $matchPoints = null;
                if ($finished) {
                    $winner      = (int) $m->winner;
                    $matchPoints = $winner === 0 ? 1 : ($winner === $n ? 2 : 0);
                }
                $row = [
                    'name'         => trim($m->{$p . '_last_name'} . ' ' . $m->{$p . '_first_name'}),
                    'license'      => $m->{$p . '_license'},
                    'distance'     => (int) $m->{$p . '_distance'},
                    'points'       => (int) $m->{$p . '_points'},
                    'innings'      => (int) $m->{$p . '_innings'},
                    'high_run'     => (int) $m->{$p . '_high_run'},
                    'match_points' => $matchPoints,
                    'played'       => $m->status !== 'scheduled',
                ];
                $teams[$side]['rows'][]        = $row;
                $teams[$side]['points']       += $row['points'];
                $teams[$side]['innings']      += $row['innings'];
                $teams[$side]['high_run']      = max($teams[$side]['high_run'], $row['high_run']);
                $teams[$side]['match_points'] += $matchPoints ?? 0;
            }
        }

        $finishedCount = count(array_filter($matches, static fn ($m) => $m->status === 'finished'));

        return [
            'teams'    => $teams,
            'finished' => $finishedCount,
            'total'    => count($matches),
            'complete' => $matches && $finishedCount === count($matches),
        ];
    }
}
