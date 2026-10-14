<?php

namespace App\Models;

use CodeIgniter\Model;

class FrbbCategoryModel extends Model
{
    /**
     * Modes de jeu du barème (colonnes de members_categories) pour une discipline et un format du marquoir.
     * Le marquoir ne distingue pas les cadres : tous ceux du format sont proposés.
     */
    public const SCOREBOARD_GAME_MODES = [
        'libre'   => ['PB' => ['PLPF'], 'GB' => ['PLGF']],
        'bande'   => ['PB' => ['BPF'], 'GB' => ['BGF']],
        '3bandes' => ['PB' => ['B3PF'], 'GB' => ['B3GF']],
        'cadre'   => ['PB' => ['C38_2', 'C57_2'], 'GB' => ['C47_2', 'C47_1', 'C71_2']],
    ];

    protected $table      = 'categories_new';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'game_mode',
        'game_mode_text',
        'format',
        'categories',
        'categories_text',
        'points',
        'average_min',
        'average_max',
    ];

    /**
     * Barème indexé par "game_mode|categories" pour lookup direct dans les vues.
     */
    public function getScale(): array
    {
        $scale = [];
        foreach ($this->findAll() as $row) {
            $scale["{$row->game_mode}|{$row->categories}"] = $row;
        }
        return $scale;
    }

    /**
     * Options de sélection groupées par game_mode, pour les <select> du formulaire admin.
     */
    public function getOptionsByGameMode(): array
    {
        $options = [];
        foreach ($this->orderBy('id', 'ASC')->findAll() as $row) {
            $options[$row->game_mode][] = ['val' => $row->categories, 'label' => $row->categories_text];
        }
        return $options;
    }

    public static function gameModesFor(?string $discipline, ?string $format): array
    {
        return self::SCOREBOARD_GAME_MODES[$discipline][$format] ?? [];
    }

    /**
     * Barème pour les formulaires du marquoir, filtré côté navigateur selon discipline et format.
     */
    public function forScoreboard(): array
    {
        $modes = self::scoreboardGameModes();

        $list = array_map(static fn ($r) => [
            'id'        => (int) $r->id,
            'mode'      => $r->game_mode,
            'mode_text' => $r->game_mode_text,
            'code'      => $r->categories,
            'label'     => $r->categories_text ?: $r->categories,
            'avg_min'   => (float) $r->average_min,
            'points'    => (int) $r->points,
        ], $this->select('id, game_mode, game_mode_text, categories, categories_text, points, average_min')
                ->whereIn('game_mode', $modes)->orderBy('id', 'ASC')->findAll());

        return ['modes' => self::SCOREBOARD_GAME_MODES, 'list' => $list];
    }

    /**
     * Catégories d'un membre par mode de jeu, ex. ['B3PF' => '1A', 'PLPF' => '2C'].
     */
    public static function memberCategories(array $memberIds): array
    {
        $memberIds = array_filter(array_map('intval', $memberIds));
        if (!$memberIds) {
            return [];
        }

        $modes = self::scoreboardGameModes();

        $out = [];
        $rows = db_connect()->table('members_categories')->whereIn('member_id', $memberIds)->get()->getResultArray();
        foreach ($rows as $row) {
            $out[(int) $row['member_id']] = array_filter(
                array_intersect_key($row, array_flip($modes)),
                static fn ($v) => $v !== null && $v !== ''
            );
        }
        return $out;
    }

    /** Ordre et titres des tableaux de la page publique « Tableaux des moyennes » (comme sur le site FRBB). */
    public const AVERAGE_TABLES = [
        'PF' => ['PLPF' => 'Partie Libre', 'C38_2' => 'Cadre 38/2', 'C57_2' => 'Cadre 57/2', 'BPF' => 'Par la bande', 'B3PF' => '3 Bandes'],
        'GF' => ['PLGF' => 'Partie Libre', 'C47_2' => 'Cadre 47/2', 'C47_1' => 'Cadre 47/1', 'C71_2' => 'Cadre 71/2', 'BGF' => 'Par la bande', 'B3GF' => '3 Bandes'],
    ];

    public const AVERAGE_NOTES = [
        'C47_1' => 'Réservé aux joueurs Exc 47/2 et Exc 71/2',
        'C71_2' => 'Réservé aux joueurs Exc 47/2 et Exc 47/1',
    ];

    /**
     * Tableaux des moyennes par format puis mode de jeu, de la catégorie la plus haute à la plus basse.
     * Petit billard : moyennes 2m10 = 2m30 × 8/7 tronquées au millième, max = min de la catégorie supérieure − 0,001
     * (règle vérifiée sur le barème FRBB 2026-2027).
     *
     * @return array<string, array<string, array{title: string, note: ?string, rows: list<array>}>>
     */
    public function averageTables(): array
    {
        $rows = $this->orderBy('average_min', 'DESC')->orderBy('points', 'DESC')->findAll();

        $out = [];
        foreach (self::AVERAGE_TABLES as $format => $modes) {
            foreach ($modes as $mode => $title) {
                $list = array_values(array_filter($rows, static fn ($r) => $r->game_mode === $mode));
                if (!$list) {
                    continue;
                }
                $prevMin = null;
                $table   = [];
                foreach ($list as $r) {
                    $min = (int) round((float) $r->average_min * 1000);
                    $max = (float) $r->average_max > 0 ? (int) round((float) $r->average_max * 1000) : null;
                    $row = [
                        'label'     => in_array($r->categories, ['Exc', 'Hon', 'Sup'], true) ? $r->categories_text : $r->categories,
                        'highlight' => in_array($r->categories, ['Hon', 'Hon-VPR', 'Exc'], true),
                        'points'    => (int) $r->points,
                        'min'       => $min,
                        'max'       => $max,
                    ];
                    if ($format === 'PF') {
                        $min210          = intdiv($min * 8, 7);
                        $row['min_210']  = $min210;
                        $row['max_210']  = $prevMin === null ? null : $prevMin - 1;
                        $prevMin         = $min210;
                    }
                    $table[] = $row;
                }
                $out[$format][$mode] = ['title' => $title, 'note' => self::AVERAGE_NOTES[$mode] ?? null, 'rows' => $table];
            }
        }
        return $out;
    }

    private static function scoreboardGameModes(): array
    {
        $modes = [];
        foreach (self::SCOREBOARD_GAME_MODES as $byFormat) {
            foreach ($byFormat as $list) {
                array_push($modes, ...$list);
            }
        }
        return $modes;
    }
}
