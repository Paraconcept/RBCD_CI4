<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;
use App\Models\AdminUserRoleModel;
use App\Models\MemberModel;

class AdminUsersController extends BaseController
{
    private AdminUserModel     $model;
    private AdminUserRoleModel $roleModel;

    public function __construct()
    {
        $this->model     = new AdminUserModel();
        $this->roleModel = new AdminUserRoleModel();
    }

    public function index(): string
    {
        $users = $this->model->orderBy('last_name', 'ASC')->findAll();

        // Charger les rôles pour tous les users en une seule requête
        $db        = \Config\Database::connect();
        $allRoles  = $db->table('admin_user_roles')
                        ->select('admin_user_id, role')
                        ->get()->getResultObject();

        $rolesMap = [];
        foreach ($allRoles as $r) {
            $rolesMap[$r->admin_user_id][] = $r->role;
        }

        return view('admin/users/index', [
            'title'       => 'Utilisateurs admin',
            'breadcrumbs' => [['title' => 'Utilisateurs admin']],
            'users'       => $users,
            'rolesMap'    => $rolesMap,
        ]);
    }

    public function pickMember(): string
    {
        $db = \Config\Database::connect();

        // Membres sans compte admin
        $members = $db->table('members m')
                      ->select('m.id, m.first_name, m.last_name, m.email, m.photo')
                      ->join('admin_users au', 'au.member_id = m.id', 'left')
                      ->where('au.id IS NULL')
                      ->where('m.is_active', 1)
                      ->orderBy('m.last_name')->orderBy('m.first_name')
                      ->get()->getResultObject();

        return view('admin/users/pick_member', [
            'title'       => 'Choisir un membre',
            'breadcrumbs' => [
                ['title' => 'Utilisateurs admin', 'url' => base_url('admin/users')],
                ['title' => 'Choisir un membre'],
            ],
            'members' => $members,
        ]);
    }

    public function create(): string
    {
        return view('admin/users/form', [
            'title'       => 'Nouveau compte externe',
            'breadcrumbs' => [
                ['title' => 'Membres du Comité', 'url' => base_url('admin/users')],
                ['title' => 'Compte externe'],
            ],
            'user'      => null,
            'member'    => null,
            'userRoles' => [],
            'roles'     => AdminUserModel::ROLES,
        ]);
    }

    public function createForMember(int $memberId): string
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/users/pick-member'))->with('error', 'Membre introuvable.');
        }

        // Vérifier qu'il n'a pas déjà un compte
        $db = \Config\Database::connect();
        if ($db->table('admin_users')->where('member_id', $memberId)->countAllResults()) {
            return redirect()->to(base_url('admin/users/pick-member'))->with('error', 'Ce membre a déjà un compte admin.');
        }

        return view('admin/users/form', [
            'title'       => 'Nouvel utilisateur — ' . esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                ['title' => 'Utilisateurs admin', 'url' => base_url('admin/users')],
                ['title' => 'Choisir un membre', 'url' => base_url('admin/users/pick-member')],
                ['title' => 'Nouveau'],
            ],
            'user'      => null,
            'member'    => $member,
            'userRoles' => [],
            'roles'     => AdminUserModel::ROLES,
        ]);
    }

    public function store()
    {
        $rawMemberId = $this->request->getPost('member_id');
        $memberId    = $rawMemberId !== null ? (int) $rawMemberId : null;
        $member      = null;

        if ($memberId) {
            $member = (new MemberModel())->find($memberId);
            if (!$member) {
                return redirect()->to(base_url('admin/users/pick-member'))->with('error', 'Membre invalide.');
            }
        }

        $rules = ['email' => 'required|valid_email|is_unique[admin_users.email]'];
        if (!$memberId) {
            $rules['first_name'] = 'required|min_length[2]|max_length[100]';
            $rules['last_name']  = 'required|min_length[2]|max_length[100]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $selectedRoles = $this->request->getPost('roles') ?? [];
        if (empty($selectedRoles)) {
            return redirect()->back()->withInput()->with('errors', ['roles' => 'Sélectionnez au moins un rôle.']);
        }

        $defaultHash = password_hash('Admin@2026', PASSWORD_BCRYPT);

        $userId = $this->model->insert([
            'first_name'            => $member ? $member->first_name : $this->request->getPost('first_name'),
            'last_name'             => $member ? $member->last_name  : mb_strtoupper($this->request->getPost('last_name'), 'UTF-8'),
            'email'                 => $this->request->getPost('email') ?: ($member->email ?? ''),
            'is_active'             => (int) $this->request->getPost('is_active'),
            'member_id'             => $memberId ?: null,
            'password_hash'         => $defaultHash,
            'password_default_hash' => $defaultHash,
        ]);

        $this->roleModel->setRolesForUser((int) $userId, $selectedRoles);

        return redirect()->to(base_url('admin/users'))->with('success', 'Compte admin créé. Mot de passe par défaut : Admin@2026');
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
            'user'      => $user,
            'member'    => null,
            'userRoles' => $this->roleModel->getRolesForUser($id),
            'roles'     => AdminUserModel::ROLES,
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
        ];

        $newPassword = $this->request->getPost('password');
        if (!empty($newPassword)) {
            $rules['password']         = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $selectedRoles = $this->request->getPost('roles') ?? [];
        if (empty($selectedRoles)) {
            return redirect()->back()->withInput()->with('errors', ['roles' => 'Sélectionnez au moins un rôle.']);
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => mb_strtoupper($this->request->getPost('last_name'), 'UTF-8'),
            'email'      => $this->request->getPost('email'),
            'is_active'  => (int) $this->request->getPost('is_active'),
        ];

        if (!empty($newPassword)) {
            $data['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $data);
        $this->roleModel->setRolesForUser($id, $selectedRoles);

        // Rafraîchir la session si l'utilisateur modifie son propre profil
        if (session()->get('admin_id') == $id) {
            session()->set([
                'admin_name'  => $data['first_name'] . ' ' . $data['last_name'],
                'admin_email' => $data['email'],
                'admin_roles' => $selectedRoles,
            ]);
        }

        return redirect()->to(base_url('admin/users'))->with('success', 'Utilisateur mis à jour.');
    }

    public function delete(int $id)
    {
        if (session()->get('admin_id') == $id) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if (!$this->model->find($id)) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Utilisateur introuvable.');
        }

        // Les rôles sont supprimés automatiquement (FK CASCADE)
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
