<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\ScheduleEncounterModel;
use App\Models\ScheduleArbitrageModel;
use App\Models\ScheduleBarDutyModel;
use App\Models\ScheduleEventModel;
use App\Models\ScheduleMarqueurModel;
use App\Models\MemberModel;

class ScheduleController extends BaseController
{
    private ScheduleEncounterModel $encounters;
    private ScheduleArbitrageModel $arbitrage;
    private ScheduleBarDutyModel   $barDuties;
    private ScheduleEventModel     $events;
    private ScheduleMarqueurModel  $marqueurs;

    public function __construct()
    {
        $this->encounters = new ScheduleEncounterModel();
        $this->arbitrage  = new ScheduleArbitrageModel();
        $this->barDuties  = new ScheduleBarDutyModel();
        $this->events     = new ScheduleEventModel();
        $this->marqueurs  = new ScheduleMarqueurModel();
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
        $marqueursByEncounter = $this->marqueurs->getForEncounters($encounterIds);
        $barByDate            = $this->barDuties->getForDates($weekDates);
        $eventsByDate         = $this->events->getForDates($weekDates);

        $currentMemberId = (int) session()->get('member_id');

        $byDate        = [];
        $activeDates   = [];
        $homeDateFlags = [];
        foreach ($encounters as $enc) {
            $enc->players        = $playersByEncounter[$enc->id] ?? [];
            $enc->arbitrageRows  = $arbitrageByEncounter[$enc->id] ?? [];
            $enc->marqueurRows   = $marqueursByEncounter[$enc->id] ?? [];
            // Current member's own signup for this encounter (null if not signed up)
            $enc->myArb      = null;
            $enc->myMarqueur = null;
            if ($currentMemberId) {
                foreach ($enc->arbitrageRows as $arb) {
                    if ((int) $arb->member_id === $currentMemberId && $arb->assignment_type === 'volunteer') {
                        $enc->myArb = $arb;
                        break;
                    }
                }
                $enc->myMarqueur = $this->marqueurs->getUserSignup($enc->id, $currentMemberId);
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

        $this->sendArbitrageConfirmation($memberId, $encounter, $round);

        return $this->response->setJSON([
            'success'      => true,
            'arb_id'       => $arbId,
            'name'         => $arb->last_name . ' ' . member_initials($arb->first_name),
            'round'        => $round,
            'match_date'   => $encounter->match_date,
            'match_time'   => $encounter->match_time,
            'competition'  => $encounter->competition ?? '',
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

        $this->sendBarConfirmation($memberId, $date, $period);

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

    public function signupMarqueur(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $memberId  = (int) session()->get('member_id');
        $encounter = $this->encounters->find($encounterId);
        if (!$encounter) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rencontre introuvable.']);
        }

        if ($this->marqueurs->getUserSignup($encounterId, $memberId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tu es déjà inscrit comme marqueur pour cette rencontre.']);
        }

        $round = max(0, min(255, (int) $this->request->getPost('round')));

        $id = $this->marqueurs->insert([
            'encounter_id' => $encounterId,
            'member_id'    => $memberId,
            'round'        => $round,
        ]);

        $row = $this->marqueurs->db
            ->table('schedule_marqueurs sm')
            ->select('sm.*, m.last_name, m.first_name')
            ->join('members m', 'm.id = sm.member_id')
            ->where('sm.id', $id)
            ->get()->getRowObject();

        $this->sendMarqueurConfirmation($memberId, $encounter);

        return $this->response->setJSON([
            'success'     => true,
            'mrq_id'      => $id,
            'name'        => $row->last_name . ' ' . member_initials($row->first_name),
            'round'       => $round,
            'match_date'  => $encounter->match_date,
            'match_time'  => $encounter->match_time,
            'competition' => $encounter->competition ?? '',
        ]);
    }

    private function sendMarqueurConfirmation(int $memberId, object $encounter): void
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member || !$member->email) {
            return;
        }

        $emailLib = \Config\Services::email();
        $emailLib->setTo($member->email);
        $emailLib->setSubject('RBC Disonais — Confirmation d\'inscription comme marqueur');
        $emailLib->setMessage(view('emails/marqueur_confirmation', [
            'member'    => $member,
            'encounter' => $encounter,
        ]));
        $emailLib->send();
    }

    private function sendArbitrageConfirmation(int $memberId, object $encounter, int $round): void
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member || !$member->email) {
            return;
        }

        $emailLib = \Config\Services::email();
        $emailLib->setTo($member->email);
        $emailLib->setSubject('RBC Disonais — Confirmation d\'inscription à l\'arbitrage');
        $emailLib->setMessage(view('emails/arbitrage_confirmation', [
            'member'    => $member,
            'encounter' => $encounter,
            'round'     => $round,
        ]));
        $emailLib->send();
    }

    private function sendBarConfirmation(int $memberId, string $date, string $period): void
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member || !$member->email) {
            return;
        }

        $emailLib = \Config\Services::email();
        $emailLib->setTo($member->email);
        $emailLib->setSubject('RBC Disonais — Confirmation de permanence au bar');
        $emailLib->setMessage(view('emails/bar_confirmation', [
            'member' => $member,
            'date'   => $date,
            'period' => $period,
        ]));
        $emailLib->send();
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
