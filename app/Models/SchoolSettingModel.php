<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolSettingModel extends Model
{
    protected $table      = 'school_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'teacher_member_id',
        'contact_member_id',
        'schedule',
        'frequency_per_month',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
