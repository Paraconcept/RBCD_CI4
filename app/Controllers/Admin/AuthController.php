<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;
use App\Models\AdminUserRoleModel;

class AuthController extends BaseController
{
    public function login(): string
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin/auth/login', ['title' => 'Connexion']);
    }

    public function loginPost()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new AdminUserModel();
        $user  = $model->authenticate(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Email ou mot de passe incorrect, ou compte verrouillé.');
        }

        if (!$user->is_active) {
            return redirect()->back()->withInput()->with('error', 'Ce compte est désactivé.');
        }

        $roles = (new AdminUserRoleModel())->getRolesForUser($user->id);

        $memberPhoto = null;
        if ($user->member_id) {
            $linked = \Config\Database::connect()
                ->table('members')->select('photo')
                ->where('id', $user->member_id)->get()->getRowObject();
            $memberPhoto = $linked?->photo ?? null;
        }

        session()->set([
            'admin_logged_in' => true,
            'admin_id'        => $user->id,
            'admin_name'      => $user->first_name . ' ' . $user->last_name,
            'admin_email'     => $user->email,
            'admin_roles'     => $roles,
            'admin_photo'     => $memberPhoto,
        ]);

        if ($user->password_default_hash && password_verify(
            $this->request->getPost('password'),
            $user->password_default_hash
        )) {
            session()->set('must_change_password', true);
            return redirect()->to(base_url('admin/change-password'));
        }

        return redirect()->to(base_url('admin/dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('admin/login'))->with('success', 'Vous avez été déconnecté.');
    }

    public function changePassword(): string
    {
        if (!session()->get('must_change_password')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('admin/auth/change_password', [
            'title' => 'Changer votre mot de passe',
        ]);
    }

    public function changePasswordPost()
    {
        if (!session()->get('must_change_password')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        if (!$this->validate([
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newPassword = $this->request->getPost('password');
        $model       = new AdminUserModel();
        $id          = (int) session()->get('admin_id');
        $user        = $model->find($id);

        if ($user->password_default_hash && password_verify($newPassword, $user->password_default_hash)) {
            return redirect()->back()->with('error', 'Choisissez un mot de passe différent du mot de passe par défaut.');
        }

        $model->update($id, [
            'password_hash'         => password_hash($newPassword, PASSWORD_BCRYPT),
            'password_default_hash' => null,
        ]);

        session()->remove('must_change_password');

        return redirect()->to(base_url('admin/dashboard'))
                         ->with('success', 'Mot de passe modifié avec succès. Bienvenue !');
    }
}
