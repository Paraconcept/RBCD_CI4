<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropPhotoFromTeamTables extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('cup_teams',  'photo');
        $this->forge->dropColumn('intm_teams', 'photo');
    }

    public function down(): void
    {
        $this->forge->addColumn('cup_teams', [
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null, 'after' => 'season'],
        ]);
        $this->forge->addColumn('intm_teams', [
            'photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null, 'after' => 'season'],
        ]);
    }
}
