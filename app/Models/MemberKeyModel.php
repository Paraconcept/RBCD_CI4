<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberKeyModel extends Model
{
    protected $table      = 'members_keys';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'member_id', 'badge_number', 'given_date', 'returned_date', 'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveKeyHolders(): array
    {
        return $this->db->table('members_keys mk')
            ->select('m.id, m.last_name, m.first_name')
            ->join('members m', 'm.id = mk.member_id')
            ->where('mk.member_id IS NOT NULL')
            ->where('mk.returned_date IS NULL')
            ->where('m.is_active', 1)
            ->orderBy('m.last_name')->orderBy('m.first_name')
            ->get()->getResultObject();
    }

    public function getAllWithHolder(): array
    {
        return $this->db->table('members_keys mk')
            ->select(['mk.*', "CONCAT(m.last_name, ' ', m.first_name) AS holder_name"])
            ->join('members m', 'm.id = mk.member_id', 'left')
            ->orderBy('mk.badge_number')
            ->get()->getResultObject();
    }
}
