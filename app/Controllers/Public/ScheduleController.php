<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\ScheduleEncounterModel;
use App\Models\ScheduleArbitrageModel;
use App\Models\ScheduleBarDutyModel;

class ScheduleController extends BaseController
{
    private ScheduleEncounterModel $encounters;
    private ScheduleArbitrageModel $arbitrage;
    private ScheduleBarDutyModel   $barDuties;

    public function __construct()
    {
        $this->encounters = new ScheduleEncounterModel();
        $this->arbitrage  = new ScheduleArbitrageModel();
        $this->barDuties  = new ScheduleBarDutyModel();
    }

    public function week(?string $week = null, ?string $year = null): string
    {
        $now  = new \DateTime();
        $week = $week ? (int) $week : (int) $now->format('W');
        $year = $year ? (int) $year : (int) $now->format('o');

        $weekDates    = $this->getWeekDates($week, $year);
        $encounters   = $this->encounters->getWeek($week, $year);
        $encounterIds = array_map(fn($e) => $e->id, $encounters);

        $playersByEncounter   = $this->getPlayersByEncounter($encounterIds);
        $arbitrageByEncounter = $this->arbitrage->getForEncounters($encounterIds);
        $barByDate            = $this->barDuties->getForDates($weekDates);

        $currentUser = (int) session()->get('admin_id');
        $currentMemberId = 0;
        if ($currentUser) {
            $adminRow = \Config\Database::connect()
                ->table('admin_users')->select('member_id')
                ->where('id', $currentUser)->get()->getRowObject();
            $currentMemberId = $adminRow ? (int) $adminRow->member_id : 0;
        }

        $byDate        = [];
        $activeDates   = [];
        $homeDateFlags = [];
        foreach ($encounters as $enc) {
            $enc->players       = $playersByEncounter[$enc->id] ?? [];
            $enc->arbitrageRows = $arbitrageByEncounter[$enc->id] ?? [];
            // Current user's own signup for this encounter (null if not signed up)
            $enc->myArb = null;
            if ($currentUser) {
                foreach ($enc->arbitrageRows as $arb) {
                    if ($arb->admin_user_id == $currentUser) {
                        $enc->myArb = $arb;
                        break;
                    }
                }
            }
            $byDate[$enc->match_date][] = $enc;
            $activeDates[$enc->match_date] = true;
            if ($enc->is_home) {
                $homeDateFlags[$enc->match_date] = true;
            }
        }
        foreach ($barByDate as $date => $_) {
            $activeDates[$date] = true;
        }

        $nav = $this->getPrevNextWeek($week, $year);

        return view('public/schedule/week', [
            'title'       => "Tableau — Semaine {$week}",
            'week'        => $week,
            'year'        => $year,
            'weekDates'   => $weekDates,
            'byDate'      => $byDate,
            'barByDate'   => $barByDate,
            'activeDates'   => $activeDates,
            'homeDateFlags' => $homeDateFlags,
            'prev'          => $nav['prev'],
            'next'          => $nav['next'],
            'currentUser'     => $currentUser,
            'currentMemberId' => $currentMemberId,
            'isLogged'        => (bool) session()->get('admin_logged_in'),
        ]);
    }

    public function signupArbitrage(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $adminUserId = (int) session()->get('admin_id');
        $encounter   = $this->encounters->find($encounterId);
        if (!$encounter) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rencontre introuvable.']);
        }

        // Prevent double signup
        if ($this->arbitrage->getUserSignup($encounterId, $adminUserId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vous êtes déjà inscrit pour cette rencontre.']);
        }

        $round = 0;
        if ($encounter->encounter_type === 'finale') {
            $round = max(1, min(255, (int) $this->request->getPost('round')));
        } else {
            // Normal match — 1 referee max
            $existing = $this->arbitrage->where('encounter_id', $encounterId)->first();
            if ($existing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Un arbitre est déjà inscrit.']);
            }
        }

        $arbId = $this->arbitrage->insert([
            'encounter_id'    => $encounterId,
            'round'           => $round,
            'admin_user_id'   => $adminUserId,
            'assignment_type' => 'volunteer',
            'confirmed'       => 1,
            'confirmed_at'    => date('Y-m-d H:i:s'),
        ]);

        $arb = $this->arbitrage->db
            ->table('schedule_arbitrage sa')
            ->select('sa.*, au.last_name, au.first_name')
            ->join('admin_users au', 'au.id = sa.admin_user_id')
            ->where('sa.id', $arbId)
            ->get()->getRowObject();

        return $this->response->setJSON([
            'success'     => true,
            'arb_id'      => $arbId,
            'name'        => $arb->last_name . ' ' . mb_substr($arb->first_name, 0, 1) . '.',
            'round'       => $round,
        ]);
    }

    public function cancelArbitrage(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $adminUserId = (int) session()->get('admin_id');
        $existing    = $this->arbitrage->getUserSignup($encounterId, $adminUserId);

        if (!$existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Inscription non trouvée.']);
        }

        if ($existing->assignment_type === 'designated') {
            return $this->response->setJSON(['success' => false, 'message' => 'Vous avez été convoqué par le DS — contactez-le pour annuler.']);
        }

        $this->arbitrage->delete($existing->id);

        return $this->response->setJSON(['success' => true]);
    }

    public function confirmArbitrage(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $adminUserId = (int) session()->get('admin_id');
        $existing    = $this->arbitrage->getUserSignup($encounterId, $adminUserId);

        if (!$existing || $existing->assignment_type !== 'designated') {
            return $this->response->setJSON(['success' => false, 'message' => 'Convocation non trouvée.']);
        }

        $this->arbitrage->update($existing->id, [
            'confirmed'    => 1,
            'confirmed_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function signupBar()
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $adminUserId = (int) session()->get('admin_id');
        $date        = $this->request->getPost('date');
        $period      = $this->request->getPost('period');

        if (!$date || !in_array($period, ['am', 'soir'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Données invalides.']);
        }

        if ($this->barDuties->isSlotTaken($date, $period)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ce créneau est déjà pris.']);
        }

        $id = $this->barDuties->insert([
            'duty_date'     => $date,
            'period'        => $period,
            'admin_user_id' => $adminUserId,
        ]);

        $duty = $this->barDuties->db
            ->table('schedule_bar_duties bd')
            ->select('bd.*, au.last_name, au.first_name')
            ->join('admin_users au', 'au.id = bd.admin_user_id')
            ->where('bd.id', $id)
            ->get()->getRowObject();

        return $this->response->setJSON([
            'success' => true,
            'id'      => $id,
            'name'    => $duty->last_name . ' ' . mb_substr($duty->first_name, 0, 1) . '.',
        ]);
    }

    public function cancelBar(int $id)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $adminUserId = (int) session()->get('admin_id');
        $duty        = $this->barDuties->find($id);

        if (!$duty || $duty->admin_user_id != $adminUserId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Inscription non trouvée.']);
        }

        $this->barDuties->delete($id);

        return $this->response->setJSON(['success' => true]);
    }

    private function getWeekDates(int $week, int $year): array
    {
        $dates = [];
        for ($day = 1; $day <= 7; $day++) {
            $dates[] = (new \DateTime())->setISODate($year, $week, $day)->format('Y-m-d');
        }
        return $dates;
    }

    private function getPrevNextWeek(int $week, int $year): array
    {
        $current  = (new \DateTime())->setISODate($year, $week, 1);
        $prevDate = (clone $current)->modify('-1 week');
        $nextDate = (clone $current)->modify('+1 week');

        return [
            'prev' => ['week' => (int) $prevDate->format('W'), 'year' => (int) $prevDate->format('o')],
            'next' => ['week' => (int) $nextDate->format('W'), 'year' => (int) $nextDate->format('o')],
        ];
    }

    private function getPlayersByEncounter(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = \Config\Database::connect()
            ->table('schedule_encounter_players sep')
            ->select('sep.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = sep.member_id', 'left')
            ->whereIn('sep.encounter_id', $ids)
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->encounter_id][] = $row;
        }
        return $indexed;
    }
}
