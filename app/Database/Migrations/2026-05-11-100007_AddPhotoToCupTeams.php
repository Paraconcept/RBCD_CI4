<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToCupTeams extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('cup_teams', [
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'season',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('cup_teams', 'photo');
    }
}
