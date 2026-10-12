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
        'type', 'status', 'table_id', 'scheduled_at', 'competition',
        'discipline', 'distance', 'innings_limit', 'unequal_innings',
        'shot_clock_enabled', 'shot_clock_seconds',
        'p1_member_id', 'p1_last_name', 'p1_first_name', 'p1_club',
        'p1_points', 'p1_innings', 'p1_high_run', 'p1_extensions_used',
        'p2_member_id', 'p2_last_name', 'p2_first_name', 'p2_club',
        'p2_points', 'p2_innings', 'p2_high_run', 'p2_extensions_used',
        'starting_player', 'current_run', 'winner', 'rev',
        'started_at', 'finished_at', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function withTable()
    {
        return $this->select('scoreboard_matches.*, t.name AS table_name')
                    ->join('scoreboard_tables t', 't.id = scoreboard_matches.table_id', 'left');
    }

    /**
     * Rencontres que l'on peut démarrer sur un billard donné (assignées à ce billard ou sans billard).
     */
    public function getStartableForTable(int $tableId): array
    {
        return $this->withTable()
            ->where('status', 'scheduled')
            ->groupStart()->where('table_id', $tableId)->orWhere('table_id', null)->groupEnd()
            ->groupStart()->where('scheduled_at', null)->orWhere('scheduled_at <=', date('Y-m-d 23:59:59'))->groupEnd()
            ->orderBy('scheduled_at IS NULL', 'ASC', false)
            ->orderBy('scheduled_at', 'ASC')
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
        }

        $discipline = (string) ($in['discipline'] ?? '');
        if (!array_key_exists($discipline, self::DISCIPLINES)) {
            $errors[] = 'Discipline invalide.';
        }
        $data['discipline'] = $discipline;

        $distance = (int) ($in['distance'] ?? 0);
        if ($distance < 1 || $distance > 9999) {
            $errors[] = 'Le nombre de points à réaliser doit être compris entre 1 et 9999.';
        }
        $data['distance'] = $distance;

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
                $players->remember($data[$p . '_last_name'], $data[$p . '_first_name'], $data[$p . '_club']);
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
            $update['finished_at'] = date('Y-m-d H:i:s');
            $update['current_run'] = 0;
            $update['winner']      = $totals[1]['points'] <=> $totals[2]['points'];
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

    public static function playerLabel(object $m, string $p): string
    {
        return trim($m->{$p . '_last_name'} . ' ' . $m->{$p . '_first_name'});
    }

    public static function average(int $points, int $innings): string
    {
        return number_format($innings > 0 ? $points / $innings : 0, 3, ',', '');
    }
}
