<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailsToScoreboardTeamEncounters extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_team_encounters', [
            'team_email'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'team_number'],
            'opponent_email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'opponent_number'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_team_encounters', ['team_email', 'opponent_email']);
    }
}
