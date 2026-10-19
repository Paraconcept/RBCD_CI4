<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Caisse du bar : clients, catalogue, services (un par enveloppe), tickets et lignes.
 */
class CreateBarPosTables extends Migration
{
    public function up(): void
    {
        $money = ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'];

        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'member_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'last_visit_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('name');
        $this->forge->addUniqueKey('member_id');
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('bar_clients');

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 40],
            'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('bar_categories');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'price'       => $money,
            'vat_group'   => ['type' => 'ENUM', 'constraint' => ['B', 'G'], 'default' => 'B'],
            'position'    => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Case de la grille 4 × 4 ; un produit désactivé a une position NULL et libère sa case
        $this->forge->addUniqueKey(['category_id', 'position']);
        $this->forge->addForeignKey('category_id', 'bar_categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('bar_products');

        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'service_date'        => ['type' => 'DATE'],
            'period'              => ['type' => 'ENUM', 'constraint' => ['am', 'soir']],
            'number'              => ['type' => 'TINYINT', 'unsigned' => true],
            'envelope_code'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'status'              => ['type' => 'ENUM', 'constraint' => ['open', 'closed'], 'default' => 'open'],
            'bartender_member_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'opened_at'           => ['type' => 'DATETIME'],
            'closed_at'           => ['type' => 'DATETIME', 'null' => true],
            'closed_by_member_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'paid_count'          => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'cancelled_count'     => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'cash_total'          => $money,
            'bancontact_total'    => $money,
            'vat_b_total'         => $money,
            'vat_g_total'         => $money,
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Le nom d'enveloppe (EJJ.MM.NN, comme dans la trésorerie) n'a pas d'année : l'unicité porte sur la date
        $this->forge->addUniqueKey(['service_date', 'number']);
        $this->forge->addKey('envelope_code');
        $this->forge->addForeignKey('bartender_member_id', 'members', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('closed_by_member_id', 'members', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('bar_services');

        // MySQL 5.7 n'a pas d'index partiel : 1 si ouvert, NULL sinon, et UNIQUE ignore les NULL → un seul service ouvert
        $this->db->query("ALTER TABLE bar_services
            ADD is_open TINYINT(1) AS (IF(status = 'open', 1, NULL)) STORED,
            ADD UNIQUE KEY bar_services_one_open (is_open)");

        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'number'                 => ['type' => 'INT', 'unsigned' => true],
            'service_id'             => ['type' => 'INT', 'unsigned' => true],
            'client_id'              => ['type' => 'INT', 'unsigned' => true],
            'status'                 => ['type' => 'ENUM', 'constraint' => ['open', 'paid', 'cancelled'], 'default' => 'open'],
            'payment_method'         => ['type' => 'ENUM', 'constraint' => ['cash', 'bancontact'], 'null' => true],
            'total'                  => $money,
            'opened_at'              => ['type' => 'DATETIME'],
            'paid_at'                => ['type' => 'DATETIME', 'null' => true],
            'cancelled_at'           => ['type' => 'DATETIME', 'null' => true],
            'cancelled_by_member_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'cancel_reason'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('number');
        $this->forge->addKey(['service_id', 'status']);
        $this->forge->addKey('client_id');
        $this->forge->addForeignKey('service_id', 'bar_services', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('client_id', 'bar_clients', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('cancelled_by_member_id', 'members', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('bar_tickets');

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ticket_id'    => ['type' => 'INT', 'unsigned' => true],
            'product_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'product_name' => ['type' => 'VARCHAR', 'constraint' => 40],
            'unit_price'   => $money,
            'vat_group'    => ['type' => 'ENUM', 'constraint' => ['B', 'G'], 'default' => 'B'],
            'quantity'     => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['ticket_id', 'product_id']);
        $this->forge->addForeignKey('ticket_id', 'bar_tickets', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('product_id', 'bar_products', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('bar_ticket_lines');
    }

    public function down(): void
    {
        foreach (['bar_ticket_lines', 'bar_tickets', 'bar_services', 'bar_products', 'bar_categories', 'bar_clients'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
