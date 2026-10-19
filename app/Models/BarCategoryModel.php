<?php

namespace App\Models;

use CodeIgniter\Model;

class BarCategoryModel extends Model
{
    protected $table         = 'bar_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['name', 'sort_order', 'is_active'];

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('sort_order')->orderBy('name')->findAll();
    }
}
