<?php

namespace App\Database\Migrations;

use App\Models\ScoreboardSkittlesPoolModel as P;
use CodeIgniter\Database\Migration;

/**
 * Poules 5 quilles déjà encodées : ordre du calendrier officiel (1 = B/C, 2 = C/A, 3 = A/B) au lieu de A/B, A/C, B/C.
 * Les matchs pas encore commencés sont aussi orientés comme le calendrier (« C contre A »).
 */
class ReorderScoreboardSkittlesPoolMatches extends Migration
{
    private const SIDES = ['member_id', 'pool_slot', 'last_name', 'first_name', 'club', 'license', 'category_id', 'category', 'average_min', 'distance'];

    public function up(): void
    {
        $rows = $this->db->table('scoreboard_matches')->where('skittles_pool_id IS NOT NULL')->get()->getResult();
        foreach ($rows as $m) {
            $no = P::matchNumber($m);
            if (!$no) {
                continue;
            }
            $data = ['final_order' => $no];
            if ($m->status === 'scheduled' && (int) $m->p1_pool_slot !== P::PAIRS[$no][0]) {
                foreach (self::SIDES as $f) {
                    $data['p1_' . $f] = $m->{'p2_' . $f};
                    $data['p2_' . $f] = $m->{'p1_' . $f};
                }
            }
            $this->db->table('scoreboard_matches')->where('id', $m->id)->update($data);
        }
    }

    public function down(): void
    {
        $this->db->table('scoreboard_matches')->where('skittles_pool_id IS NOT NULL')->update(['final_order' => null]);
    }
}
