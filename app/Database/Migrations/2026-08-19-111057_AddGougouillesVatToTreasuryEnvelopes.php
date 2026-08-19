<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGougouillesVatToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'amount_21pct_g' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_12pct',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_envelopes', ['amount_21pct_g']);
    }
}
