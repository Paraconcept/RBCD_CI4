<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardPlayerModel extends Model
{
    public const HOME_CLUB = 'RBC Disonais';

    protected $table      = 'scoreboard_players';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['last_name', 'first_name', 'club', 'license'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Membres actifs du club + joueurs externes déjà encodés.
     */
    public function search(string $term, int $limit = 15): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $members = $this->db->table('members')
            ->select('id, last_name, first_name, frbb_license')
            ->where('is_active', 1)
            ->groupStart()->like('last_name', $term)->orLike('first_name', $term)->groupEnd()
            ->orderBy('last_name')->orderBy('first_name')
            ->limit($limit)->get()->getResultObject();

        $externals = $this->groupStart()->like('last_name', $term)->orLike('first_name', $term)->orLike('club', $term)->groupEnd()
            ->orderBy('last_name')->orderBy('first_name')
            ->findAll($limit);

        $results = [];
        foreach ($members as $m) {
            $results[] = [
                'member_id'  => (int) $m->id,
                'last_name'  => mb_strtoupper($m->last_name, 'UTF-8'),
                'first_name' => $m->first_name,
                'club'       => self::HOME_CLUB,
                'license'    => $m->frbb_license,
            ];
        }
        foreach ($externals as $p) {
            $results[] = [
                'member_id'  => null,
                'last_name'  => $p->last_name,
                'first_name' => $p->first_name,
                'club'       => $p->club,
                'license'    => $p->license,
            ];
        }

        return array_slice($results, 0, $limit);
    }

    public function remember(string $lastName, string $firstName, ?string $club, ?string $license = null): void
    {
        $existing = $this->where('last_name', $lastName)
                         ->where('first_name', $firstName)
                         ->where('club', $club)
                         ->first();
        if (!$existing) {
            $this->insert(['last_name' => $lastName, 'first_name' => $firstName, 'club' => $club, 'license' => $license]);
        } elseif ($license !== null && $license !== $existing->license) {
            $this->update($existing->id, ['license' => $license]);
        }
    }
}
