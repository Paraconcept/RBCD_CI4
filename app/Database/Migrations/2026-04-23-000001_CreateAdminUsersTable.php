<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminUsersTable extends Migration
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
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'password_default_hash' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
                'null'    => true,
                'default' => null,
            ],
            'password_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'role' => [
                'type'    => "ENUM('Webmaster','Président','Secrétaire','Directeur Sportif','Trésorier')",
                'default' => 'Webmaster',
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'last_login' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
            'login_attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'default'    => 0,
            ],
            'locked_until' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
            'reset_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
            ],
            'reset_code_expires_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'reset_attempts' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('admin_users');
    }

    public function down(): void
    {
        $this->forge->dropTable('admin_users');
    }
}
