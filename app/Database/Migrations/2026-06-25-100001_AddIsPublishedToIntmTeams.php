<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPublishedToIntmTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('intm_teams', [
            'is_published' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'player4_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('intm_teams', 'is_published');
    }
}
