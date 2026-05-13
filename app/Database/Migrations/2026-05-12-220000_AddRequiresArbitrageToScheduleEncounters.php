<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequiresArbitrageToScheduleEncounters extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('schedule_encounters', [
            'requires_arbitrage' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'rounds_count',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('schedule_encounters', 'requires_arbitrage');
    }
}
