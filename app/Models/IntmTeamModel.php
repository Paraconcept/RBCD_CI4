<?php

namespace App\Models;

use CodeIgniter\Model;

class IntmTeamModel extends Model
{
    protected $table      = 'intm_teams';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'name', 'season', 'division', 'player1_id', 'player2_id', 'player3_id', 'player4_id', 'is_published',
    ];

    const DIVISIONS = ['1', '2A', '2B', '3A', '3B', '3C', '4A', '4B', '4C'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithPlayers(): array
    {
        return $this->db->table('intm_teams t')
            ->select([
                't.*',
                'CONCAT(m1.last_name, " ", m1.first_name) AS player1_name',
                'CONCAT(m2.last_name, " ", m2.first_name) AS player2_name',
                'CONCAT(m3.last_name, " ", m3.first_name) AS player3_name',
                'CONCAT(m4.last_name, " ", m4.first_name) AS player4_name',
            ])
            ->join('members m1', 'm1.id = t.player1_id')
            ->join('members m2', 'm2.id = t.player2_id')
            ->join('members m3', 'm3.id = t.player3_id')
            ->join('members m4', 'm4.id = t.player4_id')
            ->orderBy('t.name', 'ASC')
            ->get()->getResultObject();
    }
}
