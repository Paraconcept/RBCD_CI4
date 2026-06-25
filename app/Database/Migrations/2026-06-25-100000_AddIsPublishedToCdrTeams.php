<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPublishedToCdrTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('cdr_teams', [
            'is_published' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'player3_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('cdr_teams', 'is_published');
    }
}
