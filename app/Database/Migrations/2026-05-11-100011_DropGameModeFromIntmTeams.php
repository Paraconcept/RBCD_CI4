<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropGameModeFromIntmTeams extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('intm_teams', 'game_mode');
    }

    public function down(): void
    {
        $this->forge->addColumn('intm_teams', [
            'game_mode' => [
                'type'       => 'ENUM',
                'constraint' => ['Libre PF', 'Libre GF', '3 Bandes PF', '3 Bandes GF'],
                'default'    => 'Libre PF',
                'after'      => 'photo',
            ],
        ]);
    }
}
