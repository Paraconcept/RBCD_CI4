<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBarCalls extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'table_id'  => ['type' => 'INT', 'unsigned' => true],
            'called_at' => ['type' => 'DATETIME'],
            'served_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['table_id', 'served_at']);
        $this->forge->addForeignKey('table_id', 'scoreboard_tables', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bar_calls');
    }

    public function down(): void
    {
        $this->forge->dropTable('bar_calls');
    }
}
