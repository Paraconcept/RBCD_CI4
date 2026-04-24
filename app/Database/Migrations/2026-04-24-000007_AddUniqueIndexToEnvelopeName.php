<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueIndexToEnvelopeName extends Migration
{
    public function up(): void
    {
        $this->forge->addKey('name', false, true, 'treasury_envelopes_name_unique');
        $this->forge->processIndexes('treasury_envelopes');
    }

    public function down(): void
    {
        $this->forge->dropKey('treasury_envelopes', 'treasury_envelopes_name_unique');
    }
}
