<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminUserRoleModel extends Model
{
    protected $table         = 'admin_user_roles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['member_id', 'role', 'sort_order'];
    protected $useTimestamps = false;

    public const ROLES = [
        'Webmaster',
        'Président',
        'Vice-Président',
        'Secrétaire',
        'Secrétaire Adjoint',
        'Directeur Sportif',
        'Directeur Sportif Adjoint',
        'Trésorier',
        'Trésorier Adjoint',
        'Commissaire',
        'PR & Communication',
    ];

    public function getRolesForUser(int $memberId): array
    {
        return $this->where('member_id', $memberId)
                    ->orderBy('sort_order', 'ASC')
                    ->findColumn('role') ?? [];
    }

    public function setRolesForUser(int $memberId, array $roles): void
    {
        $this->where('member_id', $memberId)->delete();

        foreach (array_values($roles) as $order => $role) {
            $this->insert([
                'member_id'  => $memberId,
                'role'       => $role,
                'sort_order' => $order,
            ]);
        }
    }
}
