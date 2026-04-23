<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminUserRolesTable extends Migration
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
            'admin_user_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['admin_user_id', 'role']);
        $this->forge->addForeignKey('admin_user_id', 'admin_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admin_user_roles');
    }

    public function down(): void
    {
        $this->forge->dropTable('admin_user_roles');
    }
}
