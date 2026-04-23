<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;

class AdminUsersController extends BaseController
{
    private AdminUserModel $model;

    public function __construct()
    {
        $this->model = new AdminUserModel();
    }

    public function index(): string
    {
        return view('admin/users/index', [
            'title'       => 'Utilisateurs admin',
            'breadcrumbs' => [['title' => 'Utilisateurs admin']],
            'users'       => $this->model->orderBy('last_name', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('admin/users/form', [
            'title'       => 'Nouvel utilisateur',
            'breadcrumbs' => [
                ['title' => 'Utilisateurs admin', 'url' => base_url('admin/users')],
                ['title' => 'Nouveau'],
            ],
            'user'  => null,
            'roles' => AdminUserModel::ROLES,
        ]);
    }

    public function store()
    {
        $rules = [
            'first_name'       => 'required|min_length[2]|max_length[100]',
            'last_name'        => 'required|min_length[2]|max_length[100]',
            'email'            => 'required|valid_email|is_unique[admin_users.email]',
            'role'             => 'required|in_list[' . implode(',', AdminUserModel::ROLES) . ']',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hash = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $this->model->insert([
            'first_name'            => $this->request->getPost('first_name'),
            'last_name'             => $this->request->getPost('last_name'),
            'email'                 => $this->request->getPost('email'),
            'role'                  => $this->request->getPost('role'),
            'is_active'             => (int) $this->request->getPost('is_active'),
            'password_hash'         => $hash,
            'password_default_hash' => $hash,
        ]);

        return redirect()->to(base_url('admin/users'))->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(int $id): string
    {
        $user = $this->model->find($id);
        if (!$user) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Utilisateur introuvable.');
        }

        return view('admin/users/form', [
            'title'       => 'Modifier un utilisateur',
            'breadcrumbs' => [
                ['title' => 'Utilisateurs admin', 'url' => base_url('admin/users')],
                ['title' => 'Modifier'],
            ],
            'user'  => $user,
            'roles' => AdminUserModel::ROLES,
        ]);
    }

    public function update(int $id)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Utilisateur introuvable.');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'email'      => "required|valid_email|is_unique[admin_users.email,id,{$id}]",
            'role'       => 'required|in_list[' . implode(',', AdminUserModel::ROLES) . ']',
        ];

        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword)) {
            $rules['password']         = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'role'       => $this->request->getPost('role'),
            'is_active'  => (int) $this->request->getPost('is_active'),
        ];

        if (!empty($newPassword)) {
            $data['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $data);

        // Rafraîchir le nom en session si l'utilisateur modifie son propre profil
        if (session()->get('admin_id') == $id) {
            session()->set('admin_name', $data['first_name'] . ' ' . $data['last_name']);
        }

        return redirect()->to(base_url('admin/users'))->with('success', 'Utilisateur mis à jour.');
    }

    public function delete(int $id)
    {
        if (session()->get('admin_id') == $id) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user = $this->model->find($id);
        if (!$user) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Utilisateur introuvable.');
        }

        $this->model->delete($id);

        return redirect()->to(base_url('admin/users'))->with('success', 'Utilisateur supprimé.');
    }

    public function toggle(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(base_url('admin/users'));
        }

        if (session()->get('admin_id') == $id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Impossible de désactiver votre propre compte.']);
        }

        $user = $this->model->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Utilisateur introuvable.']);
        }

        $newStatus = $user->is_active ? 0 : 1;
        $this->model->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success'   => true,
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'Compte activé.' : 'Compte désactivé.',
        ]);
    }
}
