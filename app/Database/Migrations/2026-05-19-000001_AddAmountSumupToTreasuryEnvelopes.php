<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAmountSumupToTreasuryEnvelopes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('treasury_envelopes', [
            'amount_sumup' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => '0.00',
                'after'      => 'amount_found',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('treasury_envelopes', 'amount_sumup');
    }
}
