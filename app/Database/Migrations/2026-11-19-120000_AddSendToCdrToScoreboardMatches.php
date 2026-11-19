<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSendToCdrToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'send_to_cdr' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'send_to_dsr'],
        ]);

        // Matchs CDR déjà encodés : rattachés à une rencontre d'équipe CDR, ou encodés seuls avec « CDR… » en compétition
        $this->db->query("UPDATE scoreboard_matches m LEFT JOIN scoreboard_team_encounters t ON t.id = m.team_encounter_id
            SET m.send_to_cdr = 1
            WHERE t.competition = 'CDR' OR m.competition LIKE 'CDR%' OR m.competition LIKE '%coupe des r_gions%'");
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'send_to_cdr');
    }
}
