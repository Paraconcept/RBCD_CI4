<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTreasuryRevenuesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'revenue_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['caisse', 'virement'],
                'default'    => 'caisse',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'admin_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('revenue_date');
        $this->forge->createTable('treasury_revenues');
    }

    public function down(): void
    {
        $this->forge->dropTable('treasury_revenues');
    }
}
