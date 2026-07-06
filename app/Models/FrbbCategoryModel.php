<?php

namespace App\Models;

use CodeIgniter\Model;

class FrbbCategoryModel extends Model
{
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
}
