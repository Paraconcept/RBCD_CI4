<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBarVatToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'amount_21pct_b' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_21pct_g',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_envelopes', ['amount_21pct_b']);
    }
}
