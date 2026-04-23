<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminUserRoleModel extends Model
{
    protected $table         = 'admin_user_roles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['admin_user_id', 'role'];
    protected $useTimestamps = false;

    public function getRolesForUser(int $adminUserId): array
    {
        return $this->where('admin_user_id', $adminUserId)
                    ->orderBy('role', 'ASC')
                    ->findColumn('role') ?? [];
    }

    public function setRolesForUser(int $adminUserId, array $roles): void
    {
        $this->where('admin_user_id', $adminUserId)->delete();

        foreach ($roles as $role) {
            $this->insert(['admin_user_id' => $adminUserId, 'role' => $role]);
        }
    }
}
