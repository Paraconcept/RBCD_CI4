<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\ScheduleEncounterModel;
use App\Models\ScheduleArbitrageModel;
use App\Models\ScheduleBarDutyModel;
use App\Models\ScheduleEventModel;

class ScheduleController extends BaseController
{
    private ScheduleEncounterModel $encounters;
    private ScheduleArbitrageModel $arbitrage;
    private ScheduleBarDutyModel   $barDuties;
    private ScheduleEventModel     $events;

    public function __construct()
    {
        $this->encounters = new ScheduleEncounterModel();
        $this->arbitrage  = new ScheduleArbitrageModel();
        $this->barDuties  = new ScheduleBarDutyModel();
        $this->events     = new ScheduleEventModel();
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
        $eventsByDate         = $this->events->getForDates($weekDates);

        $currentMemberId = (int) session()->get('member_id');

        $byDate        = [];
        $activeDates   = [];
        $homeDateFlags = [];
        foreach ($encounters as $enc) {
            $enc->players       = $playersByEncounter[$enc->id] ?? [];
            $enc->arbitrageRows = $arbitrageByEncounter[$enc->id] ?? [];
            // Current member's own signup for this encounter (null if not signed up)
            $enc->myArb = null;
            if ($currentMemberId) {
                foreach ($enc->arbitrageRows as $arb) {
                    if ((int) $arb->member_id === $currentMemberId && $arb->assignment_type === 'volunteer') {
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
        foreach ($eventsByDate as $date => $_) {
            $activeDates[$date] = true;
        }

        $nav = $this->getPrevNextWeek($week, $year);

        return view('public/schedule/week', [
            'title'       => "Tableau — Semaine {$week}",
            'page_title'  => 'Au Tableau',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Au Tableau'],
            ],
            'week'        => $week,
            'year'        => $year,
            'weekDates'   => $weekDates,
            'byDate'      => $byDate,
            'barByDate'   => $barByDate,
            'activeDates'   => $activeDates,
            'homeDateFlags' => $homeDateFlags,
            'prev'          => $nav['prev'],
            'next'          => $nav['next'],
            'currentMemberId' => $currentMemberId,
            'isLogged'        => (bool) session()->get('member_logged_in'),
            'eventsByDate'    => $eventsByDate,
            'eventColors'     => ScheduleEventModel::$colors,
        ]);
    }

    public function signupArbitrage(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $memberId  = (int) session()->get('member_id');
        $encounter = $this->encounters->find($encounterId);
        if (!$encounter) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rencontre introuvable.']);
        }

        if ($this->getMyArbitrageRow($encounterId, $memberId)) {
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
            'member_id'       => $memberId,
            'assignment_type' => 'volunteer',
            'confirmed'       => 1,
            'confirmed_at'    => date('Y-m-d H:i:s'),
        ]);

        $arb = $this->arbitrage->db
            ->table('schedule_arbitrage sa')
            ->select('sa.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = sa.member_id')
            ->where('sa.id', $arbId)
            ->get()->getRowObject();

        return $this->response->setJSON([
            'success' => true,
            'arb_id'  => $arbId,
            'name'    => $arb->last_name . ' ' . member_initials($arb->first_name),
            'round'   => $round,
        ]);
    }

    public function cancelArbitrage(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $memberId = (int) session()->get('member_id');
        $existing = $this->getMyArbitrageRow($encounterId, $memberId);

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

        $memberId = (int) session()->get('member_id');
        $existing = $this->getMyArbitrageRow($encounterId, $memberId);

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

        $memberId = (int) session()->get('member_id');
        $date     = $this->request->getPost('date');
        $period   = $this->request->getPost('period');

        if (!$date || !in_array($period, ['am', 'soir'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Données invalides.']);
        }

        if ($this->barDuties->isSlotTaken($date, $period)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ce créneau est déjà pris.']);
        }

        $id = $this->barDuties->insert([
            'duty_date' => $date,
            'period'    => $period,
            'member_id' => $memberId,
        ]);

        $duty = $this->barDuties->db
            ->table('schedule_bar_duties bd')
            ->select('bd.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = bd.member_id')
            ->where('bd.id', $id)
            ->get()->getRowObject();

        return $this->response->setJSON([
            'success' => true,
            'id'      => $id,
            'name'    => $duty->last_name . ' ' . member_initials($duty->first_name),
        ]);
    }

    public function cancelBar(int $id)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $memberId = (int) session()->get('member_id');
        $duty     = $this->barDuties->find($id);

        if (!$duty || (int) $duty->member_id !== $memberId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Inscription non trouvée.']);
        }

        $this->barDuties->delete($id);

        return $this->response->setJSON(['success' => true]);
    }

    private function getMyArbitrageRow(int $encounterId, int $memberId): ?object
    {
        return $this->arbitrage->getUserSignup($encounterId, $memberId)
            ?? $this->arbitrage->where('encounter_id', $encounterId)
                               ->where('member_id', $memberId)
                               ->where('assignment_type', 'designated')
                               ->first();
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
