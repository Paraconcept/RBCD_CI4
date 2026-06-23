<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsImagesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'news_id'    => ['type' => 'INT', 'unsigned' => true],
            'filename'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('news_id');
        $this->forge->createTable('news_images');
    }

    public function down(): void
    {
        $this->forge->dropTable('news_images');
    }
}
