<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShotClockAutoStartToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'shot_clock_auto_start' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'shot_clock_extensions'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'shot_clock_auto_start');
    }
}
