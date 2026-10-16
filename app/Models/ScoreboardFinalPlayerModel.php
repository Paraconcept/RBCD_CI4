<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardFinalPlayerModel extends Model
{
    protected $table      = 'scoreboard_final_players';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'final_id', 'position', 'member_id', 'last_name', 'first_name', 'club', 'license',
        'category_id', 'category', 'average_min', 'average_max', 'distance',
    ];

    public function getForFinal(int $finalId): array
    {
        return $this->where('final_id', $finalId)->orderBy('position', 'ASC')->findAll();
    }
}
