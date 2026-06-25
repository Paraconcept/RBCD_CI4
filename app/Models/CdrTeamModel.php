<?php

namespace App\Models;

use CodeIgniter\Model;

class CdrTeamModel extends Model
{
    protected $table      = 'cdr_teams';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'name', 'season', 'game_mode', 'player1_id', 'player2_id', 'player3_id', 'is_published',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const GAME_MODES = ['Libre PF', 'Libre GF', '3 Bandes PF', '3 Bandes GF'];

    public function getAllWithPlayers(): array
    {
        return $this->db->table('cdr_teams t')
            ->select([
                't.*',
                'CONCAT(m1.last_name, " ", m1.first_name) AS player1_name',
                'CONCAT(m2.last_name, " ", m2.first_name) AS player2_name',
                'CONCAT(m3.last_name, " ", m3.first_name) AS player3_name',
            ])
            ->join('members m1', 'm1.id = t.player1_id')
            ->join('members m2', 'm2.id = t.player2_id')
            ->join('members m3', 'm3.id = t.player3_id', 'left')
            ->orderBy('t.name', 'ASC')
            ->get()->getResultObject();
    }
}
