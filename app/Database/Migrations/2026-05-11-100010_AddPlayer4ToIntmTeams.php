<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlayer4ToIntmTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('intm_teams', [
            'player4_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'after'    => 'player3_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('intm_teams', 'player4_id');
    }
}
