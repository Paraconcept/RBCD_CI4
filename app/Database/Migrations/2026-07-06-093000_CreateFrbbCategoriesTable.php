<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFrbbCategoriesTable extends Migration
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
            'game_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'game_mode_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'format' => [
                'type'       => 'ENUM',
                'constraint' => ['PF', 'GF'],
                'null'       => false,
            ],
            'categories' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'categories_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => true,
            ],
            'points' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'average_min' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,3',
                'null'       => false,
                'default'    => 0,
            ],
            'average_max' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,3',
                'null'       => false,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['game_mode', 'categories']);

        $this->forge->createTable('frbb_categories');
    }

    public function down(): void
    {
        $this->forge->dropTable('frbb_categories', true);
    }
}
