<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequiresMarquageToScheduleEncounters extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('schedule_encounters', [
            'requires_marquage' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'requires_arbitrage',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('schedule_encounters', 'requires_marquage');
    }
}
