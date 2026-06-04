<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVatAmountsToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'amount_6pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_found',
            ],
            'amount_12pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'amount_6pct',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_envelopes', ['amount_6pct', 'amount_12pct']);
    }
}
