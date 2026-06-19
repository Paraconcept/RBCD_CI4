<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MemberPaymentModel;
use App\Models\MemberKeyModel;

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

        $rows = $db->table('admin_user_roles')
                   ->select('member_id, GROUP_CONCAT(role ORDER BY sort_order SEPARATOR ", ") AS roles')
                   ->groupBy('member_id')
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
            'member' => null,
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

        return redirect()->to(base_url('admin/members'))->with('success', 'Membre créé avec succès.');
    }

    public function edit(int $id): string
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $keyModel     = new MemberKeyModel();
        $paymentModel = new MemberPaymentModel();
        $validTabs    = ['coordonnees', 'visibilite', 'photo', 'cles', 'cotisations', 'categories'];
        $activeTab    = in_array($this->request->getGet('tab'), $validTabs, true)
            ? $this->request->getGet('tab')
            : 'coordonnees';

        return view('admin/members/edit', [
            'title'       => esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
            ],
            'member'        => $member,
            'activeTab'     => $activeTab,
            'memberKeys'    => $keyModel->where('member_id', $id)->orderBy('given_date', 'DESC')->findAll(),
            'availableKeys' => $keyModel->where('member_id IS NULL')->orderBy('badge_number')->findAll(),
            'payments'      => $paymentModel->getForMember($id),
        ]);
    }

    public function updateIdentity(int $id)
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
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('admin/members/' . $id . '/edit?tab=coordonnees'))
                             ->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $bool = fn($key) => ($post[$key] ?? '0') == '1' ? 1 : 0;

        $this->model->update($id, [
            'last_name'    => mb_strtoupper($post['last_name'], 'UTF-8'),
            'first_name'   => $post['first_name'],
            'gender'       => $post['gender'],
            'birth_date'   => $post['birth_date']   ?: null,
            'reg_nat'      => $post['reg_nat']       ?: null,
            'address'      => $post['address']       ?: null,
            'postal_code'  => $post['postal_code']   ?: null,
            'city'         => $post['city'] ? mb_strtoupper($post['city'], 'UTF-8') : null,
            'phone'        => $post['phone']         ?: null,
            'mobile'       => $post['mobile']        ?: null,
            'email'        => $post['email']         ?: null,
            'is_federated' => $bool('is_federated'),
            'frbb_license' => $post['frbb_license']  ?: null,
            'is_junior'    => $bool('is_junior'),
            'is_supporter' => $bool('is_supporter'),
            'is_school'    => $bool('is_school'),
            'is_active'    => $bool('is_active'),
        ]);

        return redirect()->to(base_url('admin/members/' . $id . '/edit?tab=coordonnees'))
                         ->with('success', 'Coordonnées mises à jour.');
    }

    public function updateVisibility(int $id)
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $post = $this->request->getPost();
        $bool = fn($key) => ($post[$key] ?? '0') == '1' ? 1 : 0;

        $this->model->update($id, [
            'show_birth_date' => $bool('show_birth_date'),
            'show_address'    => $bool('show_address'),
            'show_phone'      => $bool('show_phone'),
            'show_mobile'     => $bool('show_mobile'),
            'show_email'      => $bool('show_email'),
        ]);

        return redirect()->to(base_url('admin/members/' . $id . '/edit?tab=visibilite'))
                         ->with('success', 'Visibilité mise à jour.');
    }

    public function updatePhoto(int $id)
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $rules = [
            'photo' => 'permit_empty|is_image[photo]|max_size[photo,2048]|mime_in[photo,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('admin/members/' . $id . '/edit?tab=photo'))
                             ->with('errors', $this->validator->getErrors());
        }

        $data      = [];
        $photoFile = $this->request->getFile('photo');

        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $this->deletePhotoFile($member->photo);
            $data['photo'] = $this->uploadPhoto($photoFile);
        }

        if ($this->request->getPost('remove_photo') && $member->photo) {
            $this->deletePhotoFile($member->photo);
            $data['photo'] = null;
        }

        if (!empty($data)) {
            $this->model->update($id, $data);
            if ((int) session()->get('member_id') === $id) {
                session()->set('member_photo', $data['photo'] ?? null);
            }
        }

        return redirect()->to(base_url('admin/members/' . $id . '/edit?tab=photo'))
                         ->with('success', 'Photo mise à jour.');
    }

    public function update(int $id)
    {
        // Conservé pour compatibilité — non utilisé par la nouvelle interface
        return redirect()->to(base_url('admin/members/' . $id . '/edit'));
    }

    public function delete(int $id)
    {
        $member = $this->model->find($id);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $this->deletePhotoFile($member->photo);
        $this->model->delete($id);

        return redirect()->to(base_url('admin/members'))->with('success', 'Membre supprimé.');
    }

    public function loginStatus(): string
    {
        $db = \Config\Database::connect();

        $rows = $db->table('members m')
            ->select('m.id, m.last_name, m.first_name, m.email, m.is_active AS member_active,
                      ml.is_active AS login_active, ml.password_changed_at, ml.last_login')
            ->join('members_login ml', 'ml.member_id = m.id', 'left')
            ->orderBy('m.last_name', 'ASC')
            ->orderBy('m.first_name', 'ASC')
            ->get()->getResultObject();

        return view('admin/members/login_status', [
            'title'       => 'Statut de connexion des membres',
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => 'Statut connexion'],
            ],
            'rows' => $rows,
        ]);
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

    public function storeKey(int $memberId)
    {
        if (!$this->model->find($memberId)) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $keyId    = (int) $this->request->getPost('key_id');
        $keyModel = new MemberKeyModel();
        $key      = $keyModel->find($keyId);

        if (!$key || $key->member_id !== null) {
            return redirect()->back()->with('error', 'Clé invalide ou déjà attribuée.');
        }

        $keyModel->update($keyId, [
            'member_id'     => $memberId,
            'given_date'    => $this->request->getPost('given_date') ?: date('Y-m-d'),
            'returned_date' => null,
        ]);

        return redirect()->to(base_url('admin/members/' . $memberId . '/edit?tab=cles'))->with('success', 'Clé attribuée.');
    }

    public function returnKey(int $memberId, int $keyId)
    {
        (new MemberKeyModel())->update($keyId, [
            'member_id'     => null,
            'returned_date' => date('Y-m-d'),
        ]);
        return redirect()->to(base_url('admin/members/' . $memberId . '/edit?tab=cles'))->with('success', 'Clé marquée comme retournée.');
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
            'reg_nat'         => $post['reg_nat'] ?: null,
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

}
