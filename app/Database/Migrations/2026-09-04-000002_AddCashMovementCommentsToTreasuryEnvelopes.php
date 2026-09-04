<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashMovementCommentsToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'amount_cash_withdrawal_comment' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_cash_withdrawal',
            ],
            'amount_cash_addition_comment' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_cash_addition',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_envelopes', ['amount_cash_withdrawal_comment', 'amount_cash_addition_comment']);
    }
}
