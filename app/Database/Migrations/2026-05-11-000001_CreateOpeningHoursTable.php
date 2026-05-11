<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOpeningHoursTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'TINYINT', 'unsigned' => true, 'auto_increment' => true],
            'day_order'       => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false],
            'day_name'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'is_closed'       => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'morning_open'    => ['type' => 'TIME', 'null' => true, 'default' => null],
            'morning_close'   => ['type' => 'TIME', 'null' => true, 'default' => null],
            'afternoon_open'  => ['type' => 'TIME', 'null' => true, 'default' => null],
            'afternoon_close' => ['type' => 'TIME', 'null' => true, 'default' => null],
            'evening_open'    => ['type' => 'TIME', 'null' => true, 'default' => null],
            'evening_close'   => ['type' => 'TIME', 'null' => true, 'default' => null],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('day_order');
        $this->forge->createTable('opening_hours');
    }

    public function down(): void
    {
        $this->forge->dropTable('opening_hours');
    }
}
