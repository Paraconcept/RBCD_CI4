<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropSortOrderFromSportResults extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('sport_results', 'sort_order');
    }

    public function down(): void
    {
        $this->forge->addColumn('sport_results', [
            'sort_order' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'pdf_file',
            ],
        ]);
    }
}
