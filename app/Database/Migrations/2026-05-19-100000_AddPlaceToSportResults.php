<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlaceToSportResults extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('sport_results', [
            'place' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 1,
                'null'       => false,
                'after'      => 'title',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('sport_results', 'place');
    }
}
