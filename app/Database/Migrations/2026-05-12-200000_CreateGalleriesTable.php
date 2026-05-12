<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGalleriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'cover_photo_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'event_date'     => ['type' => 'DATE', 'null' => true, 'default' => null],
            'season'         => ['type' => 'VARCHAR', 'constraint' => 9, 'null' => true, 'default' => null],
            'is_published'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('galleries');
    }

    public function down(): void
    {
        $this->forge->dropTable('galleries', true);
    }
}
