<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShotClockExtensionsToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'shot_clock_extensions' => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 3, 'after' => 'shot_clock_seconds'],
        ]);
        $this->db->table('site_settings')->where('key', 'scoreboard_shot_clock_extensions')->update(['value' => '3']);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'shot_clock_extensions');
    }
}
