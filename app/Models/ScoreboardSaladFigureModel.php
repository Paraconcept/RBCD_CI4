<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Figures de la Salade : liste du club, commune à tous les billards. Chaque ligne garde les derniers réglages
 * utilisés (active, points de chaque joueur), proposés à la partie suivante.
 */
class ScoreboardSaladFigureModel extends Model
{
    public const MAX_ACTIVE = 8;
    public const MAX_POINTS = 10;

    protected $table      = 'scoreboard_salad_figures';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['label', 'is_builtin', 'sort_order', 'active', 'p1_points', 'p2_points'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function ordered(): array
    {
        return $this->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
