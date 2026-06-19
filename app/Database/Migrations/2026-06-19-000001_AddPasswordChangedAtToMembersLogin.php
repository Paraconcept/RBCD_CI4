<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordChangedAtToMembersLogin extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('members_login', [
            'password_changed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'is_active',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('members_login', 'password_changed_at');
    }
}
