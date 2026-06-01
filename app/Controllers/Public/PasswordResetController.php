<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\MemberLoginModel;

class PasswordResetController extends BaseController
{
    public function request(): string
    {
        return view('public/auth/forgot_password', [
            'title'       => 'Mot de passe oublié — RBC Disonais',
            'page_title'  => 'Première connexion / Mot de passe oublié',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Connexion', 'url' => base_url('connexion')],
                ['label' => 'Mot de passe oublié'],
            ],
        ]);
    }

    public function sendLink()
    {
        $email = trim($this->request->getPost('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Adresse e-mail invalide.');
        }

        $db     = \Config\Database::connect();
        $member = $db->table('members')->where('email', $email)->get()->getRowObject();

        // Réponse générique si email inconnu (sécurité)
        if (!$member) {
            return redirect()->to(base_url('connexion/mot-de-passe-oublie'))
                             ->with('success', 'Si cette adresse est connue, vous recevrez un email dans quelques minutes. Vérifiez aussi vos spams.');
        }

        $loginModel = new MemberLoginModel();
        $loginRow   = $loginModel->where('member_id', $member->id)->first();

        // Créer un enregistrement stub si le membre n'en a pas encore
        if (!$loginRow) {
            $loginModel->insert([
                'member_id'    => $member->id,
                'password_hash'=> password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'is_active'    => 0,
            ]);
            $loginRow = $loginModel->where('member_id', $member->id)->first();
        }

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+5 days'));
        $isFirst = !$loginRow->is_active;

        $loginModel->update($loginRow->id, [
            'reset_code'            => $token,
            'reset_code_expires_at' => $expires,
        ]);

        $resetUrl = base_url('connexion/reinitialiser/' . $token);

        $emailLib = \Config\Services::email();
        $emailLib->setTo($email);
        $emailLib->setSubject($isFirst
            ? 'RBC Disonais — Créez votre mot de passe'
            : 'RBC Disonais — Réinitialisation de mot de passe');
        $emailLib->setMessage(view('emails/password_reset', [
            'member'   => $member,
            'resetUrl' => $resetUrl,
            'isFirst'  => $isFirst,
        ]));

        try {
            $emailLib->send();
        } catch (\Throwable $e) {
            log_message('error', 'Email reset failed: ' . $e->getMessage());
            return redirect()->to(base_url('connexion/mot-de-passe-oublie'))
                             ->with('error', 'Impossible d\'envoyer l\'email. Contactez l\'administrateur.');
        }

        return redirect()->to(base_url('connexion/mot-de-passe-oublie'))
                         ->with('success', 'Email envoyé ! Vérifiez votre boîte mail (et vos spams). Le lien est valable 5 jours.');
    }

    public function showForm(string $token): mixed
    {
        $loginModel = new MemberLoginModel();
        $row        = $loginModel->findByToken($token);

        if (!$row) {
            return redirect()->to(base_url('connexion/mot-de-passe-oublie'))
                             ->with('error', 'Ce lien est invalide ou a expiré. Faites une nouvelle demande.');
        }

        return view('public/auth/reset_password', [
            'title'       => 'Choisir un mot de passe — RBC Disonais',
            'page_title'  => 'Choisir votre mot de passe',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Connexion', 'url' => base_url('connexion')],
                ['label' => 'Nouveau mot de passe'],
            ],
            'token'  => $token,
            'member' => $row,
        ]);
    }

    public function savePassword(string $token)
    {
        $loginModel = new MemberLoginModel();
        $row        = $loginModel->findByToken($token);

        if (!$row) {
            return redirect()->to(base_url('connexion/mot-de-passe-oublie'))
                             ->with('error', 'Ce lien est invalide ou a expiré. Faites une nouvelle demande.');
        }

        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('password_confirm');

        if (strlen($password) < 8) {
            return redirect()->back()->with('form_error', 'Le mot de passe doit contenir au moins 8 caractères.');
        }

        if ($password !== $confirm) {
            return redirect()->back()->with('form_error', 'Les deux mots de passe ne correspondent pas.');
        }

        $loginModel->update($row->id, [
            'password_hash'         => password_hash($password, PASSWORD_BCRYPT),
            'is_active'             => 1,
            'reset_code'            => null,
            'reset_code_expires_at' => null,
            'login_attempts'        => 0,
            'locked_until'          => null,
        ]);

        return redirect()->to(base_url('connexion'))
                         ->with('success', 'Mot de passe enregistré ! Vous pouvez maintenant vous connecter.');
    }
}
