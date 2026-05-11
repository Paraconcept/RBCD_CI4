<?php

namespace App\Models;

use CodeIgniter\Model;

class OpeningHourModel extends Model
{
    protected $table      = 'opening_hours';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'day_order', 'day_name', 'is_closed',
        'morning_open', 'morning_close',
        'afternoon_open', 'afternoon_close',
        'evening_open', 'evening_close',
    ];

    public function getAllOrdered(): array
    {
        return $this->orderBy('day_order')->findAll();
    }
}
