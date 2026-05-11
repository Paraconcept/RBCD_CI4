<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSortOrderToAdminUserRoles extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('admin_user_roles', [
            'sort_order' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'role',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('admin_user_roles', 'sort_order');
    }
}
