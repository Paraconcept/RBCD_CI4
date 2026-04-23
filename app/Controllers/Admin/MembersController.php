<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\AdminUserModel;
use App\Models\MemberPaymentModel;

class MembersController extends BaseController
{
    private MemberModel $model;

    public function __construct()
    {
        $this->model = new MemberModel();
    }

    public function index(): string
    {
        $db = \Config\Database::connect();

        $rows = $db->table('admin_users au')
                   ->select('au.member_id, GROUP_CONCAT(aur.role ORDER BY aur.role SEPARATOR ", ") AS roles')
                   ->join('admin_user_roles aur', 'aur.admin_user_id = au.id')
                   ->where('au.member_id IS NOT NULL')
                   ->groupBy('au.member_id')
                   ->get()->getResultObject();

        $committeeMap = [];
        foreach ($rows as $r) {
            $committeeMap[(int) $r->member_id] = $r->roles;
        }

        return view('admin/members/index', [
            'title'        => 'Membres',
            'breadcrumbs'  => [['title' => 'Membres']],
            'members'      => $this->model->orderBy('last_name', 'ASC')->orderBy('first_name', 'ASC')->findAll(),
            'committeeMap' => $committeeMap,
        ]);
    }

    public function create(): string
    {
        return view('admin/members/form', [
            'title'       => 'Nouveau membre',
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => 'Nouveau'],
            ],
            'member'         => null,
            'linkedAdminUser' => null,
            'freeAdminUsers' => $this->getFreeAdminUsers(),
        ]);
    }

    public function store()
    {
        $rules = [
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'first_name' => 'required|min_length[2]|max_length[100]',
            'gender'     => 'required|in_list[M,F]',
            'email'      => 'permit_empty|valid_email|max_length[150]',
            'photo'      => 'permit_empty|is_image[photo]|max_size[photo,2048]|mime_in[photo,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->collectFormData();

        // Photo upload
        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $data['photo'] = $this->uploadPhoto($photoFile);
        }

        $memberId = $this->model->insert($data);

        // Ligne de paiement pour l'année en cours
        (new MemberPaymentModel())->createForMember((int) $memberId);

        // Lien avec un admin user
        $adminUserId = (int) $this->request->getPost('admin_user_id');
        if ($adminUserId) {
            (new AdminUserModel())->update($adminUserId, ['member_id' => $memberId]);
        }

        return redirect()->to(base_url('admin/members'))->with('success', 'Membre créé avec succès.');
    }

    public function edit(int $id): string
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $db              = \Config\Database::connect();
        $linkedAdminUser = $db->table('admin_users')->where('member_id', $id)->get()->getRowObject();

        return view('admin/members/form', [
            'title'       => 'Modifier un membre',
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
            ],
            'member'          => $member,
            'linkedAdminUser' => $linkedAdminUser,
            'freeAdminUsers'  => $this->getFreeAdminUsers($linkedAdminUser?->id),
        ]);
    }

    public function update(int $id)
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $rules = [
            'last_name'  => 'required|min_length[2]|max_length[100]',
            'first_name' => 'required|min_length[2]|max_length[100]',
            'gender'     => 'required|in_list[M,F]',
            'email'      => 'permit_empty|valid_email|max_length[150]',
            'photo'      => 'permit_empty|is_image[photo]|max_size[photo,2048]|mime_in[photo,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->collectFormData();

        // Nouvelle photo
        $photoFile = $this->request->getFile('photo');
        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $this->deletePhotoFile($member->photo);
            $data['photo'] = $this->uploadPhoto($photoFile);
        }

        // Suppression de la photo demandée
        if ($this->request->getPost('remove_photo') && $member->photo) {
            $this->deletePhotoFile($member->photo);
            $data['photo'] = null;
        }

        $this->model->update($id, $data);

        // Gestion du lien admin user
        $adminUserModel  = new AdminUserModel();
        $db              = \Config\Database::connect();
        $previousLink    = $db->table('admin_users')->where('member_id', $id)->get()->getRowObject();
        $newAdminUserId  = (int) $this->request->getPost('admin_user_id');

        // Délier l'ancien si changement
        if ($previousLink && $previousLink->id !== $newAdminUserId) {
            $adminUserModel->update($previousLink->id, ['member_id' => null]);
        }
        // Lier le nouveau
        if ($newAdminUserId && $newAdminUserId !== ($previousLink?->id ?? 0)) {
            $adminUserModel->update($newAdminUserId, ['member_id' => $id]);
        }

        return redirect()->to(base_url('admin/members'))->with('success', 'Membre mis à jour.');
    }

    public function delete(int $id)
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        // Délier du compte admin si nécessaire
        $db = \Config\Database::connect();
        $db->table('admin_users')->where('member_id', $id)->update(['member_id' => null]);

        $this->deletePhotoFile($member->photo);
        $this->model->delete($id);

        return redirect()->to(base_url('admin/members'))->with('success', 'Membre supprimé.');
    }

    public function toggle(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(base_url('admin/members'));
        }

        $member = $this->model->find($id);
        if (!$member) {
            return $this->response->setJSON(['success' => false, 'message' => 'Membre introuvable.']);
        }

        $newStatus = $member->is_active ? 0 : 1;
        $this->model->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success'   => true,
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'Membre activé.' : 'Membre désactivé.',
        ]);
    }

    // ----------------------------------------------------------------

    private function collectFormData(): array
    {
        $post = $this->request->getPost();

        // Les hidden inputs envoient toujours "0" → vérifier la valeur, pas l'existence
        $bool = fn($key) => ($post[$key] ?? '0') == '1' ? 1 : 0;

        return [
            'last_name'       => mb_strtoupper($post['last_name'], 'UTF-8'),
            'first_name'      => $post['first_name'],
            'gender'          => $post['gender'],
            'birth_date'      => $post['birth_date'] ?: null,
            'address'         => $post['address'] ?: null,
            'postal_code'     => $post['postal_code'] ?: null,
            'city'            => $post['city'] ? mb_strtoupper($post['city'], 'UTF-8') : null,
            'phone'           => $post['phone'] ?: null,
            'mobile'          => $post['mobile'] ?: null,
            'email'           => $post['email'] ?: null,
            'is_federated'    => $bool('is_federated'),
            'frbb_license'    => $post['frbb_license'] ?: null,
            'is_junior'       => $bool('is_junior'),
            'is_supporter'    => $bool('is_supporter'),
            'is_school'       => $bool('is_school'),
            'ranking'         => ($post['ranking'] ?? null) ?: null,
            'is_active'       => $bool('is_active'),
            'show_birth_date' => $bool('show_birth_date'),
            'show_address'    => $bool('show_address'),
            'show_phone'      => $bool('show_phone'),
            'show_mobile'     => $bool('show_mobile'),
            'show_email'      => $bool('show_email'),
        ];
    }

    private function uploadPhoto($file): string
    {
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/members', $newName);
        return $newName;
    }

    private function deletePhotoFile(?string $filename): void
    {
        if ($filename) {
            $path = FCPATH . 'uploads/members/' . $filename;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function getFreeAdminUsers(?int $currentLinkedId = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('admin_users')
                      ->select('id, first_name, last_name')
                      ->where('is_active', 1);

        // Inclure ceux sans member_id + celui actuellement lié
        $builder->groupStart()
                    ->where('member_id IS NULL')
                    ->orWhere('id', $currentLinkedId)
                ->groupEnd();

        return $builder->orderBy('last_name')->get()->getResultObject();
    }
}
