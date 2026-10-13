<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPauseCommentAbsentToScoreboard extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'paused'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'current_run'],
            // Forfait : 1 = joueur 1 absent, 2 = joueur 2 absent, 3 = les deux
            'absent'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'winner'],
            'comment' => ['type' => 'TEXT', 'null' => true, 'after' => 'absent'],
        ]);
        $this->forge->addColumn('scoreboard_team_encounters', [
            'recap_comment' => ['type' => 'TEXT', 'null' => true, 'after' => 'notes'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_team_encounters', 'recap_comment');
        $this->forge->dropColumn('scoreboard_matches', ['paused', 'absent', 'comment']);
    }
}
