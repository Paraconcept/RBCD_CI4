<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFormatToScoreboardTables extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_tables', [
            'format' => ['type' => 'VARCHAR', 'constraint' => 2, 'default' => 'PB', 'after' => 'name'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_tables', 'format');
    }
}
