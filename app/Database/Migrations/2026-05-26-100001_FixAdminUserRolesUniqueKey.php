<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAdminUserRolesUniqueKey extends Migration
{
    public function up(): void
    {
        // When admin_user_id was dropped, MySQL reduced the composite unique key
        // (admin_user_id, role) to (role) alone — blocking multiple commissaires.
        $this->db->query("ALTER TABLE admin_user_roles DROP INDEX admin_user_id_role");
        $this->db->query("ALTER TABLE admin_user_roles ADD UNIQUE KEY member_id_role (member_id, role)");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE admin_user_roles DROP INDEX member_id_role");
        $this->db->query("ALTER TABLE admin_user_roles ADD UNIQUE KEY admin_user_id_role (role)");
    }
}
