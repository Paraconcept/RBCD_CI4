<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGalleryPhotosTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'gallery_id' => ['type' => 'INT', 'unsigned' => true],
            'filename'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'caption'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'sort_order' => ['type' => 'SMALLINT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('gallery_id');
        $this->forge->createTable('gallery_photos');
    }

    public function down(): void
    {
        $this->forge->dropTable('gallery_photos', true);
    }
}
