<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WidenResetCodeColumn extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE admin_users MODIFY reset_code VARCHAR(64) NULL');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE admin_users MODIFY reset_code VARCHAR(10) NULL');
    }
}
