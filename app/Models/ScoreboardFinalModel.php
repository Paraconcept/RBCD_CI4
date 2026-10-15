<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Finale ou poule de 3 à 7 joueurs : chacun rencontre chacun une fois, sur le marquoir comme un match officiel.
 * Classement : points de match (2 victoire, 1 nul, 0 défaite), puis moyenne proportionnelle,
 * puis meilleure série, puis résultat du match entre les ex æquo.
 */
class ScoreboardFinalModel extends Model
{
    public const MIN_PLAYERS = 3;
    public const MAX_PLAYERS = 7;

    protected $table      = 'scoreboard_finals';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'name', 'scheduled_at', 'discipline', 'format', 'innings_limit', 'unequal_innings',
        'shot_clock_enabled', 'shot_clock_seconds', 'shot_clock_extensions', 'table_ids', 'comment', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Finales avec l'avancement de leurs matchs. */
    public function withProgress()
    {
        return $this->select("scoreboard_finals.*,
                (SELECT COUNT(*) FROM scoreboard_final_players fp WHERE fp.final_id = scoreboard_finals.id) AS players_count,
                COUNT(m.id) AS matches_total,
                SUM(m.status = 'finished') AS matches_finished,
                SUM(m.status = 'live') AS matches_live")
            ->join('scoreboard_matches m', "m.final_id = scoreboard_finals.id AND m.status <> 'cancelled'", 'left')
            ->groupBy('scoreboard_finals.id');
    }

    public function getMatches(int $id): array
    {
        return (new ScoreboardMatchModel())->withTable()
            ->where('final_id', $id)
            ->where('status <>', 'cancelled')
            ->orderBy('final_order', 'ASC')
            ->findAll();
    }

    /** Vrai dès qu'un match a commencé : la liste des joueurs ne peut plus changer. */
    public function hasStarted(int $id): bool
    {
        return $this->db->table('scoreboard_matches')->where('final_id', $id)->whereIn('status', ['live', 'finished'])->countAllResults() > 0;
    }

    public static function tableIds(object $f): array
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) $f->table_ids))));
    }

    /**
     * Toutes les rencontres « chacun contre chacun » (méthode du cercle), puis réordonnées pour qu'un joueur
     * ne joue pas deux matchs d'affilée, ni deux matchs en même temps quand plusieurs billards sont utilisés.
     *
     * @return list<array{0:int, 1:int}> paires d'index de joueurs
     */
    public static function schedule(int $n, int $tables = 1): array
    {
        $ids = range(0, $n - 1);
        if ($n % 2) {
            $ids[] = null;
        }
        $m = count($ids);
        $pairs = [];
        for ($r = 0; $r < $m - 1; $r++) {
            for ($i = 0; $i < $m / 2; $i++) {
                if ($ids[$i] !== null && $ids[$m - 1 - $i] !== null) {
                    $pairs[] = [$ids[$i], $ids[$m - 1 - $i]];
                }
            }
            $last = array_pop($ids);
            array_splice($ids, 1, 0, [$last]);
        }

        // Fenêtre la plus large possible (matchs joués en parallèle), puis le seul match précédent
        for ($window = max(1, $tables); $window >= 1; $window--) {
            $budget = 200000;
            $order  = self::orderWithout($pairs, [], $window, $budget);
            if ($order !== null) {
                return $order;
            }
        }
        // À 3 joueurs, un joueur rejoue forcément tout de suite : on garde l'ordre du cercle
        $order = $pairs;
        return $order;
    }

    /** Recherche en profondeur d'un ordre où chaque match est disjoint des $window précédents. */
    private static function orderWithout(array $left, array $done, int $window, int &$budget): ?array
    {
        if (!$left) {
            return $done;
        }
        $busy = $done ? array_merge(...array_slice($done, -$window)) : [];
        foreach ($left as $k => $p) {
            if (--$budget < 0) {
                return null;
            }
            if (in_array($p[0], $busy, true) || in_array($p[1], $busy, true)) {
                continue;
            }
            $rest = $left;
            unset($rest[$k]);
            $found = self::orderWithout($rest, array_merge($done, [$p]), $window, $budget);
            if ($found !== null) {
                return $found;
            }
        }
        return null;
    }

    /**
     * Nouvel ordre fixé par le DS. Les billards des matchs pas encore commencés suivent la
     * nouvelle position (rotation sur les billards cochés), les autres gardent le leur.
     */
    public function reorderMatches(object $f, array $matchIds): bool
    {
        $current = array_map(static fn ($m) => (int) $m->id, $this->getMatches((int) $f->id));
        $sorted  = $matchIds;
        sort($sorted);
        sort($current);
        if ($sorted !== $current) {
            return false;
        }

        $tables = self::tableIds($f);
        $this->db->transStart();
        foreach ($matchIds as $i => $matchId) {
            $this->db->table('scoreboard_matches')->where('id', $matchId)->update(['final_order' => $i + 1]);
            if ($tables) {
                $this->db->table('scoreboard_matches')->where('id', $matchId)->where('status', 'scheduled')
                         ->update(['table_id' => $tables[$i % count($tables)]]);
            }
        }
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * (Re)crée les matchs de la finale. Seulement tant qu'aucun match n'a commencé.
     */
    public function generateMatches(object $f, ?int $createdBy = null): int
    {
        $players = (new ScoreboardFinalPlayerModel())->getForFinal((int) $f->id);
        $tables  = self::tableIds($f);
        $matches = new ScoreboardMatchModel();

        $this->db->transStart();
        $this->db->table('scoreboard_matches')->where('final_id', $f->id)->delete();
        foreach (self::schedule(count($players), count($tables)) as $i => [$a, $b]) {
            $data = [
                'type' => 'official', 'status' => 'scheduled', 'final_id' => $f->id, 'final_order' => $i + 1,
                'table_id' => $tables ? $tables[$i % count($tables)] : null,
                'scheduled_at' => $f->scheduled_at, 'competition' => $f->name,
                'discipline' => $f->discipline, 'format' => $f->format,
                'innings_limit' => $f->innings_limit, 'unequal_innings' => $f->unequal_innings,
                'shot_clock_enabled' => $f->shot_clock_enabled, 'shot_clock_seconds' => $f->shot_clock_seconds,
                'shot_clock_extensions' => $f->shot_clock_extensions, 'created_by' => $createdBy,
            ];
            foreach (['p1' => $players[$a], 'p2' => $players[$b]] as $p => $pl) {
                $data[$p . '_final_player_id'] = $pl->id;
                foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'category_id', 'category', 'average_min', 'distance'] as $field) {
                    $data[$p . '_' . $field] = $pl->$field;
                }
            }
            $matches->insert($data);
        }
        $this->db->transComplete();

        return count($players) * (count($players) - 1) / 2;
    }

    /**
     * Classement des joueurs d'après les matchs terminés.
     *
     * @return list<array> lignes triées, avec 'rank'
     */
    public static function standings(array $players, array $matches): array
    {
        $rows = [];
        foreach ($players as $p) {
            $rows[(int) $p->id] = ['player' => $p, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                                   'match_points' => 0, 'points' => 0, 'innings' => 0, 'high_run' => 0, 'average' => 0.0, 'proportional' => null];
        }
        $h2h = [];

        foreach ($matches as $m) {
            if ($m->status !== 'finished') {
                continue;
            }
            $winner = (int) $m->winner;
            foreach ([1 => 2, 2 => 1] as $n => $o) {
                $id  = (int) $m->{'p' . $n . '_final_player_id'};
                $opp = (int) $m->{'p' . $o . '_final_player_id'};
                if (!isset($rows[$id])) {
                    continue;
                }
                $mp = $winner === 0 ? 1 : ($winner === $n ? 2 : 0);
                $r  = &$rows[$id];
                $r['played']++;
                $r[$mp === 2 ? 'won' : ($mp === 1 ? 'drawn' : 'lost')]++;
                $r['match_points'] += $mp;
                $r['points']       += (int) $m->{'p' . $n . '_points'};
                $r['innings']      += (int) $m->{'p' . $n . '_innings'};
                $r['high_run']      = max($r['high_run'], (int) $m->{'p' . $n . '_high_run'});
                unset($r);
                $h2h[$id][$opp] = $mp;
            }
        }

        foreach ($rows as &$r) {
            $r['average'] = $r['innings'] > 0 ? $r['points'] / $r['innings'] : 0.0;
            $min = (float) ($r['player']->average_min ?? 0);
            $r['proportional'] = $min > 0 && $r['innings'] > 0 ? $r['average'] / $min : null;
        }
        unset($r);

        $rows = array_values($rows);
        usort($rows, static function ($a, $b) use ($h2h) {
            $ia = (int) $a['player']->id;
            $ib = (int) $b['player']->id;
            return [$b['match_points'], round($b['proportional'] ?? -1, 9), $b['high_run'], $h2h[$ib][$ia] ?? 0]
               <=> [$a['match_points'], round($a['proportional'] ?? -1, 9), $a['high_run'], $h2h[$ia][$ib] ?? 0];
        });

        foreach ($rows as $i => &$r) {
            $r['rank'] = $i + 1;
        }
        unset($r);

        return $rows;
    }

    /** Résultats croisés : [id joueur][id adversaire] = match terminé (ou en cours). */
    public static function crossTable(array $matches): array
    {
        $grid = [];
        foreach ($matches as $m) {
            $a = (int) $m->p1_final_player_id;
            $b = (int) $m->p2_final_player_id;
            $grid[$a][$b] = ['match' => $m, 'side' => 1];
            $grid[$b][$a] = ['match' => $m, 'side' => 2];
        }
        return $grid;
    }
}
