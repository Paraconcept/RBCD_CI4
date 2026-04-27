<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddModifiedByToTreasuryEnvelopes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_envelopes', [
            'modified_by_member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'encoded_by_member_id',
            ],
        ]);

        $this->forge->addForeignKey('modified_by_member_id', 'members', 'id', 'SET NULL', 'SET NULL');
        $this->forge->processIndexes('treasury_envelopes');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('treasury_envelopes', 'treasury_envelopes_modified_by_member_id_foreign');
        $this->forge->dropColumn('treasury_envelopes', 'modified_by_member_id');
    }
}
