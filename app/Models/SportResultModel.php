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
        'final_date', 'pdf_file', 'is_published',
        'cdr_team_id', 'intm_team_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public const TYPES = [
        'championnat' => 'Championnat ★',
        'coupe'       => 'Coupe ◆',
        'autre'       => 'Autre',
    ];

    public function getGroupedBySeasonWithWinner(bool $publishedOnly = false): array
    {
        $builder = $this->db->table('sport_results sr')
            ->select([
                'sr.id', 'sr.season', 'sr.type', 'sr.title', 'sr.place',
                'sr.winner_member_id', 'sr.winner_name', 'sr.winner_photo',
                'sr.final_date', 'sr.pdf_file', 'sr.is_published',
                'm.last_name  AS m_last',
                'm.first_name AS m_first',
                'm.photo      AS m_photo',
                'm.id         AS m_id',
            ])
            ->join('members m', 'm.id = sr.winner_member_id', 'left')
            ->orderBy('sr.season', 'DESC')
            ->orderBy('sr.final_date', 'DESC')
            ->orderBy('sr.title', 'ASC')
            ->orderBy('sr.place', 'ASC');
        if ($publishedOnly) { $builder->where('sr.is_published', 1); }
        $rows = $builder->get()->getResultObject();

        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r->season][] = $r;
        }
        return $grouped;
    }

    public function getBySeasonWithWinner(string $season, bool $publishedOnly = false): array
    {
        $builder = $this->db->table('sport_results sr')
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
            ->where('sr.season', $season)
            ->orderBy('sr.final_date', 'DESC')
            ->orderBy('sr.title', 'ASC')
            ->orderBy('sr.place', 'ASC');
        if ($publishedOnly) { $builder->where('sr.is_published', 1); }
        return $builder->get()->getResultObject();
    }

    public function getByMember(int $memberId, ?string $season = null, bool $publishedOnly = false): array
    {
        $m  = (int) $memberId;
        $sql = "
            SELECT sr.season, sr.type, sr.title, sr.place, sr.final_date, sr.pdf_file,
                   sr.cdr_team_id, sr.intm_team_id
            FROM sport_results sr
            LEFT JOIN cdr_teams  ct ON ct.id  = sr.cdr_team_id
            LEFT JOIN intm_teams it ON it.id  = sr.intm_team_id
            WHERE (
                sr.winner_member_id = {$m}
                OR ct.player1_id = {$m} OR ct.player2_id = {$m} OR ct.player3_id = {$m}
                OR it.player1_id = {$m} OR it.player2_id = {$m}
                OR it.player3_id = {$m} OR it.player4_id = {$m}
            )
        ";
        if ($season !== null)  { $sql .= " AND sr.season = " . $this->db->escape($season); }
        if ($publishedOnly)    { $sql .= " AND sr.is_published = 1"; }
        $sql .= " GROUP BY sr.id ORDER BY sr.final_date DESC, sr.title ASC, sr.place ASC";

        return $this->db->query($sql)->getResultObject();
    }

    public function getAllSeasons(): array
    {
        return array_column(
            $this->db->query("SELECT DISTINCT season FROM sport_results ORDER BY season DESC")->getResultArray(),
            'season'
        );
    }

    public function getByCdrTeam(int $teamId, bool $publishedOnly = false): array
    {
        $builder = $this->db->table('sport_results')
            ->where('cdr_team_id', $teamId)
            ->orderBy('final_date', 'DESC')
            ->orderBy('title', 'ASC')
            ->orderBy('place', 'ASC');
        if ($publishedOnly) { $builder->where('is_published', 1); }
        return $builder->get()->getResultObject();
    }

    public function getByIntmTeam(int $teamId, bool $publishedOnly = false): array
    {
        $builder = $this->db->table('sport_results')
            ->where('intm_team_id', $teamId)
            ->orderBy('final_date', 'DESC')
            ->orderBy('title', 'ASC')
            ->orderBy('place', 'ASC');
        if ($publishedOnly) { $builder->where('is_published', 1); }
        return $builder->get()->getResultObject();
    }
}
