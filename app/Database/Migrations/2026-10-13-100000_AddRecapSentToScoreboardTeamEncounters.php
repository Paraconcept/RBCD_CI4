<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecapSentToScoreboardTeamEncounters extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_team_encounters', [
            'recap_sent_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'notes'],
            'recap_sent_to' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'recap_sent_at'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_team_encounters', ['recap_sent_at', 'recap_sent_to']);
    }
}
