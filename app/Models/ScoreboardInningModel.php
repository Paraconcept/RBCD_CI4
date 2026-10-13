<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardInningModel extends Model
{
    protected $table      = 'scoreboard_innings';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['match_id', 'set_no', 'inning_no', 'player', 'points'];

    public function getForMatch(int $matchId): array
    {
        return $this->where('match_id', $matchId)->orderBy('set_no', 'ASC')->orderBy('inning_no', 'ASC')->orderBy('player', 'ASC')->findAll();
    }
}
