<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduleEventsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'event_date'  => ['type' => 'DATE', 'null' => false],
            'start_time'  => ['type' => 'TIME', 'null' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'description' => ['type' => 'TEXT', 'null' => true],
            'color'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'blue'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_date');
        $this->forge->createTable('schedule_events');
    }

    public function down(): void
    {
        $this->forge->dropTable('schedule_events');
    }
}
