<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardDeviceModel extends Model
{
    protected $table      = 'scoreboard_devices';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['name', 'token_hash', 'created_by', 'last_seen_at', 'last_ip'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /** Appareils autorisés, avec le membre du comité qui les a autorisés. */
    public function listWithAuthor(): array
    {
        return $this->select("scoreboard_devices.*, CONCAT(m.first_name, ' ', m.last_name) AS author")
                    ->join('members m', 'm.id = scoreboard_devices.created_by', 'left')
                    ->orderBy('scoreboard_devices.name', 'ASC')
                    ->findAll();
    }
}
