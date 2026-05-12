<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\AdminUserModel;

class AccountController extends BaseController
{
    private function getMember(): object|null
    {
        $memberId = session()->get('admin_member_id');
        if (!$memberId) {
            return null;
        }
        return (new MemberModel())->find($memberId);
    }

    public function index(): string
    {
        $member = $this->getMember();
        $tab    = $this->request->getGet('tab') ?? 'coordonnees';

        return view('public/pages/mon_compte', [
            'title'       => 'Mon compte — RBC Disonais',
            'page_title'  => 'Mon compte',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Mon compte'],
            ],
            'member'    => $member,
            'activeTab' => $tab,
            'success'   => session()->getFlashdata('success'),
            'error'     => session()->getFlashdata('error'),
        ]);
    }

    public function saveCoordonnees()
    {
        $memberId = session()->get('admin_member_id');
        if (!$memberId) {
            return redirect()->to(base_url('mon-compte'))->with('error', 'Aucun profil membre lié à votre compte.');
        }

        $model = new MemberModel();
        $model->update($memberId, [
            'phone'       => $this->request->getPost('phone') ?: null,
            'mobile'      => $this->request->getPost('mobile') ?: null,
            'email'       => $this->request->getPost('email') ?: null,
            'address'     => $this->request->getPost('address') ?: null,
            'postal_code' => $this->request->getPost('postal_code') ?: null,
            'city'        => $this->request->getPost('city') ? mb_strtoupper($this->request->getPost('city')) : null,
        ]);

        return redirect()->to(base_url('mon-compte') . '?tab=coordonnees')
                         ->with('success', 'Coordonnées mises à jour.');
    }

    public function savePassword()
    {
        $userId = session()->get('admin_id');
        if (!$userId) {
            return redirect()->to(base_url('mon-compte'))->with('error', 'Session invalide.');
        }

        $current = $this->request->getPost('current_password');
        $new     = $this->request->getPost('new_password');
        $confirm = $this->request->getPost('new_password_confirm');

        $adminModel = new AdminUserModel();
        $user       = $adminModel->find($userId);

        if (!$user || !password_verify($current, $user->password_hash)) {
            return redirect()->to(base_url('mon-compte') . '?tab=mot-de-passe')
                             ->with('error', 'Mot de passe actuel incorrect.');
        }
        if (strlen($new) < 8) {
            return redirect()->to(base_url('mon-compte') . '?tab=mot-de-passe')
                             ->with('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
        }
        if ($new !== $confirm) {
            return redirect()->to(base_url('mon-compte') . '?tab=mot-de-passe')
                             ->with('error', 'Les deux mots de passe ne correspondent pas.');
        }

        $adminModel->update($userId, [
            'password_hash'         => password_hash($new, PASSWORD_BCRYPT),
            'password_default_hash' => null,
        ]);

        return redirect()->to(base_url('mon-compte') . '?tab=mot-de-passe')
                         ->with('success', 'Mot de passe modifié avec succès.');
    }

    public function saveConfidentialite()
    {
        $memberId = session()->get('admin_member_id');
        if (!$memberId) {
            return redirect()->to(base_url('mon-compte'))->with('error', 'Aucun profil membre lié à votre compte.');
        }

        $post = $this->request->getPost();
        (new MemberModel())->update($memberId, [
            'show_photo'      => isset($post['show_photo'])      ? 1 : 0,
            'show_phone'      => isset($post['show_phone'])      ? 1 : 0,
            'show_mobile'     => isset($post['show_mobile'])     ? 1 : 0,
            'show_email'      => isset($post['show_email'])      ? 1 : 0,
            'show_address'    => isset($post['show_address'])    ? 1 : 0,
            'show_birth_date' => isset($post['show_birth_date']) ? 1 : 0,
        ]);

        return redirect()->to(base_url('mon-compte') . '?tab=confidentialite')
                         ->with('success', 'Paramètres de confidentialité enregistrés.');
    }

    public function togglePrivacy()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $memberId = session()->get('admin_member_id');
        if (!$memberId) {
            return $this->response->setJSON(['success' => false]);
        }

        $allowed = ['show_photo', 'show_phone', 'show_mobile', 'show_email', 'show_address', 'show_birth_date'];
        $field   = $this->request->getPost('field');
        $value   = (int) $this->request->getPost('value');

        if (!in_array($field, $allowed, true)) {
            return $this->response->setJSON(['success' => false]);
        }

        $model  = new MemberModel();
        $model->update($memberId, [$field => $value]);
        $member = $model->find($memberId);

        $photoUrl = ($member->photo && $member->show_photo)
            ? base_url('uploads/members/' . $member->photo)
            : null;

        return $this->response->setJSON([
            'success'   => true,
            'photoUrl'  => $photoUrl,
            'hasPhoto'  => (bool) $member->photo,
            'showPhoto' => (bool) $member->show_photo,
            'csrf'      => csrf_hash(),
        ]);
    }

    public function uploadPhoto()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $memberId = session()->get('admin_member_id');
        if (!$memberId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Non autorisé.']);
        }

        $photoData = $this->request->getPost('photo_data');
        if (!$photoData || !str_starts_with($photoData, 'data:image/')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Données image invalides.']);
        }

        // Décoder le base64
        $parts     = explode(',', $photoData, 2);
        $imageData = base64_decode($parts[1] ?? '');
        if (!$imageData) {
            return $this->response->setJSON(['success' => false, 'error' => 'Décodage impossible.']);
        }

        $model  = new MemberModel();
        $member = $model->find($memberId);

        // Supprimer l'ancienne photo
        if ($member->photo) {
            $old = FCPATH . 'uploads/members/' . $member->photo;
            if (file_exists($old)) {
                unlink($old);
            }
        }

        $newName = 'member_' . $memberId . '_' . time() . '.jpg';
        file_put_contents(FCPATH . 'uploads/members/' . $newName, $imageData);

        $model->update($memberId, ['photo' => $newName]);

        return $this->response->setJSON([
            'success'  => true,
            'photoUrl' => base_url('uploads/members/' . $newName),
            'csrf'     => csrf_hash(),
        ]);
    }

    public function deletePhoto()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $memberId = session()->get('admin_member_id');
        if (!$memberId) {
            return $this->response->setJSON(['success' => false]);
        }

        $model  = new MemberModel();
        $member = $model->find($memberId);

        if ($member->photo) {
            $path = FCPATH . 'uploads/members/' . $member->photo;
            if (file_exists($path)) {
                unlink($path);
            }
            $model->update($memberId, ['photo' => null]);
        }

        return $this->response->setJSON(['success' => true, 'csrf' => csrf_hash()]);
    }
}
