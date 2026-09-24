<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupporterCards extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'member_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'card_number'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            // Carte prépayée : la date d'achat est la date de paiement
            'purchase_date' => ['type' => 'DATE', 'null' => false],
            'expiry_date'   => ['type' => 'DATE', 'null' => false],
            'amount'        => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => false, 'default' => 0],
            'notes'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['member_id', 'purchase_date']);
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('supporter_cards');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'card_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'session_date' => ['type' => 'DATE', 'null' => false],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('card_id');
        $this->forge->addForeignKey('card_id', 'supporter_cards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('supporter_card_sessions');

        $this->forge->addColumn('treasury_settings', [
            'supporter_card_price' => [
                'type' => 'DECIMAL', 'constraint' => '8,2', 'null' => false, 'default' => 30,
                'after' => 'forfait_price',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_settings', 'supporter_card_price');
        $this->forge->dropTable('supporter_card_sessions');
        $this->forge->dropTable('supporter_cards');
    }
}
