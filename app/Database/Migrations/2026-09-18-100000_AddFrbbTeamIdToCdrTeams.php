<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFrbbTeamIdToCdrTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('cdr_teams', [
            'frbb_team_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'game_mode',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('cdr_teams', 'frbb_team_id');
    }
}
