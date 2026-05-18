<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserRoleModel;
use App\Models\MemberModel;
use App\Models\MemberLoginModel;

class AdminUsersController extends BaseController
{
    private AdminUserRoleModel $roleModel;

    public function __construct()
    {
        $this->roleModel = new AdminUserRoleModel();
    }

    public function index(): string
    {
        $db     = \Config\Database::connect();
        $admins = $db->table('admin_user_roles aur')
                     ->select('m.id, m.first_name, m.last_name, m.email, m.photo,
                               ml.is_active, ml.last_login,
                               GROUP_CONCAT(aur.role ORDER BY aur.sort_order SEPARATOR ", ") AS roles_str')
                     ->join('members m',      'm.id = aur.member_id')
                     ->join('members_login ml', 'ml.member_id = m.id', 'left')
                     ->groupBy('m.id')
                     ->orderBy('m.last_name')->orderBy('m.first_name')
                     ->get()->getResultObject();

        return view('admin/users/index', [
            'title'       => 'Accès Administration',
            'breadcrumbs' => [['title' => 'Accès Administration']],
            'admins'      => $admins,
        ]);
    }

    public function create(): string
    {
        $db = \Config\Database::connect();

        $members = $db->table('members m')
                      ->select('m.id, m.first_name, m.last_name, m.email')
                      ->join('admin_user_roles aur', 'aur.member_id = m.id', 'left')
                      ->where('aur.id IS NULL')
                      ->where('m.is_active', 1)
                      ->orderBy('m.last_name')->orderBy('m.first_name')
                      ->get()->getResultObject();

        return view('admin/users/form', [
            'title'       => 'Donner un accès admin',
            'breadcrumbs' => [
                ['title' => 'Accès Administration', 'url' => base_url('admin/users')],
                ['title' => 'Nouveau'],
            ],
            'member'    => null,
            'userRoles' => [],
            'roles'     => AdminUserRoleModel::ROLES,
            'members'   => $members,
        ]);
    }

    public function store()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $roles    = $this->request->getPost('roles') ?? [];

        if (!$memberId) {
            return redirect()->back()->withInput()->with('errors', ['member_id' => 'Sélectionnez un membre.']);
        }
        if (empty($roles)) {
            return redirect()->back()->withInput()->with('errors', ['roles' => 'Sélectionnez au moins un rôle.']);
        }

        $member = (new MemberModel())->find($memberId);
        if (!$member) {
            return redirect()->back()->withInput()->with('error', 'Membre introuvable.');
        }

        // Ensure members_login exists (will be activated via password reset flow)
        $loginModel = new MemberLoginModel();
        if (!$loginModel->where('member_id', $memberId)->first()) {
            $loginModel->insert([
                'member_id'     => $memberId,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'is_active'     => 0,
            ]);
        }

        $this->roleModel->setRolesForUser($memberId, $roles);

        $name = esc($member->first_name . ' ' . $member->last_name);
        return redirect()->to(base_url('admin/users'))
                         ->with('success', "{$name} a accès à l'administration. Il/elle devra définir son mot de passe via la page de connexion.");
    }

    public function edit(int $memberId): string
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Membre introuvable.');
        }

        return view('admin/users/form', [
            'title'       => 'Modifier l\'accès admin',
            'breadcrumbs' => [
                ['title' => 'Accès Administration', 'url' => base_url('admin/users')],
                ['title' => esc($member->last_name . ' ' . $member->first_name)],
            ],
            'member'    => $member,
            'userRoles' => $this->roleModel->getRolesForUser($memberId),
            'roles'     => AdminUserRoleModel::ROLES,
            'members'   => [],
        ]);
    }

    public function update(int $memberId)
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Membre introuvable.');
        }

        $roles = $this->request->getPost('roles') ?? [];
        if (empty($roles)) {
            return redirect()->back()->withInput()->with('errors', ['roles' => 'Sélectionnez au moins un rôle.']);
        }

        $this->roleModel->setRolesForUser($memberId, $roles);

        if ((int) session()->get('member_id') === $memberId) {
            session()->set([
                'member_name'  => $member->first_name . ' ' . $member->last_name,
                'member_email' => $member->email,
                'admin_roles'  => $roles,
            ]);
        }

        return redirect()->to(base_url('admin/users'))->with('success', 'Rôles mis à jour.');
    }

    public function delete(int $memberId)
    {
        if ((int) session()->get('member_id') === $memberId) {
            return redirect()->to(base_url('admin/users'))
                             ->with('error', 'Vous ne pouvez pas révoquer votre propre accès.');
        }

        $this->roleModel->where('member_id', $memberId)->delete();

        return redirect()->to(base_url('admin/users'))->with('success', 'Accès administration révoqué.');
    }
}
