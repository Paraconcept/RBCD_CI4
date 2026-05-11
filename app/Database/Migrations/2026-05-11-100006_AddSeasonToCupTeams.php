<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeasonToCupTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('cup_teams', [
            'season' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'default'    => '',
                'after'      => 'name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('cup_teams', 'season');
    }
}
