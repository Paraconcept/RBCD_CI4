<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSportResultsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'season' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['coupe', 'championnat', 'autre'],
                'default'    => 'championnat',
                'null'       => false,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'winner_member_id' => [
                'type'     => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
            'winner_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
            ],
            'final_date' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],
            'pdf_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'sort_order' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('season');
        $this->forge->createTable('sport_results');
    }

    public function down(): void
    {
        $this->forge->dropTable('sport_results', true);
    }
}
