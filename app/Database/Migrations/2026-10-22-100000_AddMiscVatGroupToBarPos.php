<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMiscVatGroupToBarPos extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE bar_products MODIFY vat_group ENUM('B','G','D') NOT NULL DEFAULT 'B'");
        $this->db->query("ALTER TABLE bar_ticket_lines MODIFY vat_group ENUM('B','G','D') NOT NULL DEFAULT 'B'");
        $this->forge->addColumn('bar_services', [
            'vat_d_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => '0.00',
                'after'      => 'vat_g_total',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('bar_services', ['vat_d_total']);
        // Les ventes « divers » n'ont pas d'équivalent : elles repassent en B (bar)
        $this->db->query("UPDATE bar_ticket_lines SET vat_group = 'B' WHERE vat_group = 'D'");
        $this->db->query("UPDATE bar_products SET vat_group = 'B' WHERE vat_group = 'D'");
        $this->db->query("ALTER TABLE bar_ticket_lines MODIFY vat_group ENUM('B','G') NOT NULL DEFAULT 'B'");
        $this->db->query("ALTER TABLE bar_products MODIFY vat_group ENUM('B','G') NOT NULL DEFAULT 'B'");
    }
}
