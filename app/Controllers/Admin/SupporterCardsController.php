<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\SupporterCardModel;
use App\Models\SupporterCardSessionModel;
use App\Models\TreasurySettingModel;

class SupporterCardsController extends BaseController
{
    private MemberModel               $memberModel;
    private SupporterCardModel        $cardModel;
    private SupporterCardSessionModel $sessionModel;

    public function __construct()
    {
        $this->memberModel  = new MemberModel();
        $this->cardModel    = new SupporterCardModel();
        $this->sessionModel = new SupporterCardSessionModel();
    }

    public function store(int $memberId)
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }
        if (!$member->is_supporter) {
            return $this->back($memberId, 'error', 'Les cartes sont réservées aux membres sympathisants.');
        }

        $current = array_filter($this->cardModel->getForMember($memberId), fn($c) => $c->status === 'active');
        if ($current) {
            return $this->back($memberId, 'error', 'Ce membre a encore une carte active : elle doit être complète ou expirée avant d\'en créer une nouvelle.');
        }

        $data = $this->collectCard();
        if (is_string($data)) {
            return $this->back($memberId, 'error', $data);
        }

        $settings = (new TreasurySettingModel())->first();
        $this->cardModel->insert($data + [
            'member_id' => $memberId,
            'amount'    => (float) ($settings->supporter_card_price ?? 30),
        ]);

        return $this->back($memberId, 'success', 'Nouvelle carte sympathisant enregistrée.');
    }

    public function update(int $memberId, int $cardId)
    {
        $card = $this->findCard($memberId, $cardId);
        if (!$card) {
            return $this->back($memberId, 'error', 'Carte introuvable.');
        }

        $data = $this->collectCard();
        if (is_string($data)) {
            return $this->back($memberId, 'error', $data);
        }

        $outside = $this->sessionModel->where('card_id', $cardId)
            ->groupStart()
                ->where('session_date <', $data['purchase_date'])
                ->orWhere('session_date >', $data['expiry_date'])
            ->groupEnd()
            ->countAllResults();
        if ($outside > 0) {
            return $this->back($memberId, 'error', 'Des séances déjà encodées tombent en dehors de ces dates.');
        }

        $this->cardModel->update($cardId, $data);

        return $this->back($memberId, 'success', 'Carte sympathisant mise à jour.');
    }

    public function delete(int $memberId, int $cardId)
    {
        if (!$this->findCard($memberId, $cardId)) {
            return $this->back($memberId, 'error', 'Carte introuvable.');
        }
        $this->cardModel->delete($cardId);

        return $this->back($memberId, 'success', 'Carte sympathisant supprimée.');
    }

    public function addSession(int $memberId, int $cardId)
    {
        $card = $this->findCard($memberId, $cardId);
        if (!$card) {
            return $this->back($memberId, 'error', 'Carte introuvable.');
        }

        $date = (string) $this->request->getPost('session_date');
        if (!$this->isDate($date)) {
            return $this->back($memberId, 'error', 'Date de séance invalide.');
        }
        if ($date < $card->purchase_date || $date > $card->expiry_date) {
            return $this->back($memberId, 'error', 'La séance doit tomber entre le '
                . date('d/m/Y', strtotime($card->purchase_date)) . ' et le '
                . date('d/m/Y', strtotime($card->expiry_date)) . '.');
        }

        $used = $this->sessionModel->where('card_id', $cardId)->countAllResults();
        if ($used >= SupporterCardModel::SESSIONS_PER_CARD) {
            return $this->back($memberId, 'error', 'Cette carte a déjà ses ' . SupporterCardModel::SESSIONS_PER_CARD . ' séances.');
        }

        $this->sessionModel->insert(['card_id' => $cardId, 'session_date' => $date]);

        return $this->back($memberId, 'success', 'Séance du ' . date('d/m/Y', strtotime($date)) . ' ajoutée ('
            . ($used + 1) . '/' . SupporterCardModel::SESSIONS_PER_CARD . ').');
    }

    public function deleteSession(int $memberId, int $cardId, int $sessionId)
    {
        $session = $this->findCard($memberId, $cardId)
            ? $this->sessionModel->where('card_id', $cardId)->find($sessionId)
            : null;
        if (!$session) {
            return $this->back($memberId, 'error', 'Séance introuvable.');
        }
        $this->sessionModel->delete($sessionId);

        return $this->back($memberId, 'success', 'Séance supprimée.');
    }

    // ----------------------------------------------------------------

    private function findCard(int $memberId, int $cardId): ?object
    {
        return $this->cardModel->where('member_id', $memberId)->find($cardId);
    }

    /**
     * @return array|string données de la carte, ou message d'erreur
     */
    private function collectCard(): array|string
    {
        $post     = $this->request->getPost();
        $purchase = (string) ($post['purchase_date'] ?? '');
        if (!$this->isDate($purchase)) {
            return 'Date d\'achat invalide.';
        }

        $expiry = (string) ($post['expiry_date'] ?? '');
        if ($expiry === '') {
            $expiry = SupporterCardModel::defaultExpiry($purchase);
        } elseif (!$this->isDate($expiry) || $expiry < $purchase) {
            return 'La date d\'expiration doit suivre la date d\'achat.';
        }

        return [
            'card_number'   => trim((string) ($post['card_number'] ?? '')) ?: null,
            'purchase_date' => $purchase,
            'expiry_date'   => $expiry,
            'notes'         => trim((string) ($post['notes'] ?? '')) ?: null,
        ];
    }

    private function isDate(string $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    private function back(int $memberId, string $type, string $message)
    {
        $url = $this->request->getPost('_back') === 'payments'
            ? base_url("admin/members/{$memberId}/payments")
            : base_url("admin/members/{$memberId}/edit?tab=cotisations");

        return redirect()->to($url)->with($type, $message);
    }
}
