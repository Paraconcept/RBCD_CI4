<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashMovementsToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'amount_cash_withdrawal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_21pct_b',
            ],
            'amount_cash_addition' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_cash_withdrawal',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_envelopes', ['amount_cash_withdrawal', 'amount_cash_addition']);
    }
}
