<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTreasurySettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'annual_cotisation' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '50.00'],
            'forfait_price'     => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '75.00'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('treasury_settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('treasury_settings');
    }
}
