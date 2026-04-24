<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameForfaitH1H2ToF1F2 extends Migration
{
    private array $renames = [
        ['forfait_h1_choice',    'forfait_f1_choice',    'TINYINT(1) NOT NULL DEFAULT 0'],
        ['forfait_h1_paid',      'forfait_f1_paid',      'TINYINT(1) NOT NULL DEFAULT 0'],
        ['forfait_h1_paid_date', 'forfait_f1_paid_date', 'DATE NULL DEFAULT NULL'],
        ['forfait_h2_choice',    'forfait_f2_choice',    'TINYINT(1) NOT NULL DEFAULT 0'],
        ['forfait_h2_paid',      'forfait_f2_paid',      'TINYINT(1) NOT NULL DEFAULT 0'],
        ['forfait_h2_paid_date', 'forfait_f2_paid_date', 'DATE NULL DEFAULT NULL'],
    ];

    public function up(): void
    {
        $db = $this->forge->getConnection();
        foreach ($this->renames as [$old, $new, $type]) {
            $db->query("ALTER TABLE member_payments CHANGE COLUMN `{$old}` `{$new}` {$type}");
        }
    }

    public function down(): void
    {
        $db = $this->forge->getConnection();
        foreach ($this->renames as [$old, $new, $type]) {
            $db->query("ALTER TABLE member_payments CHANGE COLUMN `{$new}` `{$old}` {$type}");
        }
    }
}
