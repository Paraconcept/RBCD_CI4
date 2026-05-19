<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropRankingFromMembers extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('members', 'ranking');
    }

    public function down(): void
    {
        $this->forge->addColumn('members', [
            'ranking' => ['type' => 'INT', 'null' => true, 'default' => null, 'after' => 'is_school'],
        ]);
    }
}
