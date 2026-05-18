<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMembersLoginTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'member_id'             => ['type' => 'INT', 'unsigned' => true],
            'password_hash'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_active'             => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_login'            => ['type' => 'DATETIME', 'null' => true],
            'login_attempts'        => ['type' => 'TINYINT', 'constraint' => 3, 'default' => 0],
            'locked_until'          => ['type' => 'TIMESTAMP', 'null' => true],
            'reset_code'            => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'reset_code_expires_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('member_id');
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('members_login');
    }

    public function down(): void
    {
        $this->forge->dropTable('members_login', true);
    }
}
