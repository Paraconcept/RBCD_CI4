<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;

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

        session()->set([
            'admin_logged_in' => true,
            'admin_id'        => $user->id,
            'admin_name'      => $user->first_name . ' ' . $user->last_name,
            'admin_email'     => $user->email,
            'admin_role'      => $user->role,
        ]);

        return redirect()->to(base_url('admin/dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('admin/login'))->with('success', 'Vous avez été déconnecté.');
    }
}
