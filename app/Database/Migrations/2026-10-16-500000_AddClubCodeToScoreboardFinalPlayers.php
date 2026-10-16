<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClubCodeToScoreboardFinalPlayers extends Migration
{
    public function up(): void
    {
        // Code FRBB du club (ex. « LLV 06 »), imprimé sur la feuille de classement du championnat régional
        $this->forge->addColumn('scoreboard_final_players', [
            'club_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'club'],
        ]);
        if (!$this->db->table('site_settings')->where('key', 'frbb_club_code')->countAllResults()) {
            $now = date('Y-m-d H:i:s');
            $this->db->table('site_settings')->insert(['key' => 'frbb_club_code', 'value' => 'LLV 06', 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_final_players', 'club_code');
        $this->db->table('site_settings')->where('key', 'frbb_club_code')->delete();
    }
}
