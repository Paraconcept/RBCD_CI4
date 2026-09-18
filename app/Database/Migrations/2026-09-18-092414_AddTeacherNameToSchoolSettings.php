<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeacherNameToSchoolSettings extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('school_settings', [
            'teacher_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'teacher_member_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('school_settings', 'teacher_name');
    }
}
