<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMemberPaymentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'year' => [
                'type'       => 'SMALLINT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => false,
            ],
            // Cotisation club RBCD (jan–déc)
            'rbcd_paid' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'rbcd_paid_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            // Cotisation fédération FRBB (sep–jun)
            'frbb_paid' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'frbb_paid_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            // Forfait billard H1 (jan–jun) — 75 €
            'forfait_h1_paid' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'forfait_h1_paid_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            // Forfait billard H2 (jul–déc) — 75 €
            'forfait_h2_paid' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'forfait_h2_paid_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['member_id', 'year']);
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('member_payments');
    }

    public function down(): void
    {
        $this->forge->dropTable('member_payments', true);
    }
}
