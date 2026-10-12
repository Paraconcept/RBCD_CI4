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
        ], $this->select('id, game_mode, game_mode_text, categories, categories_text, average_min')
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
