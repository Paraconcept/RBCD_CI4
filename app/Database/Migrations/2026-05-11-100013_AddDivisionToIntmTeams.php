<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDivisionToIntmTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('intm_teams', [
            'division' => [
                'type'       => 'ENUM',
                'constraint' => ['1', '2A', '2B', '3A', '3B', '3C', '4A', '4B', '4C'],
                'null'       => true,
                'default'    => null,
                'after'      => 'season',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('intm_teams', 'division');
    }
}
