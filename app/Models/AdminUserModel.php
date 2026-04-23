<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminUserModel extends Model
{
    protected $table      = 'admin_users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    public const ROLES = [
        'Webmaster',
        'Président',
        'Secrétaire',
        'Directeur Sportif',
        'Trésorier',
    ];

    protected $allowedFields = [
        'last_name', 'first_name', 'email', 'member_id',
        'password_hash', 'password_default_hash', 'password_expires_at',
        'is_active', 'last_login',
        'login_attempts', 'locked_until',
        'reset_code', 'reset_code_expires_at', 'reset_attempts',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'email'      => 'required|valid_email|is_unique[admin_users.email,id,{id}]',
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name'  => 'required|min_length[2]|max_length[100]',
    ];

    public function authenticate(string $email, string $password): object|false
    {
        $user = $this->where('email', $email)->first();

        if (!$user) {
            return false;
        }

        if ($user->locked_until && strtotime($user->locked_until) > time()) {
            return false;
        }

        if (!password_verify($password, $user->password_hash)) {
            $attempts   = ($user->login_attempts ?? 0) + 1;
            $updateData = ['login_attempts' => $attempts];

            if ($attempts >= 5) {
                $updateData['locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }

            $this->update($user->id, $updateData);
            return false;
        }

        $this->update($user->id, [
            'login_attempts' => 0,
            'locked_until'   => null,
            'last_login'     => date('Y-m-d H:i:s'),
        ]);

        return $user;
    }
}
