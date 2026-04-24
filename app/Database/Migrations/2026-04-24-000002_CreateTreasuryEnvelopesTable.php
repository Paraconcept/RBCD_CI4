<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTreasuryEnvelopesTable extends Migration
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
            'date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['bar', 'divers'],
                'default'    => 'bar',
                'null'       => false,
            ],
            'amount_calculated' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'amount_found' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'closed_by_member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('date');
        $this->forge->addForeignKey('closed_by_member_id', 'members', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('treasury_envelopes');
    }

    public function down(): void
    {
        $this->forge->dropTable('treasury_envelopes', true);
    }
}
