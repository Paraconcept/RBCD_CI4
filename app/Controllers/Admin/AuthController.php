<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberLoginModel;
use App\Models\AdminUserRoleModel;

class AuthController extends BaseController
{
    public function login(): mixed
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin/auth/login', ['title' => 'Connexion']);
    }

    public function loginPost()
    {
        if (!$this->validate([
            'email'    => 'required|valid_email',
            'password' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $db     = \Config\Database::connect();
        $member = $db->table('members')->where('email', $email)->get()->getRowObject();

        if (!$member) {
            return redirect()->back()->withInput()->with('error', 'Email ou mot de passe incorrect.');
        }

        $loginModel = new MemberLoginModel();
        $loginRow   = $loginModel->where('member_id', $member->id)->first();

        $roleModel = new AdminUserRoleModel();
        $roles     = $roleModel->getRolesForUser($member->id);

        if (!$loginRow || !$loginRow->is_active || empty($roles)) {
            return redirect()->back()->withInput()
                             ->with('error', 'Compte non autorisé ou inactif.');
        }

        if ($loginRow->locked_until && strtotime($loginRow->locked_until) > time()) {
            return redirect()->back()->withInput()
                             ->with('error', 'Compte verrouillé temporairement. Réessayez dans 15 minutes.');
        }

        if (!password_verify($password, $loginRow->password_hash)) {
            $attempts = ($loginRow->login_attempts ?? 0) + 1;
            $update   = ['login_attempts' => $attempts];
            if ($attempts >= 5) {
                $update['locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            $loginModel->update($loginRow->id, $update);
            return redirect()->back()->withInput()->with('error', 'Email ou mot de passe incorrect.');
        }

        $loginModel->update($loginRow->id, [
            'login_attempts' => 0,
            'locked_until'   => null,
            'last_login'     => date('Y-m-d H:i:s'),
        ]);

        session()->set([
            'admin_logged_in' => true,
            'member_logged_in' => true,
            'member_id'        => $member->id,
            'member_login_id'  => $loginRow->id,
            'member_name'      => $member->first_name . ' ' . $member->last_name,
            'member_email'     => $member->email,
            'member_photo'     => $member->photo,
            'admin_roles'      => $roles,
        ]);

        return redirect()->to(base_url('admin/dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('admin/login'))->with('success', 'Vous avez été déconnecté.');
    }

    public function changePassword(): string
    {
        return view('admin/auth/change_password', [
            'title' => 'Changer votre mot de passe',
        ]);
    }

    public function changePasswordPost()
    {
        if (!$this->validate([
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $loginModel = new MemberLoginModel();
        $loginId    = (int) session()->get('member_login_id');

        $loginModel->update($loginId, [
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
        ]);

        session()->remove('must_change_password');

        return redirect()->to(base_url('admin/dashboard'))
                         ->with('success', 'Mot de passe modifié avec succès.');
    }
}
