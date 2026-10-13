<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardTableModel extends Model
{
    protected $table      = 'scoreboard_tables';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['name', 'format', 'sort_order', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
    }
}
