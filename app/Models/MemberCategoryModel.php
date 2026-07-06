<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberCategoryModel extends Model
{
    protected $table      = 'members_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'member_id',
        'PLPF', 'PLPF_st',
        'BPF', 'BPF_st',
        'C38_2', 'C38_2_st',
        'C57_2', 'C57_2_st',
        'B3PF', 'B3PF_st',
        'PLGF', 'PLGF_st',
        'BGF', 'BGF_st',
        'C47_2', 'C47_2_st',
        'C47_1', 'C47_1_st',
        'C71_2', 'C71_2_st',
        'B3GF', 'B3GF_st',
    ];

    public function getForMember(int $memberId): ?object
    {
        return $this->where('member_id', $memberId)->first();
    }
}
