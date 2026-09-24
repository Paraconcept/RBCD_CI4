<?php

namespace App\Models;

use CodeIgniter\Model;

class SupporterCardSessionModel extends Model
{
    protected $table      = 'supporter_card_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'card_id',
        'session_date',
    ];
}
