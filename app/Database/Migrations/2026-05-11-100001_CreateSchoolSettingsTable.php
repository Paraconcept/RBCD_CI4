<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolSettingsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'teacher_member_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'contact_member_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'schedule'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'frequency_per_month' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 4],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('school_settings');
    }

    public function down(): void
    {
        $this->forge->dropTable('school_settings');
    }
}
