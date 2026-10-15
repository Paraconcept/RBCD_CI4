<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSignatureRequiredToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'signature_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'shot_clock_auto_start'],
        ]);
        // Matchs des rencontres d'équipe (CDR) déjà encodés : pas de signature, comme pour les nouveaux
        $this->db->table('scoreboard_matches')->where('team_encounter_id IS NOT NULL')->update(['signature_required' => 0]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'signature_required');
    }
}
