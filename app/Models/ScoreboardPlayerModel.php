<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardPlayerModel extends Model
{
    public const HOME_CLUB = 'RBC DISONAIS';

    protected $table      = 'scoreboard_players';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['last_name', 'first_name', 'club', 'club_code', 'license', 'categories'];

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

        $memberCats = FrbbCategoryModel::memberCategories(array_column($members, 'id'));
        $homeCode   = (string) (new SiteSettingModel())->getSetting('frbb_club_code', '');

        $results = [];
        foreach ($members as $m) {
            $results[] = [
                'member_id'  => (int) $m->id,
                'last_name'  => mb_strtoupper($m->last_name, 'UTF-8'),
                'first_name' => $m->first_name,
                'club'       => self::HOME_CLUB,
                'club_code'  => $homeCode,
                'license'    => $m->frbb_license,
                'categories' => $memberCats[(int) $m->id] ?? (object) [],
            ];
        }
        foreach ($externals as $p) {
            $results[] = [
                'member_id'  => null,
                'last_name'  => $p->last_name,
                'first_name' => $p->first_name,
                'club'       => $p->club,
                'club_code'  => $p->club_code,
                'license'    => $p->license,
                'categories' => (object) json_decode((string) $p->categories, true),
            ];
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * $categories : catégories connues par mode de jeu, fusionnées avec celles déjà mémorisées.
     */
    public function remember(string $lastName, string $firstName, ?string $club, ?string $license = null, array $categories = [], ?string $clubCode = null): void
    {
        $existing = $this->where('last_name', $lastName)
                         ->where('first_name', $firstName)
                         ->where('club', $club)
                         ->first();
        if (!$existing) {
            $this->insert([
                'last_name' => $lastName, 'first_name' => $firstName, 'club' => $club, 'club_code' => $clubCode, 'license' => $license,
                'categories' => $categories ? json_encode($categories) : null,
            ]);
            return;
        }

        $update = [];
        if ($license !== null && $license !== $existing->license) {
            $update['license'] = $license;
        }
        if ($clubCode !== null && $clubCode !== $existing->club_code) {
            $update['club_code'] = $clubCode;
        }
        if ($categories) {
            $merged = array_merge((array) json_decode((string) $existing->categories, true), $categories);
            $update['categories'] = json_encode($merged);
        }
        if ($update) {
            $this->update($existing->id, $update);
        }
    }
}
