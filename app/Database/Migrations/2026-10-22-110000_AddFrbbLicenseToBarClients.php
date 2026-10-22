<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFrbbLicenseToBarClients extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('bar_clients', [
            'frbb_license' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'member_id',
            ],
        ]);
        $this->db->query('ALTER TABLE bar_clients ADD INDEX frbb_license (frbb_license)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('bar_clients', ['frbb_license']);
    }
}
