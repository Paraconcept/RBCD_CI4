<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Paiement d'un ticket en plusieurs parts (ticket d'équipe : deux joueurs en espèces, un par Bancontact).
 * Une ligne par part ; un ticket payé d'un seul mode en a une seule. Les totaux de clôture se calculent sur cette table.
 */
class CreateBarTicketPayments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'unsigned' => true],
            'method'    => ['type' => 'ENUM', 'constraint' => ['cash', 'bancontact']],
            'amount'    => ['type' => 'DECIMAL', 'constraint' => '8,2'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->addForeignKey('ticket_id', 'bar_tickets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bar_ticket_payments', false, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        $this->db->query("ALTER TABLE bar_tickets
            MODIFY payment_method ENUM('cash', 'bancontact', 'mixed') NULL,
            ADD payment_corrected_at DATETIME NULL AFTER paid_at,
            ADD payment_corrected_by_member_id INT UNSIGNED NULL AFTER payment_corrected_at");

        $this->db->query("INSERT INTO bar_ticket_payments (ticket_id, method, amount)
            SELECT id, payment_method, total FROM bar_tickets WHERE status = 'paid' AND payment_method IS NOT NULL");
    }

    public function down()
    {
        $this->forge->dropTable('bar_ticket_payments', true);
        $this->db->query("UPDATE bar_tickets SET payment_method = 'cash' WHERE payment_method = 'mixed'");
        $this->db->query("ALTER TABLE bar_tickets
            MODIFY payment_method ENUM('cash', 'bancontact') NULL,
            DROP payment_corrected_at, DROP payment_corrected_by_member_id");
    }
}
