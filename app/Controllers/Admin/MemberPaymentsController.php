<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MemberPaymentModel;

class MemberPaymentsController extends BaseController
{
    private MemberModel        $memberModel;
    private MemberPaymentModel $paymentModel;

    public function __construct()
    {
        $this->memberModel  = new MemberModel();
        $this->paymentModel = new MemberPaymentModel();
    }

    public function index(int $memberId): string
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        return view('admin/member_payments/index', [
            'title'       => 'Cotisations — ' . esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
                ['title' => 'Cotisations'],
            ],
            'member'   => $member,
            'payments' => $this->paymentModel->getForMember($memberId),
        ]);
    }

    public function create(int $memberId): string
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        return view('admin/member_payments/form', [
            'title'       => 'Nouvelle année — ' . esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
                ['title' => 'Cotisations', 'url' => base_url("admin/members/{$memberId}/payments")],
                ['title' => 'Nouvelle année'],
            ],
            'member'  => $member,
            'payment' => null,
        ]);
    }

    public function edit(int $memberId, int $paymentId): string
    {
        $member  = $this->memberModel->find($memberId);
        $payment = $this->paymentModel->find($paymentId);
        if (!$member || !$payment || $payment->member_id != $memberId) {
            return redirect()->to(base_url("admin/members/{$memberId}/payments"))->with('error', 'Enregistrement introuvable.');
        }

        return view('admin/member_payments/form', [
            'title'       => "Cotisations {$payment->year} — " . esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                ['title' => 'Membres', 'url' => base_url('admin/members')],
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
                ['title' => 'Cotisations', 'url' => base_url("admin/members/{$memberId}/payments")],
                ['title' => "Année {$payment->year}"],
            ],
            'member'  => $member,
            'payment' => $payment,
        ]);
    }

    public function store(int $memberId)
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $year = (int) $this->request->getPost('year');
        if ($year < 2000 || $year > 2100) {
            return redirect()->back()->withInput()->with('errors', ['year' => 'Année invalide.']);
        }

        $season = $year . '-' . ($year + 1);

        // Vérifier unicité member + year
        $existing = $this->paymentModel->where('member_id', $memberId)->where('year', $year)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('errors', ['year' => "Une ligne existe déjà pour la saison {$season}."]);
        }

        $this->paymentModel->insert($this->collectData($memberId, $year));

        return redirect()->to(base_url("admin/members/{$memberId}/payments"))
                         ->with('success', "Saison {$season} ajoutée.");
    }

    public function update(int $memberId, int $paymentId)
    {
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment || $payment->member_id != $memberId) {
            return redirect()->to(base_url("admin/members/{$memberId}/payments"))->with('error', 'Enregistrement introuvable.');
        }

        $this->paymentModel->update($paymentId, $this->collectData($memberId, (int) $payment->year));

        $season = $payment->year . '-' . ($payment->year + 1);
        return redirect()->to(base_url("admin/members/{$memberId}/payments"))
                         ->with('success', "Saison {$season} mise à jour.");
    }

    public function delete(int $memberId, int $paymentId)
    {
        $payment = $this->paymentModel->find($paymentId);
        if (!$payment || $payment->member_id != $memberId) {
            return redirect()->to(base_url("admin/members/{$memberId}/payments"))->with('error', 'Enregistrement introuvable.');
        }

        $this->paymentModel->delete($paymentId);

        $season = $payment->year . '-' . ($payment->year + 1);
        return redirect()->to(base_url("admin/members/{$memberId}/payments"))
                         ->with('success', "Saison {$season} supprimée.");
    }

    // ----------------------------------------------------------------

    private function collectData(int $memberId, int $year): array
    {
        $post = $this->request->getPost();

        // Les champs hidden envoient toujours "0", donc on vérifie la valeur (== '1'), pas l'existence
        $f1Choice = ($post['forfait_f1_choice'] ?? '0') == '1' ? 1 : 0;
        $f2Choice = ($post['forfait_f2_choice'] ?? '0') == '1' ? 1 : 0;
        $rbcdPaid = ($post['rbcd_paid']         ?? '0') == '1' ? 1 : 0;
        $frbbPaid = ($post['frbb_paid']          ?? '0') == '1' ? 1 : 0;
        $f1Paid   = ($post['forfait_f1_paid']    ?? '0') == '1' ? 1 : 0;
        $f2Paid   = ($post['forfait_f2_paid']    ?? '0') == '1' ? 1 : 0;

        return [
            'member_id'            => $memberId,
            'year'                 => $year,
            'rbcd_paid'            => $rbcdPaid,
            'rbcd_paid_date'       => $rbcdPaid && !empty($post['rbcd_paid_date']) ? $post['rbcd_paid_date'] : null,
            'frbb_paid'            => $frbbPaid,
            'frbb_paid_date'       => $frbbPaid && !empty($post['frbb_paid_date']) ? $post['frbb_paid_date'] : null,
            'forfait_f1_choice'    => $f1Choice,
            'forfait_f1_paid'      => $f1Choice && $f1Paid ? 1 : 0,
            'forfait_f1_paid_date' => $f1Choice && $f1Paid && !empty($post['forfait_f1_paid_date']) ? $post['forfait_f1_paid_date'] : null,
            'forfait_f2_choice'    => $f2Choice,
            'forfait_f2_paid'      => $f2Choice && $f2Paid ? 1 : 0,
            'forfait_f2_paid_date' => $f2Choice && $f2Paid && !empty($post['forfait_f2_paid_date']) ? $post['forfait_f2_paid_date'] : null,
        ];
    }
}
