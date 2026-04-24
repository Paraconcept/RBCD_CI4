<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMembersKeysTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'badge_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'given_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'returned_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('members_keys');
    }

    public function down(): void
    {
        $this->forge->dropTable('members_keys', true);
    }
}
