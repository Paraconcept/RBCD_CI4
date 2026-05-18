<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduleMarqueursTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'encounter_id' => ['type' => 'INT', 'unsigned' => true],
            'member_id'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('encounter_id');
        $this->forge->createTable('schedule_marqueurs');
    }

    public function down(): void
    {
        $this->forge->dropTable('schedule_marqueurs');
    }
}
