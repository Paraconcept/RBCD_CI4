<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMembersKeysMemberIdNullable extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE members_keys DROP FOREIGN KEY members_keys_member_id_foreign');

        $this->forge->modifyColumn('members_keys', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        $this->forge->addForeignKey('member_id', 'members', 'id', 'SET NULL', 'SET NULL');
        $this->forge->processIndexes('members_keys');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE members_keys DROP FOREIGN KEY members_keys_member_id_foreign');

        $this->forge->modifyColumn('members_keys', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('members_keys');
    }
}
