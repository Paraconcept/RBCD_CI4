<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMemberPaymentsAddForfaitChoice extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('member_payments', [
            'forfait_h1_choice' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'frbb_paid_date',
            ],
            'forfait_h2_choice' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'forfait_h1_paid_date',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('member_payments', [
            'forfait_h1_choice',
            'forfait_h2_choice',
        ]);
    }
}
