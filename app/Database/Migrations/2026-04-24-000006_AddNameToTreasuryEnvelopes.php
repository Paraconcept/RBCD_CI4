<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_envelopes', 'name');
    }
}
