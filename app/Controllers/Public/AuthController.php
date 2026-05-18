<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\MemberLoginModel;
use App\Models\AdminUserRoleModel;

class AuthController extends BaseController
{
    public function login(): mixed
    {
        if (session()->get('member_logged_in')) {
            return redirect()->to(base_url('tableau'));
        }

        $redirect = $this->request->getGet('redirect');
        if ($redirect) {
            session()->set('redirect_after_login', $redirect);
        }

        return view('public/auth/login', [
            'title'       => 'Connexion membres — RBC Disonais',
            'page_title'  => 'Espace réservé aux membres',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Connexion'],
            ],
        ]);
    }

    public function loginPost()
    {
        $isAjax = $this->request->isAJAX();

        if (!$this->validate([
            'email'    => 'required|valid_email',
            'password' => 'required',
        ])) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode(' ', $this->validator->getErrors()),
                    'csrf'    => csrf_hash(),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new MemberLoginModel();
        $user  = $model->authenticate(
            $this->request->getPost('email'),
            $this->request->getPost('password')
        );

        if (!$user) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect, ou compte non activé.',
                    'csrf'    => csrf_hash(),
                ]);
            }
            return redirect()->back()->withInput()
                             ->with('error', 'Email ou mot de passe incorrect, ou compte non activé.');
        }

        $roles = (new AdminUserRoleModel())->getRolesForUser($user->member_id);

        session()->set([
            'member_logged_in' => true,
            'member_id'        => $user->member_id,
            'member_login_id'  => $user->id,
            'member_name'      => $user->first_name . ' ' . $user->last_name,
            'member_email'     => $user->email,
            'member_photo'     => $user->photo,
        ]);

        if (!empty($roles)) {
            session()->set([
                'admin_logged_in' => true,
                'admin_roles'     => $roles,
            ]);
        }

        if ($isAjax) {
            return $this->response->setJSON(['success' => true]);
        }

        $redirect = session()->get('redirect_after_login') ?? base_url('tableau');
        session()->remove('redirect_after_login');

        return redirect()->to($redirect);
    }

    public function logout()
    {
        session()->destroy();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->to(base_url('connexion'))->with('success', 'Vous avez été déconnecté.');
    }
}
