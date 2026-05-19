<?php

namespace App\Models;

use CodeIgniter\Model;

class SportResultModel extends Model
{
    protected $table      = 'sport_results';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'season', 'type', 'title', 'place',
        'winner_member_id', 'winner_name', 'winner_photo',
        'final_date', 'pdf_file',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const TYPES = [
        'championnat' => 'Championnat ★',
        'coupe'       => 'Coupe ◆',
        'autre'       => 'Autre',
    ];

    public function getGroupedBySeasonWithWinner(): array
    {
        $rows = $this->db->table('sport_results sr')
            ->select([
                'sr.id', 'sr.season', 'sr.type', 'sr.title', 'sr.place',
                'sr.winner_member_id', 'sr.winner_name', 'sr.winner_photo',
                'sr.final_date', 'sr.pdf_file',
                'm.last_name  AS m_last',
                'm.first_name AS m_first',
                'm.photo      AS m_photo',
                'm.id         AS m_id',
            ])
            ->join('members m', 'm.id = sr.winner_member_id', 'left')
            ->orderBy('sr.season', 'DESC')
            ->orderBy('sr.title', 'ASC')
            ->orderBy('sr.place', 'ASC')
            ->get()->getResultObject();

        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r->season][] = $r;
        }
        return $grouped;
    }

    public function getBySeasonWithWinner(string $season): array
    {
        return $this->db->table('sport_results sr')
            ->select([
                'sr.id', 'sr.season', 'sr.type', 'sr.title',
                'sr.winner_member_id', 'sr.winner_name', 'sr.winner_photo',
                'sr.final_date', 'sr.pdf_file',
                'm.last_name  AS m_last',
                'm.first_name AS m_first',
                'm.photo      AS m_photo',
                'm.id         AS m_id',
            ])
            ->join('members m', 'm.id = sr.winner_member_id', 'left')
            ->where('sr.season', $season)
            ->orderBy('sr.final_date', 'DESC')
            ->get()->getResultObject();
    }

    public function getAllSeasons(): array
    {
        return array_column(
            $this->db->query("SELECT DISTINCT season FROM sport_results ORDER BY season DESC")->getResultArray(),
            'season'
        );
    }
}
