<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberClubFeeModel;
use App\Models\MemberModel;
use App\Models\MemberPaymentModel;
use App\Models\SupporterCardModel;
use App\Models\TreasurySettingModel;

class MemberPaymentsController extends BaseController
{
    private MemberModel        $memberModel;
    private MemberPaymentModel $paymentModel;
    private MemberClubFeeModel $clubFeeModel;

    public function __construct()
    {
        $this->memberModel  = new MemberModel();
        $this->paymentModel = new MemberPaymentModel();
        $this->clubFeeModel = new MemberClubFeeModel();
    }

    public function index(int $memberId)
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }

        $ref = $this->request->getGet('ref') ?? '';

        return view('admin/member_payments/index', [
            'title'       => 'Cotisations — ' . esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                $this->firstCrumb($ref),
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
                ['title' => 'Cotisations'],
            ],
            'member'         => $member,
            'clubFees'       => $this->clubFeeModel->getForMember($memberId),
            'payments'       => $this->paymentModel->getForMember($memberId),
            'supporterCards' => (new SupporterCardModel())->getForMember($memberId),
            'ref'            => $ref,
        ]);
    }

    public function edit(int $memberId, int $year)
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }
        if ($year < 2000 || $year > 2100) {
            return redirect()->to(base_url("admin/members/{$memberId}/payments"))->with('error', 'Année invalide.');
        }

        $ref    = $this->request->getGet('ref') ?? '';
        $season = MemberClubFeeModel::frbbSeasonFor($year);

        return view('admin/member_payments/form', [
            'title'       => "Cotisations {$year} — " . esc($member->first_name . ' ' . $member->last_name),
            'breadcrumbs' => [
                $this->firstCrumb($ref),
                ['title' => esc($member->first_name . ' ' . $member->last_name)],
                ['title' => 'Cotisations', 'url' => base_url("admin/members/{$memberId}/payments")],
                ['title' => "Année {$year}"],
            ],
            'member'         => $member,
            'year'           => $year,
            'season'         => $season,
            'clubFee'        => $this->clubFeeModel->findForYear($memberId, $year),
            'payment'        => $this->paymentModel->findForSeason($memberId, $season),
            'ref'            => $ref,
            'supporterCards' => (new SupporterCardModel())->getForMember($memberId),
            'cardsBackYear'  => $year,
        ]);
    }

    public function save(int $memberId, int $year)
    {
        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return redirect()->to(base_url('admin/members'))->with('error', 'Membre introuvable.');
        }
        if ($year < 2000 || $year > 2100) {
            return redirect()->to(base_url("admin/members/{$memberId}/payments"))->with('error', 'Année invalide.');
        }

        $post    = $this->request->getPost();
        $on      = fn(string $f) => ($post[$f] ?? '0') == '1' ? 1 : 0;
        $dateFor = fn(bool $paid, string $f) => $paid && !empty($post[$f]) ? $post[$f] : null;

        $clubFee  = $this->clubFeeModel->findForYear($memberId, $year);
        $settings = (new TreasurySettingModel())->first();
        $semester = (float) ($settings->semester_cotisation ?? 30);

        $club = ['member_id' => $memberId, 'year' => $year];
        foreach (['h1', 'h2'] as $h) {
            $paid = $on("rbcd_{$h}_paid");
            // Montant figé au moment du paiement : un changement de tarif ne réécrit pas le passé
            $wasPaid = $clubFee && $clubFee->{"rbcd_{$h}_paid"};
            $club["rbcd_{$h}_paid"]      = $paid;
            $club["rbcd_{$h}_paid_date"] = $dateFor((bool) $paid, "rbcd_{$h}_paid_date");
            $club["rbcd_{$h}_amount"]    = $paid ? ($wasPaid ? $clubFee->{"rbcd_{$h}_amount"} : $semester) : null;

            $choice = $on("forfait_{$h}_choice");
            $fPaid  = $choice && $on("forfait_{$h}_paid");
            $club["forfait_{$h}_choice"]    = $choice;
            $club["forfait_{$h}_paid"]      = $fPaid ? 1 : 0;
            $club["forfait_{$h}_paid_date"] = $dateFor($fPaid, "forfait_{$h}_paid_date");
        }

        if ($clubFee) {
            $this->clubFeeModel->update($clubFee->id, $club);
        } else {
            $this->clubFeeModel->insert($club);
        }

        $season   = MemberClubFeeModel::frbbSeasonFor($year);
        $payment  = $this->paymentModel->findForSeason($memberId, $season);
        $frbbPaid = $on('frbb_paid');
        $frbb     = [
            'member_id'      => $memberId,
            'year'           => $season,
            'frbb_paid'      => $frbbPaid,
            'frbb_paid_date' => $dateFor((bool) $frbbPaid, 'frbb_paid_date'),
        ];
        if ($payment) {
            $this->paymentModel->update($payment->id, $frbb);
        } elseif ($frbbPaid || $member->is_federated) {
            $this->paymentModel->insert($frbb);
        }

        return redirect()->to($this->backUrl($memberId, $year))
                         ->with('success', "Cotisations {$year} mises à jour.");
    }

    public function deleteClub(int $memberId, int $year)
    {
        $clubFee = $this->clubFeeModel->findForYear($memberId, $year);
        if (!$clubFee) {
            return redirect()->to($this->backUrl($memberId, $year))->with('error', 'Enregistrement introuvable.');
        }
        $this->clubFeeModel->delete($clubFee->id);

        return redirect()->to($this->backUrl($memberId, $year))
                         ->with('success', "Cotisations club {$year} supprimées.");
    }

    public function deleteSeason(int $memberId, int $seasonYear)
    {
        $payment = $this->paymentModel->findForSeason($memberId, $seasonYear);
        if (!$payment) {
            return redirect()->to($this->backUrl($memberId, $seasonYear))->with('error', 'Enregistrement introuvable.');
        }
        $this->paymentModel->delete($payment->id);

        return redirect()->to($this->backUrl($memberId, $seasonYear))
                         ->with('success', 'Saison FRBB ' . $seasonYear . '-' . ($seasonYear + 1) . ' supprimée.');
    }

    // ----------------------------------------------------------------

    private function firstCrumb(string $ref): array
    {
        return $ref === 'treasury'
            ? ['title' => 'Trésorerie', 'url' => base_url('admin/treasury')]
            : ['title' => 'Membres',    'url' => base_url('admin/members')];
    }

    private function backUrl(int $memberId, int $year): string
    {
        return match ($this->request->getGet('ref') ?? $this->request->getPost('_back') ?? '') {
            'treasury'    => base_url("admin/treasury?year={$year}"),
            'member_edit' => base_url("admin/members/{$memberId}/edit?tab=cotisations"),
            default       => base_url("admin/members/{$memberId}/payments"),
        };
    }
}
