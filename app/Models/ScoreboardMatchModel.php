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
        'type', 'team_encounter_id', 'status', 'table_id', 'scheduled_at', 'competition',
        'discipline', 'format', 'innings_limit', 'unequal_innings',
        'shot_clock_enabled', 'shot_clock_seconds', 'shot_clock_extensions',
        'p1_member_id', 'p1_last_name', 'p1_first_name', 'p1_club', 'p1_license', 'p1_distance',
        'p1_points', 'p1_innings', 'p1_high_run', 'p1_extensions_used',
        'p2_member_id', 'p2_last_name', 'p2_first_name', 'p2_club', 'p2_license', 'p2_distance',
        'p2_points', 'p2_innings', 'p2_high_run', 'p2_extensions_used',
        'starting_player', 'current_run', 'winner', 'rev',
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
        $rows    = (new ScoreboardInningModel())->getForMatch((int) $match->id);
        $starter = (int) ($match->starting_player ?: 1);
        $byPlayer = [1 => [], 2 => []];
        foreach ($rows as $r) {
            $byPlayer[(int) $r->player][(int) $r->inning_no] = (int) $r->points;
        }

        $innings = [];
        $other   = $starter === 1 ? 2 : 1;
        $count   = max(count($byPlayer[1]), count($byPlayer[2]));
        for ($n = 1; $n <= $count; $n++) {
            if (isset($byPlayer[$starter][$n])) $innings[] = $byPlayer[$starter][$n];
            if (isset($byPlayer[$other][$n]))   $innings[] = $byPlayer[$other][$n];
        }

        return [
            'rev'        => (int) $match->rev,
            'status'     => $match->status,
            'innings'    => $innings,
            'current_run' => (int) $match->current_run,
            'extensions' => [(int) $match->p1_extensions_used, (int) $match->p2_extensions_used],
        ];
    }

    /**
     * Rencontres du jour pour les spectateurs (page publique « En direct »), tous billards confondus.
     */
    public function getTodayForSpectators(): array
    {
        $today = self::now('Y-m-d');
        return $this->withTable()
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

        foreach (['p1', 'p2'] as $i => $p) {
            $last  = mb_strtoupper(trim((string) ($in[$p . '_last_name'] ?? '')), 'UTF-8');
            $first = trim((string) ($in[$p . '_first_name'] ?? ''));
            $club  = trim((string) ($in[$p . '_club'] ?? ''));
            if ($last === '' || $first === '') {
                $errors[] = 'Nom et prénom du joueur ' . ($i + 1) . ' obligatoires.';
            }
            $memberId = (int) ($in[$p . '_member_id'] ?? 0);
            $data[$p . '_member_id']  = $memberId > 0 ? $memberId : null;
            $data[$p . '_last_name']  = $last;
            $data[$p . '_first_name'] = $first;
            $data[$p . '_club']       = $club !== '' ? $club : null;
            $license = mb_substr(trim((string) ($in[$p . '_license'] ?? '')), 0, 25);
            $data[$p . '_license']    = $license !== '' ? $license : null;

            $distance = (int) ($in[$p . '_distance'] ?? 0);
            if ($distance < 1 || $distance > 9999) {
                $errors[] = 'Les points à réaliser du joueur ' . ($i + 1) . ' doivent être compris entre 1 et 9999.';
            }
            $data[$p . '_distance'] = $distance;
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

        $data['innings_limit'] = null;
        if (!empty($in['innings_limit_enabled'])) {
            $limit = (int) ($in['innings_limit'] ?? 0);
            if ($limit < 1 || $limit > 999) {
                $errors[] = 'La limite de reprises doit être comprise entre 1 et 999.';
            }
            $data['innings_limit'] = $limit;
        }

        $data['unequal_innings']    = !empty($in['unequal_innings']) ? 1 : 0;
        $data['shot_clock_enabled'] = !empty($in['shot_clock_enabled']) ? 1 : 0;
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
        foreach (['p1', 'p2'] as $p) {
            if ($data[$p . '_member_id'] === null) {
                $players->remember($data[$p . '_last_name'], $data[$p . '_first_name'], $data[$p . '_club'], $data[$p . '_license'] ?? null);
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

        $starter = (int) $match->starting_player;
        $other   = $starter === 1 ? 2 : 1;
        $totals  = [1 => ['points' => 0, 'innings' => 0, 'high' => 0], 2 => ['points' => 0, 'innings' => 0, 'high' => 0]];
        $rows    = [];

        foreach (array_values((array) ($state['innings'] ?? [])) as $i => $pts) {
            $player = $i % 2 === 0 ? $starter : $other;
            $pts    = max(0, (int) $pts);
            $totals[$player]['innings']++;
            $totals[$player]['points'] += $pts;
            $totals[$player]['high']    = max($totals[$player]['high'], $pts);
            $rows[] = [
                'match_id'  => $match->id,
                'inning_no' => $totals[$player]['innings'],
                'player'    => $player,
                'points'    => $pts,
            ];
        }

        $ext = (array) ($state['extensions'] ?? []);
        $update = [
            'p1_points' => $totals[1]['points'], 'p1_innings' => $totals[1]['innings'], 'p1_high_run' => $totals[1]['high'],
            'p2_points' => $totals[2]['points'], 'p2_innings' => $totals[2]['innings'], 'p2_high_run' => $totals[2]['high'],
            'p1_extensions_used' => (int) ($ext[0] ?? 0),
            'p2_extensions_used' => (int) ($ext[1] ?? 0),
            'current_run' => max(0, (int) ($state['current_run'] ?? 0)),
            'rev'         => $rev,
        ];

        if (!empty($state['finished'])) {
            $update['status']      = 'finished';
            $update['finished_at'] = self::now();
            $update['current_run'] = 0;
            // Distances différentes (handicap) : on compare le pourcentage de la distance réalisé
            $update['winner']      = ($totals[1]['points'] * (int) $match->p2_distance) <=> ($totals[2]['points'] * (int) $match->p1_distance);
            $update['winner']      = match ($update['winner']) { 1 => 1, -1 => 2, default => 0 };
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

        return $rev;
    }

    /**
     * Remet une rencontre à l'état « prévue » en effaçant toutes les reprises.
     */
    public function resetToScheduled(int $id): void
    {
        $this->db->transStart();
        $this->db->table('scoreboard_innings')->where('match_id', $id)->delete();
        $this->update($id, [
            'status' => 'scheduled', 'starting_player' => null, 'current_run' => 0, 'winner' => null,
            'p1_points' => 0, 'p1_innings' => 0, 'p1_high_run' => 0, 'p1_extensions_used' => 0,
            'p2_points' => 0, 'p2_innings' => 0, 'p2_high_run' => 0, 'p2_extensions_used' => 0,
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
     */
    public function startWith(object $match, int $starter, int $tableId): void
    {
        $data = [
            'status'          => 'live',
            'table_id'        => $tableId,
            'starting_player' => 1,
            'started_at'      => self::now(),
        ];

        if ($starter === 2) {
            foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'distance'] as $f) {
                $data['p1_' . $f] = $match->{'p2_' . $f};
                $data['p2_' . $f] = $match->{'p1_' . $f};
            }
        }

        $this->update($match->id, $data);
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

    public static function canStartNow(object $m, int $windowMinutes): bool
    {
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
        return (int) $m->p1_distance === (int) $m->p2_distance
            ? $m->p1_distance . ' pts'
            : $m->p1_distance . ' / ' . $m->p2_distance . ' pts';
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
