<?php

namespace App\Models;

use CodeIgniter\Model;

class TreasurySettingModel extends Model
{
    protected $table      = 'treasury_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'annual_cotisation',
        'semester_cotisation',
        'forfait_price',
        'supporter_card_price',
        'lesson_price',
        'hourly_price',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
