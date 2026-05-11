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
        'forfait_price',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
