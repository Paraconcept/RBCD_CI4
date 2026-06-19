<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberLoginModel extends Model
{
    protected $table      = 'members_login';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'member_id', 'password_hash', 'is_active',
        'password_changed_at',
        'last_login', 'login_attempts', 'locked_until',
        'reset_code', 'reset_code_expires_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function authenticate(string $email, string $password): object|false
    {
        $row = $this->db->table('members_login ml')
            ->select('ml.*, m.first_name, m.last_name, m.email, m.photo')
            ->join('members m', 'm.id = ml.member_id')
            ->where('m.email', $email)
            ->get()->getRowObject();

        if (!$row) {
            return false;
        }

        if (!$row->is_active) {
            return false;
        }

        if ($row->locked_until && strtotime($row->locked_until) > time()) {
            return false;
        }

        if (!password_verify($password, $row->password_hash)) {
            $attempts = ($row->login_attempts ?? 0) + 1;
            $update   = ['login_attempts' => $attempts];
            if ($attempts >= 5) {
                $update['locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            $this->update($row->id, $update);
            return false;
        }

        $this->update($row->id, [
            'login_attempts' => 0,
            'locked_until'   => null,
            'last_login'     => date('Y-m-d H:i:s'),
        ]);

        return $row;
    }

    public function findByToken(string $token): object|false
    {
        $row = $this->db->table('members_login ml')
            ->select('ml.*, m.first_name, m.last_name, m.email')
            ->join('members m', 'm.id = ml.member_id')
            ->where('ml.reset_code', $token)
            ->where('ml.reset_code_expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowObject();

        return $row ?: false;
    }
}
