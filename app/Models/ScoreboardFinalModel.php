<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Finale ou poule de 3 à 7 joueurs : chacun rencontre chacun une fois, sur le marquoir comme un match officiel,
 * éventuellement suivi des matchs de classement 1er-2e, 3e-4e… (finale sans éliminatoires).
 * Classement FRBB : d'abord les joueurs dans (ou au-dessus de) la moyenne de leur catégorie, puis ceux sous la
 * moyenne ; dans chaque groupe, points de match (2 victoire, 1 nul, 0 défaite), puis moyenne proportionnelle,
 * puis meilleures moyennes particulières, puis meilleures séries.
 */
class ScoreboardFinalModel extends Model
{
    public const MIN_PLAYERS = 3;
    public const MAX_PLAYERS = 7;

    /** Type de finale : choisit le modèle de la feuille de classement PDF. */
    public const KINDS = [
        'poule'        => 'Finale du club ou poule',
        'championship' => 'Finale de championnat régional (FRBB)',
        'cup'          => 'Finale de coupe',
    ];

    protected $table      = 'scoreboard_finals';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'name', 'kind', 'regional_contact', 'scheduled_at', 'day2_at', 'day2_from', 'discipline', 'format', 'innings_limit', 'unequal_innings',
        'shot_clock_enabled', 'shot_clock_seconds', 'shot_clock_extensions', 'table_ids',
        'placement_matches', 'results_ordered_at', 'comment', 'created_by',
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
        if ($n === 4) {
            // Calendrier fédéral : 1-2 et 3-4 d'abord ; sync() replace ensuite 3 à 6 selon leurs résultats
            return [[0, 1], [2, 3], [0, 2], [1, 3], [0, 3], [1, 2]];
        }
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

    /** Matchs de classement d'une finale à $n joueurs : [[1, 2], [3, 4]…], le dernier joueur d'un nombre impair n'en joue pas. */
    public static function placementPairs(int $n): array
    {
        $pairs = [];
        for ($r = 1; $r + 1 <= $n; $r += 2) {
            $pairs[] = [$r, $r + 1];
        }
        return $pairs;
    }

    public static function matchCount(int $n, bool $placement): int
    {
        return intdiv($n * ($n - 1), 2) + ($placement ? count(self::placementPairs($n)) : 0);
    }

    public static function rankLabel(int $rank): string
    {
        return 'Classé ' . $rank . ($rank === 1 ? 'er' : 'e');
    }

    /** Date et heure prévues pour le match à la position $order (jour 1 ou jour 2). */
    public static function dayAt(object $f, int $order): ?string
    {
        return !empty($f->day2_at) && (int) $f->day2_from > 0 && $order >= (int) $f->day2_from ? $f->day2_at : $f->scheduled_at;
    }

    /**
     * Nouvel ordre fixé par le DS. Les matchs de classement restent toujours après la poule. Les billards des
     * matchs pas encore commencés suivent la nouvelle position (rotation sur les billards cochés), les autres
     * gardent le leur ; un match qui change de jour prend l'heure de ce jour.
     */
    public function reorderMatches(object $f, array $matchIds): bool
    {
        $byId = [];
        foreach ($this->getMatches((int) $f->id) as $m) {
            $byId[(int) $m->id] = $m;
        }
        $sorted = $matchIds;
        sort($sorted);
        $current = array_keys($byId);
        sort($current);
        if ($sorted !== $current) {
            return false;
        }
        $pool      = array_values(array_filter($matchIds, static fn ($id) => empty($byId[$id]->final_placement)));
        $placement = array_values(array_filter($matchIds, static fn ($id) => !empty($byId[$id]->final_placement)));

        $tables = self::tableIds($f);
        $this->db->transStart();
        foreach (array_merge($pool, $placement) as $i => $matchId) {
            $m    = $byId[$matchId];
            $data = ['final_order' => $i + 1];
            if ($m->status === 'scheduled') {
                if ($tables) {
                    $data['table_id'] = $tables[$i % count($tables)];
                }
                if (($at = self::movedDay($f, $m, $i + 1)) !== false) {
                    $data['scheduled_at'] = $at;
                }
            }
            $this->db->table('scoreboard_matches')->where('id', $matchId)->update($data);
        }
        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Dates des matchs à jouer après un changement de date de la finale. Une heure réglée à la main sur un
     * match (19h30, 21h00…) est conservée tant que son jour ne change pas.
     */
    public function applyDays(object $f, object $old): void
    {
        foreach ($this->getMatches((int) $f->id) as $m) {
            if ($m->status !== 'scheduled') {
                continue;
            }
            $order = (int) $m->final_order;
            $at    = $m->scheduled_at === self::dayAt($old, $order) ? self::dayAt($f, $order) : self::movedDay($f, $m, $order);
            if ($at !== false && $at !== $m->scheduled_at) {
                $this->db->table('scoreboard_matches')->where('id', $m->id)->update(['scheduled_at' => $at]);
            }
        }
    }

    /** Nouvelle date si le match à la position $order n'est pas prévu le bon jour, sinon false. */
    private static function movedDay(object $f, object $m, int $order): string|false|null
    {
        $at = self::dayAt($f, $order);
        $day = static fn (?string $dt) => $dt ? substr($dt, 0, 10) : null;
        return $day($at) === $day($m->scheduled_at) ? false : $at;
    }

    /**
     * (Re)crée les matchs de la finale. Seulement tant qu'aucun match n'a commencé.
     */
    public function generateMatches(object $f, ?int $createdBy = null): int
    {
        $players = (new ScoreboardFinalPlayerModel())->getForFinal((int) $f->id);
        $tables  = self::tableIds($f);
        $matches = new ScoreboardMatchModel();
        $order   = 0;
        $base    = static function (int $order) use ($f, $tables, $createdBy): array {
            return [
                'type' => 'official', 'status' => 'scheduled', 'final_id' => $f->id, 'final_order' => $order,
                'table_id' => $tables ? $tables[($order - 1) % count($tables)] : null,
                'scheduled_at' => self::dayAt($f, $order), 'competition' => $f->name,
                'discipline' => $f->discipline, 'format' => $f->format,
                'innings_limit' => $f->innings_limit, 'unequal_innings' => $f->unequal_innings,
                'shot_clock_enabled' => $f->shot_clock_enabled, 'shot_clock_seconds' => $f->shot_clock_seconds,
                'shot_clock_extensions' => $f->shot_clock_extensions, 'created_by' => $createdBy,
            ];
        };

        $this->db->transStart();
        $this->db->table('scoreboard_matches')->where('final_id', $f->id)->delete();
        foreach (self::schedule(count($players), count($tables)) as [$a, $b]) {
            $matches->insert($base(++$order) + self::playerData('p1', $players[$a]) + self::playerData('p2', $players[$b]));
        }
        if ($f->placement_matches) {
            foreach (self::placementPairs(count($players)) as [$r1, $r2]) {
                $matches->insert($base(++$order) + ['final_placement' => $r1 . '-' . $r2]
                                 + self::playerData('p1', null, $r1) + self::playerData('p2', null, $r2));
            }
        }
        $this->db->table('scoreboard_finals')->where('id', $f->id)->update(['results_ordered_at' => null]);
        $this->db->transComplete();

        return $order;
    }

    /** Colonnes d'un côté du match : le joueur de la finale, ou « Classé 1er » en attente du classement de la poule. */
    private static function playerData(string $p, ?object $pl, int $rank = 0): array
    {
        $data = [$p . '_final_player_id' => $pl->id ?? null];
        foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'category_id', 'category', 'average_min', 'distance'] as $field) {
            $data[$p . '_' . $field] = $pl->$field ?? null;
        }
        if (!$pl) {
            $data[$p . '_last_name']  = self::rankLabel($rank);
            $data[$p . '_first_name'] = '';
            $data[$p . '_distance']   = 0;
        }
        return $data;
    }

    /**
     * À la fin (ou à la correction) d'un match : place les matchs 3 à 6 du calendrier fédéral à 4 joueurs,
     * puis désigne les joueurs des matchs de classement quand toute la poule est jouée.
     */
    public function sync(int $id): void
    {
        $f = $this->find($id);
        if (!$f) {
            return;
        }
        if (!$f->results_ordered_at) {
            $this->orderByResults($f, $this->getMatches($id));
        }
        $this->fillPlacements($this->getMatches($id), (new ScoreboardFinalPlayerModel())->getForFinal($id));
    }

    /**
     * Calendrier fédéral à 4 joueurs : 3. Gagnant 1 – Perdant 2, 4. Gagnant 2 – Perdant 1, 5. Gagnant 1 – Gagnant 2,
     * 6. Perdant 1 – Perdant 2. Fait une seule fois : un nul ou un match déjà lancé laisse l'ordre au DS (glisser-déposer).
     */
    private function orderByResults(object $f, array $matches): void
    {
        $pool = array_values(array_filter($matches, static fn ($m) => empty($m->final_placement)));
        if (count($pool) !== 6 || $pool[0]->status !== 'finished' || $pool[1]->status !== 'finished') {
            return;
        }
        $done = fn () => $this->db->table('scoreboard_finals')->where('id', $f->id)->update(['results_ordered_at' => ScoreboardMatchModel::now()]);

        $sides = static function (object $m): ?array {
            $w = (int) $m->winner;
            return in_array($w, [1, 2], true) ? [(int) $m->{'p' . $w . '_final_player_id'}, (int) $m->{'p' . (3 - $w) . '_final_player_id'}] : null;
        };
        $r1 = $sides($pool[0]);
        $r2 = $sides($pool[1]);
        $rest = array_slice($pool, 2);
        if (!$r1 || !$r2 || array_intersect($r1, $r2) || array_filter($rest, static fn ($m) => $m->status !== 'scheduled')) {
            $done();
            return;
        }
        [$w1, $l1] = $r1;
        [$w2, $l2] = $r2;

        $ids = [(int) $pool[0]->id, (int) $pool[1]->id];
        foreach ([[$w1, $l2], [$w2, $l1], [$w1, $w2], [$l1, $l2]] as $pair) {
            foreach ($rest as $m) {
                $pm = [(int) $m->p1_final_player_id, (int) $m->p2_final_player_id];
                if (!array_diff($pair, $pm)) {
                    $ids[] = (int) $m->id;
                    continue 2;
                }
            }
            $done();
            return;
        }
        foreach ($matches as $m) {
            if (!empty($m->final_placement)) {
                $ids[] = (int) $m->id;
            }
        }
        $this->reorderMatches($f, $ids);
        $done();
    }

    private function fillPlacements(array $matches, array $players): void
    {
        $pool      = array_filter($matches, static fn ($m) => empty($m->final_placement));
        $placement = array_filter($matches, static fn ($m) => !empty($m->final_placement) && $m->status === 'scheduled');
        if (!$placement) {
            return;
        }
        $complete = !array_filter($pool, static fn ($m) => $m->status !== 'finished');
        $ranked   = [];
        if ($complete) {
            foreach (self::standings($players, $pool) as $r) {
                $ranked[$r['rank']] = $r['player'];
            }
        }

        $model = new ScoreboardMatchModel();
        foreach ($placement as $m) {
            [$a, $b] = array_map('intval', explode('-', $m->final_placement));
            $pa = $ranked[$a] ?? null;
            $pb = $ranked[$b] ?? null;
            if ((int) ($pa->id ?? 0) === (int) $m->p1_final_player_id && (int) ($pb->id ?? 0) === (int) $m->p2_final_player_id) {
                continue;
            }
            // Poule pas (ou plus, après une correction) terminée : le match redevient « Classé 1er – Classé 2e »
            $model->update($m->id, $pa && $pb
                ? self::playerData('p1', $pa) + self::playerData('p2', $pb)
                : self::playerData('p1', null, $a) + self::playerData('p2', null, $b));
        }
    }

    /**
     * Classement des joueurs d'après les matchs terminés.
     *
     * @return list<array> lignes triées, avec 'rank' et 'in_average' (M : dans ou au-dessus de la moyenne, sinon S/M)
     */
    public static function standings(array $players, array $matches): array
    {
        $rows = [];
        foreach ($players as $p) {
            $rows[(int) $p->id] = ['player' => $p, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                                   'match_points' => 0, 'points' => 0, 'innings' => 0, 'high_run' => 0, 'average' => 0.0, 'proportional' => null,
                                   'in_average' => false, 'match_averages' => [], 'high_runs' => []];
        }

        foreach ($matches as $m) {
            if ($m->status !== 'finished') {
                continue;
            }
            $winner = (int) $m->winner;
            foreach ([1, 2] as $n) {
                $id = (int) $m->{'p' . $n . '_final_player_id'};
                if (!isset($rows[$id])) {
                    continue;
                }
                $mp      = $winner === 0 ? 1 : ($winner === $n ? 2 : 0);
                $points  = (int) $m->{'p' . $n . '_points'};
                $innings = (int) $m->{'p' . $n . '_innings'};
                $r = &$rows[$id];
                $r['played']++;
                $r[$mp === 2 ? 'won' : ($mp === 1 ? 'drawn' : 'lost')]++;
                $r['match_points'] += $mp;
                $r['points']       += $points;
                $r['innings']      += $innings;
                $r['high_run']      = max($r['high_run'], (int) $m->{'p' . $n . '_high_run'});
                $r['match_averages'][] = $innings > 0 ? round($points / $innings, 3) : 0.0;
                $r['high_runs'][]      = (int) $m->{'p' . $n . '_high_run'};
                unset($r);
            }
        }

        foreach ($rows as &$r) {
            $r['average'] = $r['innings'] > 0 ? $r['points'] / $r['innings'] : 0.0;
            $min = (float) ($r['player']->average_min ?? 0);
            $r['proportional'] = $min > 0 && $r['innings'] > 0 ? $r['average'] / $min : null;
            // Sans catégorie (pas de moyenne minimum), le joueur est compté dans la moyenne
            $r['in_average'] = $min <= 0 || ($r['played'] > 0 && round($r['average'], 3) >= round($min, 3));
            rsort($r['match_averages']);
            rsort($r['high_runs']);
        }
        unset($r);

        // Comparaisons à 3 décimales, comme sur les feuilles imprimées
        $rows = array_values($rows);
        usort($rows, static function ($a, $b) {
            $cmp = [(int) $b['in_average'], $b['match_points'], round($b['proportional'] ?? -1, 3)]
               <=> [(int) $a['in_average'], $a['match_points'], round($a['proportional'] ?? -1, 3)];
            foreach (['match_averages', 'high_runs'] as $list) {
                for ($i = 0, $n = max(count($a[$list]), count($b[$list])); $cmp === 0 && $i < $n; $i++) {
                    $cmp = ($b[$list][$i] ?? -1) <=> ($a[$list][$i] ?? -1);
                }
            }
            return $cmp;
        });

        foreach ($rows as $i => &$r) {
            $r['rank'] = $i + 1;
        }
        unset($r);

        return $rows;
    }

    /**
     * Mention de fin de finale d'après la moyenne générale : SM (sous la moyenne), M (dans la moyenne),
     * RECLASSE (au-dessus de la sous-catégorie), PROMU (au-dessus de la catégorie), DP (double promotion).
     * $levels : FrbbCategoryModel::levels() de la catégorie du joueur.
     */
    public static function rating(array $row, ?array $levels): ?string
    {
        if (!$row['played']) {
            return null;
        }
        if (!$row['in_average']) {
            return 'SM';
        }
        $avg = round($row['average'], 3);
        return match (true) {
            $levels && $levels['dp'] !== null && $avg > $levels['dp']               => 'DP',
            $levels && $levels['group_max'] !== null && $avg > $levels['group_max'] => 'PROMU',
            $levels && $levels['max'] !== null && $avg > $levels['max']             => 'RECLASSE',
            default                                                                 => 'M',
        };
    }

    /** Libellé imprimé : bilingue (Gemiddeld / Onder Gemiddeld) sur la feuille FRBB, court sur celle de la coupe. */
    public static function ratingLabel(?string $rating, string $kind): string
    {
        return match ($rating) {
            null       => '',
            'SM'       => $kind === 'championship' ? 'SM/OG' : 'S/M',
            'M'        => $kind === 'championship' ? 'M/G' : 'M',
            'RECLASSE' => 'RECLASSÉ',
            default    => $rating,
        };
    }

    /**
     * Matchs de chaque joueur dans l'ordre de jeu, pour les feuilles de classement :
     * [id joueur => list<array{match: object, side: int, opponent: ?object}>]. Les matchs de classement
     * encore sans joueurs n'y figurent pas.
     */
    public static function playerLines(array $players, array $matches): array
    {
        $byId  = [];
        $lines = [];
        foreach ($players as $p) {
            $byId[(int) $p->id] = $p;
            $lines[(int) $p->id] = [];
        }
        foreach ($matches as $m) {
            foreach ([1, 2] as $n) {
                $id = (int) $m->{'p' . $n . '_final_player_id'};
                if (isset($lines[$id])) {
                    $lines[$id][] = ['match' => $m, 'side' => $n, 'opponent' => $byId[(int) $m->{'p' . (3 - $n) . '_final_player_id'}] ?? null];
                }
            }
        }
        return $lines;
    }

    /** Saison sportive d'une date : de juillet à juin, ex. « 2026-2027 ». */
    public static function season(?string $date): string
    {
        $t = strtotime($date ?? 'now');
        $y = (int) date('Y', $t) - ((int) date('n', $t) < 7 ? 1 : 0);
        return $y . '-' . ($y + 1);
    }

    /** Résultats croisés de la poule : [id joueur][id adversaire] = match terminé (ou en cours). */
    public static function crossTable(array $matches): array
    {
        $grid = [];
        foreach ($matches as $m) {
            if (!empty($m->final_placement)) {
                continue;
            }
            $a = (int) $m->p1_final_player_id;
            $b = (int) $m->p2_final_player_id;
            $grid[$a][$b] = ['match' => $m, 'side' => 1];
            $grid[$b][$a] = ['match' => $m, 'side' => 2];
        }
        return $grid;
    }
}
