<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFormatToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'format' => ['type' => 'ENUM', 'constraint' => ['PB', 'GB'], 'default' => 'PB', 'after' => 'discipline'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'format');
    }
}
