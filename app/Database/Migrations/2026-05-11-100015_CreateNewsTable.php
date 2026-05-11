<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'excerpt'      => ['type' => 'TEXT', 'null' => true],
            'content'      => ['type' => 'LONGTEXT'],
            'image'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'published_at' => ['type' => 'DATE', 'null' => true],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('news');
    }

    public function down(): void
    {
        $this->forge->dropTable('news');
    }
}
