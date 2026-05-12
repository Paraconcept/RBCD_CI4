<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShowPhotoToMembers extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE members ADD COLUMN show_photo TINYINT(1) NOT NULL DEFAULT 1 AFTER show_birth_date");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE members DROP COLUMN show_photo");
    }
}
