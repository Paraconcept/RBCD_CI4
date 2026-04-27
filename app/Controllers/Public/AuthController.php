<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;
use App\Models\AdminUserRoleModel;

class AuthController extends BaseController
{
    public function login(): string
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to(base_url('tableau'));
        }

        return view('public/auth/login', ['title' => 'Connexion membres']);
    }

    public function loginPost()
    {
        if (!$this->validate([
            'email'    => 'required|valid_email',
            'password' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new AdminUserModel();
        $user  = $model->authenticate(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        if (!$user) {
            return redirect()->back()->withInput()
                             ->with('error', 'Email ou mot de passe incorrect, ou compte verrouillé.');
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

        $redirect = session()->get('redirect_after_login') ?? base_url('tableau');
        session()->remove('redirect_after_login');

        return redirect()->to($redirect);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('tableau'))->with('success', 'Vous avez été déconnecté.');
    }
}
