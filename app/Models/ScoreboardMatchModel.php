<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardMatchModel extends Model
{
    public const DISCIPLINES = [
        'libre'   => 'Libre',
        'cadre'   => 'Cadre',
        'bande'   => 'Bande',
        '3bandes' => '3 Bandes',
    ];

    public const TIMEZONE = 'Europe/Brussels';

    public const DEFAULT_SET_POINTS  = 15;
    public const DEFAULT_SETS_TO_WIN = 2;

    public const FORMATS = [
        'PB' => 'Petit billard',
        'GB' => 'Grand billard',
    ];

    public const STATUSES = [
        'scheduled' => 'Prévue',
        'live'      => 'En cours',
        'finished'  => 'Terminée',
        'cancelled' => 'Annulée',
    ];

    protected $table      = 'scoreboard_matches';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'type', 'team_encounter_id', 'final_id', 'final_order', 'final_placement', 'status', 'table_id', 'scheduled_at', 'competition', 'match_number',
        'discipline', 'format', 'innings_limit', 'unequal_innings', 'sets_to_win',
        'shot_clock_enabled', 'shot_clock_seconds', 'shot_clock_extensions', 'shot_clock_auto_start', 'signature_required',
        'p1_member_id', 'p1_final_player_id', 'p1_last_name', 'p1_first_name', 'p1_club', 'p1_license',
        'p1_category_id', 'p1_category', 'p1_average_min', 'p1_distance',
        'p1_points', 'p1_innings', 'p1_high_run', 'p1_sets', 'p1_extensions_used',
        'p2_member_id', 'p2_final_player_id', 'p2_last_name', 'p2_first_name', 'p2_club', 'p2_license',
        'p2_category_id', 'p2_category', 'p2_average_min', 'p2_distance',
        'p2_points', 'p2_innings', 'p2_high_run', 'p2_sets', 'p2_extensions_used',
        'p3_member_id', 'p3_last_name', 'p3_first_name', 'p3_club', 'p3_license',
        'p3_category_id', 'p3_category', 'p3_average_min', 'p3_distance',
        'p3_points', 'p3_innings', 'p3_high_run',
        'starting_player', 'current_run', 'paused', 'winner', 'absent', 'comment', 'rev',
        'started_at', 'finished_at', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function withTable()
    {
        // Sous-requête : seules des colonnes au nom unique sont jointes, pour que les filtres et tris
        // non qualifiés (scheduled_at, discipline…) restent sans ambiguïté
        return $this->select('scoreboard_matches.*, t.name AS table_name, te.te_code, te.te_competition')
                    ->join('scoreboard_tables t', 't.id = scoreboard_matches.table_id', 'left')
                    ->join('(SELECT id AS te_id, code AS te_code, competition AS te_competition FROM scoreboard_team_encounters) te',
                           'te.te_id = scoreboard_matches.team_encounter_id', 'left', false);
    }

    /**
     * Rencontres que l'on peut démarrer sur un billard donné (assignées à ce billard ou sans billard).
     */
    public function getStartableForTable(int $tableId): array
    {
        return $this->withTable()
            ->where('status', 'scheduled')
            ->groupStart()->where('table_id', $tableId)->orWhere('table_id', null)->groupEnd()
            ->groupStart()->where('scheduled_at', null)->orWhere('scheduled_at <=', self::now('Y-m-d 23:59:59'))->groupEnd()
            ->orderBy('scheduled_at IS NULL', 'ASC', false)
            ->orderBy('scheduled_at', 'ASC')
            ->orderBy('final_order', 'ASC')
            ->orderBy('scoreboard_matches.id', 'ASC')
            ->findAll();
    }

    /**
     * Rencontres prévues après aujourd'hui sur ce billard (ou sans billard attribué).
     */
    public function getUpcomingForTable(int $tableId, int $limit = 10): array
    {
        return $this->withTable()
            ->where('status', 'scheduled')
            ->groupStart()->where('table_id', $tableId)->orWhere('table_id', null)->groupEnd()
            ->where('scheduled_at >', self::now('Y-m-d 23:59:59'))
            ->orderBy('scheduled_at', 'ASC')
            ->findAll($limit);
    }

    /**
     * État d'une partie tel que l'écran du marquoir le manipule (séries dans l'ordre chronologique).
     */
    public function stateFor(object $match): array
    {
        $starter = (int) ($match->starting_player ?: 1);
        $bySet   = [];
        foreach ((new ScoreboardInningModel())->getForMatch((int) $match->id) as $r) {
            $bySet[(int) $r->set_no][(int) $r->player][(int) $r->inning_no] = (int) $r->points;
        }
        ksort($bySet);

        $count   = self::playerCount($match);
        $innings = [];
        foreach ($bySet as $setNo => $byPlayer) {
            $first = self::setStarter($starter, $setNo);
            $order = [];
            for ($k = 0; $k < $count; $k++) {
                $order[] = ($first - 1 + $k) % $count + 1;
            }
            $rounds = max(array_map(static fn ($p) => count($byPlayer[$p] ?? []), $order));
            for ($n = 1; $n <= $rounds; $n++) {
                foreach ($order as $p) {
                    if (isset($byPlayer[$p][$n])) $innings[] = $byPlayer[$p][$n];
                }
            }
        }

        return [
            'rev'        => (int) $match->rev,
            'status'     => $match->status,
            'innings'    => $innings,
            'current_run' => (int) $match->current_run,
            'paused'     => (bool) ($match->paused ?? false),
            'extensions' => [(int) $match->p1_extensions_used, (int) $match->p2_extensions_used],
        ];
    }

    /**
     * Rencontres officielles du jour pour les spectateurs (page publique « En direct »), tous billards confondus.
     * Les parties amicales (entraînements entre membres) ne sont jamais publiées.
     */
    public function getTodayForSpectators(): array
    {
        $today = self::now('Y-m-d');
        return $this->withTable()
            ->where('type', 'official')
            ->whereIn('status', ['scheduled', 'live', 'finished'])
            ->groupStart()
                ->where("DATE(scheduled_at) = '$today'", null, false)
                ->orWhere("DATE(started_at) = '$today'", null, false)
                ->orWhere("DATE(finished_at) = '$today'", null, false)
            ->groupEnd()
            ->orderBy("FIELD(status, 'live', 'scheduled', 'finished')", '', false)
            ->orderBy('scheduled_at', 'ASC')
            ->orderBy('started_at', 'ASC')
            ->findAll();
    }

    /**
     * « Dernières parties » d'un billard : amicales du jour même, officielles de la semaine en cours (depuis lundi).
     */
    public function getRecentForTable(int $tableId, int $limit = 10): array
    {
        $now      = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $friendly = $now->format('Y-m-d 00:00:00');
        $monday   = (clone $now)->modify('monday this week')->format('Y-m-d 00:00:00');

        return $this->where('table_id', $tableId)
            ->where('status', 'finished')
            ->groupStart()
                ->groupStart()->where('type', 'friendly')->where('finished_at >=', $friendly)->groupEnd()
                ->orGroupStart()->where('type', 'official')->where('finished_at >=', $monday)->groupEnd()
            ->groupEnd()
            ->orderBy('finished_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * Les amicales terminées ou annulées n'ont plus d'intérêt le lendemain : effacées (avec leurs reprises) à chaque
     * passage sur le marquoir ou la liste admin, faute de tâche planifiée.
     */
    public function purgeOldFriendlies(): void
    {
        $limit = self::now('Y-m-d 00:00:00');
        $this->db->table('scoreboard_matches')
            ->where('type', 'friendly')
            ->whereIn('status', ['finished', 'cancelled'])
            ->where('COALESCE(finished_at, updated_at, created_at) <', $limit)
            ->delete();
    }

    /**
     * Ce que « Nettoyer les rencontres » efface : toutes les parties terminées ou annulées, sauf celles d'une rencontre d'équipe
     * ou d'une finale qui a encore des matchs à jouer ou en cours ; les rencontres d'équipe et finales entièrement
     * terminées partent avec leurs matchs.
     *
     * @return array{matches: list<int>, official: int, friendly: int, encounters: list<int>, finals: list<int>}
     */
    public function purgeable(): array
    {
        $open = static fn (string $col): string => "SELECT $col FROM scoreboard_matches WHERE status IN ('scheduled', 'live') AND $col IS NOT NULL";
        $rows = $this->db->table('scoreboard_matches')
            ->select('id, type, team_encounter_id, final_id')
            ->whereIn('status', ['finished', 'cancelled'])
            ->where('(team_encounter_id IS NULL OR team_encounter_id NOT IN (' . $open('team_encounter_id') . '))', null, false)
            ->where('(final_id IS NULL OR final_id NOT IN (' . $open('final_id') . '))', null, false)
            ->get()->getResultObject();

        $out = ['matches' => [], 'official' => 0, 'friendly' => 0, 'encounters' => [], 'finals' => []];
        foreach ($rows as $r) {
            $out['matches'][] = (int) $r->id;
            $out[$r->type === 'official' ? 'official' : 'friendly']++;
            if ($r->team_encounter_id) $out['encounters'][(int) $r->team_encounter_id] = (int) $r->team_encounter_id;
            if ($r->final_id) $out['finals'][(int) $r->final_id] = (int) $r->final_id;
        }
        $out['encounters'] = array_values($out['encounters']);
        $out['finals']     = array_values($out['finals']);

        return $out;
    }

    public function purgeFinished(): array
    {
        $p = $this->purgeable();
        $this->db->transStart();
        foreach (array_chunk($p['matches'], 500) as $ids) {
            $this->db->table('scoreboard_matches')->whereIn('id', $ids)->delete();
        }
        if ($p['encounters']) $this->db->table('scoreboard_team_encounters')->whereIn('id', $p['encounters'])->delete();
        if ($p['finals'])     $this->db->table('scoreboard_finals')->whereIn('id', $p['finals'])->delete();
        $this->db->transComplete();

        return $p;
    }

    public function getLiveForTable(int $tableId): ?object
    {
        return $this->where('status', 'live')->where('table_id', $tableId)->orderBy('started_at', 'DESC')->first();
    }

    /**
     * Valide et normalise les champs d'une rencontre saisis dans un formulaire (admin ou joueurs).
     * Retourne [données, erreurs].
     */
    public static function dataFromInput(array $in): array
    {
        $errors = [];
        $data   = [];
        // Partie amicale à 3 : jamais par sets, sans chrono
        $trio   = (int) ($in['players'] ?? 2) === 3;
        $keys   = $trio ? ['p1', 'p2', 'p3'] : ['p1', 'p2'];
        $sets   = !$trio && !empty($in['sets_enabled']);
        // Forfait (rencontres d'équipe) : un joueur absent n'a besoin ni de nom ni de points à réaliser
        $absent = (!empty($in['p1_absent']) ? 1 : 0) | (!empty($in['p2_absent']) ? 2 : 0);
        $data['absent'] = $absent;

        foreach ($keys as $i => $p) {
            $last  = mb_strtoupper(trim((string) ($in[$p . '_last_name'] ?? '')), 'UTF-8');
            $first = trim((string) ($in[$p . '_first_name'] ?? ''));
            $club  = trim((string) ($in[$p . '_club'] ?? ''));
            $isAbsent = (bool) ($absent & ($i + 1));
            if ($isAbsent && $last === '') {
                $last = 'ABSENT';
            }
            if (!$isAbsent && ($last === '' || $first === '')) {
                $errors[] = 'Nom et prénom du joueur ' . ($i + 1) . ' obligatoires.';
            }
            $memberId = (int) ($in[$p . '_member_id'] ?? 0);
            $data[$p . '_member_id']  = $memberId > 0 ? $memberId : null;
            $data[$p . '_last_name']  = $last;
            $data[$p . '_first_name'] = $first;
            $data[$p . '_club']       = $club !== '' ? $club : null;
            $license = mb_substr(trim((string) ($in[$p . '_license'] ?? '')), 0, 25);
            $data[$p . '_license']    = $license !== '' ? $license : null;

            // Par sets : les points d'un set remplacent les points à réaliser (voir plus bas)
            $distance = (int) ($in[$p . '_distance'] ?? 0);
            if (!$sets && !$isAbsent && ($distance < 1 || $distance > 9999)) {
                $errors[] = 'Les points à réaliser du joueur ' . ($i + 1) . ' doivent être compris entre 1 et 9999.';
            }
            $data[$p . '_distance'] = max(0, $distance);
        }

        $discipline = (string) ($in['discipline'] ?? '');
        if (!array_key_exists($discipline, self::DISCIPLINES)) {
            $errors[] = 'Discipline invalide.';
        }
        $data['discipline'] = $discipline;

        $format = (string) ($in['format'] ?? '');
        if (!array_key_exists($format, self::FORMATS)) {
            $errors[] = 'Format de billard invalide.';
        }
        $data['format'] = $format;

        $modes = FrbbCategoryModel::gameModesFor($discipline, $format);
        foreach ($keys as $i => $p) {
            $data[$p . '_category_id'] = $data[$p . '_category'] = $data[$p . '_average_min'] = null;
            $categoryId = (int) ($in[$p . '_category_id'] ?? 0);
            if ($categoryId <= 0) {
                continue;
            }
            $cat = (new FrbbCategoryModel())->find($categoryId);
            if (!$cat || !in_array($cat->game_mode, $modes, true)) {
                $errors[] = 'La catégorie du joueur ' . ($i + 1) . ' ne correspond pas à la discipline et au format choisis.';
                continue;
            }
            $data[$p . '_category_id'] = (int) $cat->id;
            $data[$p . '_category']    = $cat->categories;
            $data[$p . '_average_min'] = $cat->average_min;
        }

        $data['innings_limit'] = null;
        if (!empty($in['innings_limit_enabled'])) {
            $limit = (int) ($in['innings_limit'] ?? 0);
            if ($limit < 1 || $limit > 999) {
                $errors[] = 'La limite de reprises doit être comprise entre 1 et 999.';
            }
            $data['innings_limit'] = $limit;
        }

        $data['unequal_innings']    = !empty($in['unequal_innings']) ? 1 : 0;

        $data['sets_to_win'] = null;
        if ($sets) {
            if ($discipline !== '3bandes') {
                $errors[] = "Le jeu par sets n'est possible qu'en 3 bandes.";
            }
            // Points d'un set propres à chaque joueur (handicap, ex. Coupe de Belgique) ; set_points = ancien champ commun
            foreach (['p1', 'p2'] as $p) {
                $setPoints = (int) ($in[$p . '_distance'] ?? 0) ?: (int) ($in['set_points'] ?? self::DEFAULT_SET_POINTS);
                if ($setPoints < 1 || $setPoints > 999) {
                    $errors[] = "Les points d'un set doivent être compris entre 1 et 999.";
                }
                $data[$p . '_distance'] = $setPoints;
            }
            $toWin = (int) ($in['sets_to_win'] ?? 0);
            if ($toWin < 1 || $toWin > 9) {
                $errors[] = 'Le nombre de sets gagnants doit être compris entre 1 et 9.';
            }
            $data['sets_to_win']     = $toWin;
            // Règle du jeu par sets : toujours à reprises inégales, sans limite de reprises
            $data['unequal_innings'] = 1;
            $data['innings_limit']   = null;
        }
        $data['shot_clock_enabled'] = !$trio && !empty($in['shot_clock_enabled']) ? 1 : 0;
        $seconds = (int) ($in['shot_clock_seconds'] ?? 40);
        $data['shot_clock_seconds'] = max(5, min(300, $seconds ?: 40));
        $data['shot_clock_extensions'] = max(0, min(10, (int) ($in['shot_clock_extensions'] ?? 3)));

        $tableId = (int) ($in['table_id'] ?? 0);
        $data['table_id'] = $tableId > 0 ? $tableId : null;

        return [$data, $errors];
    }

    /**
     * Mémorise les joueurs externes pour l'auto-complétion des encodages futurs.
     */
    public static function rememberExternalPlayers(array $data): void
    {
        $players = new ScoreboardPlayerModel();
        foreach (['p1', 'p2', 'p3'] as $i => $p) {
            if (!array_key_exists($p . '_last_name', $data)) {
                continue;
            }
            if ($data[$p . '_member_id'] === null && !((int) ($data['absent'] ?? 0) & ($i + 1))) {
                $cat = !empty($data[$p . '_category_id']) ? (new FrbbCategoryModel())->find($data[$p . '_category_id']) : null;
                $players->remember($data[$p . '_last_name'], $data[$p . '_first_name'], $data[$p . '_club'], $data[$p . '_license'] ?? null,
                                   $cat ? [$cat->game_mode => $cat->categories] : []);
            }
        }
    }

    /**
     * Enregistre l'état envoyé par le marquoir.
     * $points : séries dans l'ordre chronologique (le joueur qui casse joue les index pairs).
     * Un état plus ancien que celui en base est ignoré (rejeu après coupure réseau).
     */
    public function applyState(object $match, array $state): int
    {
        $rev = (int) ($state['rev'] ?? 0);
        if ($rev <= (int) $match->rev) {
            return (int) $match->rev;
        }

        $totals = array_fill_keys(self::playerNumbers($match), ['points' => 0, 'innings' => 0, 'high' => 0]);
        $walk   = self::walkInnings($match, array_map('intval', array_values((array) ($state['innings'] ?? []))));
        $rows   = [];

        foreach ($walk['rows'] as $r) {
            $player = $r['player'];
            $totals[$player]['innings']++;
            $totals[$player]['points'] += $r['points'];
            $totals[$player]['high']    = max($totals[$player]['high'], $r['points']);
            $rows[] = [
                'match_id'  => $match->id,
                'set_no'    => $r['set'],
                'inning_no' => $r['inning'],
                'player'    => $player,
                'points'    => $r['points'],
            ];
        }

        $ext = (array) ($state['extensions'] ?? []);
        $update = [
            'p1_points' => $totals[1]['points'], 'p1_innings' => $totals[1]['innings'], 'p1_high_run' => $totals[1]['high'],
            'p2_points' => $totals[2]['points'], 'p2_innings' => $totals[2]['innings'], 'p2_high_run' => $totals[2]['high'],
            'p1_sets'   => $walk['won'][1], 'p2_sets' => $walk['won'][2],
            'p1_extensions_used' => (int) ($ext[0] ?? 0),
            'p2_extensions_used' => (int) ($ext[1] ?? 0),
            'current_run' => max(0, (int) ($state['current_run'] ?? 0)),
            'paused'      => !empty($state['paused']) && empty($state['finished']) ? 1 : 0,
            'rev'         => $rev,
        ];
        if (self::isTrio($match)) {
            $update += ['p3_points' => $totals[3]['points'], 'p3_innings' => $totals[3]['innings'], 'p3_high_run' => $totals[3]['high']];
        }

        if (!empty($state['finished'])) {
            $update['status']      = 'finished';
            $update['finished_at'] = self::now();
            $update['current_run'] = 0;
            $update['winner']      = self::isTrio($match)
                ? self::trioWinner($match, $totals)
                : (self::isSets($match)
                ? match ($walk['won'][1] <=> $walk['won'][2]) { 1 => 1, -1 => 2, default => 0 }
                : self::winnerOf($match, $totals[1]['points'], $totals[1]['innings'], $totals[2]['points'], $totals[2]['innings']));
        } elseif ($match->status === 'finished') {
            // Correction après la fin (touche « - » sur l'écran de résultat) : la partie reprend
            $update['status']      = 'live';
            $update['finished_at'] = null;
            $update['winner']      = null;
        }

        $this->db->transStart();
        $this->db->table('scoreboard_innings')->where('match_id', $match->id)->delete();
        if ($rows) {
            $this->db->table('scoreboard_innings')->insertBatch($rows);
        }
        $this->update($match->id, $update);
        $this->db->transComplete();

        // Fin (ou correction) d'un match de finale : ordre des matchs 3 à 6 et matchs de classement à jour
        if ($match->final_id && (isset($update['winner']) || $match->status === 'finished')) {
            (new ScoreboardFinalModel())->sync((int) $match->final_id);
        }

        return $rev;
    }

    public static function isSets(object $m): bool
    {
        return !empty($m->sets_to_win);
    }

    /** Partie amicale à 3 joueurs (rotation 1 → 2 → 3). */
    public static function isTrio(object $m): bool
    {
        return !empty($m->p3_last_name);
    }

    public static function playerCount(object $m): int
    {
        return self::isTrio($m) ? 3 : 2;
    }

    /** @return list<int> */
    public static function playerNumbers(object $m): array
    {
        return range(1, self::playerCount($m));
    }

    /** @return list<string> ex. ['p1', 'p2', 'p3'] */
    public static function playerKeys(object $m): array
    {
        return array_map(static fn (int $n): string => 'p' . $n, self::playerNumbers($m));
    }

    /** Joueur qui casse un set : celui qui a commencé la partie garde la bille blanche, les sets alternent. */
    public static function setStarter(int $starter, int $setNo): int
    {
        return $setNo % 2 === 1 ? $starter : ($starter === 1 ? 2 : 1);
    }

    /**
     * Découpe les séries chronologiques en reprises numérotées (par set en jeu par sets).
     * Un set s'arrête dès qu'un joueur atteint ses points (reprises inégales), le suivant
     * est cassé par l'autre joueur. À 3 joueurs, rotation 1 → 2 → 3 depuis celui qui casse.
     * Doit rester identique à walk() de scoreboard-display.js.
     *
     * @return array{rows: list<array{set:int, inning:int, player:int, points:int}>, won: array{1:int, 2:int}}
     */
    public static function walkInnings(object $m, array $innings): array
    {
        $starter = (int) ($m->starting_player ?: 1);
        $toWin   = (int) ($m->sets_to_win ?? 0);
        $count   = self::playerCount($m);
        $dist    = [];
        foreach (self::playerNumbers($m) as $n) {
            $dist[$n] = (int) $m->{'p' . $n . '_distance'};
        }
        $won     = [1 => 0, 2 => 0];
        $rows    = [];

        $set = 1; $inSet = 0;
        $pts = array_fill_keys(array_keys($dist), 0); $inn = $pts;
        foreach ($innings as $v) {
            $first  = self::setStarter($starter, $set);
            $player = ($first - 1 + $inSet) % $count + 1;
            $v      = max(0, (int) $v);
            $pts[$player] += $v;
            $inn[$player]++;
            $inSet++;
            $rows[] = ['set' => $set, 'inning' => $inn[$player], 'player' => $player, 'points' => $v];

            if ($toWin && $pts[$player] >= $dist[$player]) {
                $won[$player]++;
                if ($won[$player] >= $toWin) {
                    break;
                }
                $set++; $inSet = 0;
                $pts = [1 => 0, 2 => 0]; $inn = [1 => 0, 2 => 0];
            }
        }

        return ['rows' => $rows, 'won' => $won];
    }

    /**
     * Points déjà marqués par chaque joueur dans ce qui reste à jouer : le set en cours en jeu par sets,
     * toute la partie sinon (les points à réaliser ne peuvent pas descendre en dessous).
     */
    public function pointsInPlay(object $m): array
    {
        $walk = self::walkInnings($m, $this->stateFor($m)['innings']);
        $set  = $walk['rows'] ? end($walk['rows'])['set'] : 1;
        $pts  = array_fill_keys(self::playerNumbers($m), 0);
        foreach ($walk['rows'] as $r) {
            if ($r['set'] === $set) {
                $pts[$r['player']] += $r['points'];
            }
        }
        // Le set qui vient de se terminer n'est plus en jeu : le suivant repart de zéro
        if (self::isSets($m) && $m->status !== 'finished' && max($pts[1] - (int) $m->p1_distance, $pts[2] - (int) $m->p2_distance) >= 0) {
            return [1 => 0, 2 => 0];
        }
        return $pts;
    }

    /**
     * Données d'un match perdu par forfait : il est directement terminé, sans reprise.
     * absent : 1 = joueur 1, 2 = joueur 2, 3 = les deux (aucun vainqueur).
     */
    public static function forfeitData(int $absent, ?string $when): array
    {
        return [
            'absent' => $absent, 'status' => 'finished', 'winner' => match ($absent) { 1 => 2, 2 => 1, default => null },
            'starting_player' => null, 'current_run' => 0, 'paused' => 0, 'started_at' => null, 'finished_at' => $when ?? self::now(),
            'p1_points' => 0, 'p1_innings' => 0, 'p1_high_run' => 0, 'p1_sets' => 0,
            'p2_points' => 0, 'p2_innings' => 0, 'p2_high_run' => 0, 'p2_sets' => 0,
        ];
    }

    /** Score affiché dans les listes : sets gagnés en jeu par sets, sinon points. */
    public static function score(object $m, string $p): int
    {
        return (int) $m->{$p . (self::isSets($m) ? '_sets' : '_points')};
    }

    /**
     * Vainqueur (1, 2, ou 0 = nul) d'une partie terminée.
     * Si l'un des joueurs a atteint sa distance, on compare le pourcentage de la distance réalisé
     * (distances différentes = handicap). Sinon la partie s'est arrêtée à la limite de reprises :
     * on départage à la moyenne proportionnelle (moyenne réalisée / moyenne minimum de la catégorie),
     * ou au pourcentage de la distance si une catégorie manque.
     * Doit rester identique au calcul de l'écran (scoreboard-display.js, winnerOf).
     */
    public static function winnerOf(object $m, int $pts1, int $inn1, int $pts2, int $inn2): int
    {
        $prop1 = self::proportionalAverage($m, 'p1', $pts1, $inn1);
        $prop2 = self::proportionalAverage($m, 'p2', $pts2, $inn2);
        $reached = $pts1 >= (int) $m->p1_distance || $pts2 >= (int) $m->p2_distance;

        $cmp = (!$reached && $prop1 !== null && $prop2 !== null)
            ? round($prop1, 9) <=> round($prop2, 9)
            : ($pts1 * (int) $m->p2_distance) <=> ($pts2 * (int) $m->p1_distance);

        return match ($cmp) { 1 => 1, -1 => 2, default => 0 };
    }

    /** Moyenne réalisée divisée par la moyenne minimum de la catégorie (null si inconnue). */
    public static function proportionalAverage(object $m, string $p, int $points, int $innings): ?float
    {
        $min = (float) ($m->{$p . '_average_min'} ?? 0);
        return $min > 0 && $innings > 0 ? ($points / $innings) / $min : null;
    }

    /**
     * Classement d'une partie à 3 : [joueur => rang] (1 = premier, ex æquo possibles).
     * Même règle que winnerOf() : distance atteinte par quelqu'un → pourcentage de la distance ;
     * sinon (limite de reprises) moyenne proportionnelle si tous ont une catégorie.
     * $totals : [joueur => ['points' => …, 'innings' => …]]. Doit rester identique à ranking() de scoreboard-display.js.
     */
    public static function ranking(object $m, array $totals): array
    {
        $reached = false;
        $prop    = [];
        foreach ($totals as $n => $t) {
            $reached = $reached || $t['points'] >= (int) $m->{'p' . $n . '_distance'};
            $prop[$n] = self::proportionalAverage($m, 'p' . $n, $t['points'], $t['innings']);
        }
        $useProp = !$reached && !in_array(null, $prop, true);

        $cmp = static function (int $a, int $b) use ($m, $totals, $prop, $useProp): int {
            if ($useProp) {
                return round($prop[$a], 9) <=> round($prop[$b], 9);
            }
            return ($totals[$a]['points'] * (int) $m->{'p' . $b . '_distance'}) <=> ($totals[$b]['points'] * (int) $m->{'p' . $a . '_distance'});
        };

        $ranks = [];
        foreach (array_keys($totals) as $a) {
            $ranks[$a] = 1;
            foreach (array_keys($totals) as $b) {
                if ($a !== $b && $cmp($b, $a) > 0) $ranks[$a]++;
            }
        }
        return $ranks;
    }

    /** Vainqueur d'une partie à 3 : seul premier du classement, 0 si ex æquo en tête. */
    public static function trioWinner(object $m, array $totals): int
    {
        $first = array_keys(self::ranking($m, $totals), 1, true);
        return count($first) === 1 ? $first[0] : 0;
    }

    /** Classement d'une partie à 3 terminée, d'après les totaux enregistrés. */
    public static function storedRanking(object $m): array
    {
        $totals = [];
        foreach ([1, 2, 3] as $n) {
            $totals[$n] = ['points' => (int) $m->{'p' . $n . '_points'}, 'innings' => (int) $m->{'p' . $n . '_innings'}];
        }
        return self::ranking($m, $totals);
    }

    /** Vrai si la partie terminée a été départagée à la moyenne proportionnelle. */
    public static function decidedByProportional(object $m): bool
    {
        return $m->status === 'finished' && !self::isSets($m)
            && (int) $m->p1_points < (int) $m->p1_distance && (int) $m->p2_points < (int) $m->p2_distance
            && self::proportionalAverage($m, 'p1', (int) $m->p1_points, (int) $m->p1_innings) !== null
            && self::proportionalAverage($m, 'p2', (int) $m->p2_points, (int) $m->p2_innings) !== null;
    }

    public static function proportionalLabel(object $m, string $p): string
    {
        $v = self::proportionalAverage($m, $p, (int) $m->{$p . '_points'}, (int) $m->{$p . '_innings'});
        return $v === null ? '' : number_format($v, 3, ',', '');
    }

    /** ex. « 1A (0,811) » */
    public static function categoryLabel(object $m, string $p): string
    {
        if (empty($m->{$p . '_category'})) {
            return '';
        }
        return $m->{$p . '_category'} . ' (' . number_format((float) $m->{$p . '_average_min'}, 3, ',', '') . ')';
    }

    /**
     * Remet une rencontre à l'état « prévue » en effaçant toutes les reprises.
     */
    public function resetToScheduled(int $id): void
    {
        $this->db->transStart();
        $this->db->table('scoreboard_innings')->where('match_id', $id)->delete();
        $this->db->table('scoreboard_signatures')->where('match_id', $id)->delete();
        // Le résultat de la rencontre d'équipe change : la feuille récapitulative signée n'est plus valable
        $this->db->query('DELETE s FROM scoreboard_team_signatures s JOIN scoreboard_matches m ON m.team_encounter_id = s.encounter_id WHERE m.id = ?', [$id]);
        $this->update($id, [
            'status' => 'scheduled', 'starting_player' => null, 'current_run' => 0, 'paused' => 0, 'winner' => null, 'absent' => 0,
            'p1_points' => 0, 'p1_innings' => 0, 'p1_high_run' => 0, 'p1_sets' => 0, 'p1_extensions_used' => 0,
            'p2_points' => 0, 'p2_innings' => 0, 'p2_high_run' => 0, 'p2_sets' => 0, 'p2_extensions_used' => 0,
            'started_at' => null, 'finished_at' => null,
        ]);
        $this->db->query('UPDATE scoreboard_matches SET rev = rev + 1 WHERE id = ?', [$id]);
        $this->db->transComplete();
    }

    /**
     * Une amicale annulée disparaît des listes ; une rencontre du DS redevient « prévue ».
     */
    public function resetOrCancel(object $match): void
    {
        if ($match->type === 'friendly') {
            $this->update($match->id, ['status' => 'cancelled']);
        } else {
            $this->resetToScheduled((int) $match->id);
        }
    }

    /**
     * Le joueur qui commence devient toujours le joueur 1 (à gauche, chiffres blancs).
     * $order : à 3 joueurs, ordre de jeu complet (anciens numéros), ex. [2, 3, 1].
     */
    public function startWith(object $match, int $starter, int $tableId, array $order = []): void
    {
        $data = [
            'status'          => 'live',
            'table_id'        => $tableId,
            'starting_player' => 1,
            'started_at'      => self::now(),
        ];

        if (self::isTrio($match)) {
            $data += self::permutedPlayers($match, $order);
        } elseif ($starter === 2) {
            $data += self::swappedPlayers($match);
        }

        $this->update($match->id, $data);
    }

    /**
     * Partie à 3 démarrée par erreur dans le mauvais ordre : tant qu'aucun coup n'est joué, elle redevient
     * « prévue » pour repasser par « Qui commence ? ».
     */
    public function backToOrderChoice(object $match): bool
    {
        $played = $this->db->table('scoreboard_innings')->where('match_id', $match->id)->countAllResults();
        if (!self::isTrio($match) || $match->status !== 'live' || $played > 0 || (int) $match->current_run > 0) {
            return false;
        }

        $this->update($match->id, ['status' => 'scheduled', 'started_at' => null, 'paused' => 0, 'rev' => (int) $match->rev + 1]);

        return true;
    }

    /** Ordre de jeu valide à 3 : une permutation de 1, 2, 3. */
    public static function validOrder(array $order): bool
    {
        $sorted = array_map('intval', $order);
        sort($sorted);
        return $sorted === [1, 2, 3];
    }

    private static function permutedPlayers(object $match, array $order): array
    {
        $data = [];
        foreach (array_values($order) as $i => $from) {
            foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'category_id', 'category', 'average_min', 'distance'] as $f) {
                $data['p' . ($i + 1) . '_' . $f] = $match->{'p' . (int) $from . '_' . $f};
            }
        }
        return $data;
    }

    /**
     * Inverse les deux joueurs d'une partie démarrée, tant qu'aucun coup n'a été joué
     * (l'arbitre s'est trompé de joueur au moment de choisir qui commence).
     */
    public function swapPlayers(object $match): bool
    {
        $played = $this->db->table('scoreboard_innings')->where('match_id', $match->id)->countAllResults();
        if ($match->status !== 'live' || $played > 0 || (int) $match->current_run > 0) {
            return false;
        }

        $data = self::swappedPlayers($match);
        $data['p1_extensions_used'] = $match->p2_extensions_used;
        $data['p2_extensions_used'] = $match->p1_extensions_used;
        $data['rev'] = (int) $match->rev + 1;
        $this->update($match->id, $data);

        return true;
    }

    private static function swappedPlayers(object $match): array
    {
        $data = [];
        foreach (['member_id', 'final_player_id', 'last_name', 'first_name', 'club', 'license', 'category_id', 'category', 'average_min', 'distance'] as $f) {
            $data['p1_' . $f] = $match->{'p2_' . $f};
            $data['p2_' . $f] = $match->{'p1_' . $f};
        }
        return $data;
    }

    /**
     * Heure belge, explicite pour que les rencontres (saisies en heure locale) restent justes
     * quel que soit le fuseau configuré dans App::$appTimezone.
     */
    public static function now(string $format = 'Y-m-d H:i:s'): string
    {
        return (new \DateTime('now', new \DateTimeZone(self::TIMEZONE)))->format($format);
    }

    /** Horodatage de l'heure belge, comparable à strtotime() d'une date du marquoir. */
    public static function nowTs(): int
    {
        return strtotime(self::now());
    }

    /**
     * Moment à partir duquel une rencontre programmée peut être démarrée (null = sans heure, toujours possible).
     */
    public static function startAllowedAt(object $m, int $windowMinutes): ?int
    {
        return $m->scheduled_at ? strtotime($m->scheduled_at) - $windowMinutes * 60 : null;
    }

    /** Match de classement d'une finale dont les joueurs dépendent du classement de la poule, pas encore terminée. */
    public static function isPendingPlacement(object $m): bool
    {
        return !empty($m->final_placement) && empty($m->p1_final_player_id);
    }

    public static function canStartNow(object $m, int $windowMinutes): bool
    {
        if (self::isPendingPlacement($m)) {
            return false;
        }
        $from = self::startAllowedAt($m, $windowMinutes);
        return $from === null || self::nowTs() >= $from;
    }

    /**
     * « CDR · LPF001 » pour un match de rencontre d'équipe (requiert withTable()), sinon la compétition saisie.
     */
    public static function competitionLabel(object $m): string
    {
        $parts = [];
        if (!empty($m->te_code)) {
            $parts[] = $m->te_competition . ' · ' . $m->te_code;
        }
        if (!empty($m->competition)) {
            $parts[] = $m->competition;
        }
        return implode(' — ', $parts);
    }

    /** ex. « 3 Bandes PB » */
    public static function disciplineLabel(object $m): string
    {
        return (self::DISCIPLINES[$m->discipline] ?? $m->discipline) . ' ' . ($m->format ?? '');
    }

    public static function distanceLabel(object $m): string
    {
        if (self::isSets($m)) {
            return (int) $m->sets_to_win . ' set' . ((int) $m->sets_to_win > 1 ? 's' : '') . ' gagnant' . ((int) $m->sets_to_win > 1 ? 's' : '')
                 . ' de ' . self::setPointsLabel($m) . ' pts';
        }
        $dist = array_map(static fn (string $p): int => (int) $m->{$p . '_distance'}, self::playerKeys($m));
        return count(array_unique($dist)) === 1 ? $dist[0] . ' pts' : implode(' / ', $dist) . ' pts';
    }

    /** Points d'un set : « 15 », ou « 15 / 12 » quand les joueurs n'ont pas les mêmes. */
    public static function setPointsLabel(object $m): string
    {
        return (int) $m->p1_distance === (int) $m->p2_distance ? (string) (int) $m->p1_distance : (int) $m->p1_distance . ' / ' . (int) $m->p2_distance;
    }

    public static function playerLabel(object $m, string $p): string
    {
        return trim($m->{$p . '_last_name'} . ' ' . $m->{$p . '_first_name'});
    }

    public static function average(int $points, int $innings): string
    {
        return number_format($innings > 0 ? $points / $innings : 0, 3, ',', '');
    }
}
