<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ScheduleEncounterModel;
use App\Models\ScheduleEncounterPlayerModel;
use App\Models\ScheduleArbitrageModel;
use App\Models\ScheduleBarDutyModel;
use App\Models\MemberModel;

class ScheduleController extends BaseController
{
    private ScheduleEncounterModel       $encounters;
    private ScheduleEncounterPlayerModel $players;
    private ScheduleArbitrageModel       $arbitrage;
    private ScheduleBarDutyModel         $barDuties;

    public function __construct()
    {
        $this->encounters = new ScheduleEncounterModel();
        $this->players    = new ScheduleEncounterPlayerModel();
        $this->arbitrage  = new ScheduleArbitrageModel();
        $this->barDuties  = new ScheduleBarDutyModel();
    }

    public function index(?string $week = null, ?string $year = null): string
    {
        $now  = new \DateTime();
        $week = $week ? (int) $week : (int) $now->format('W');
        $year = $year ? (int) $year : (int) $now->format('o');

        $weekDates   = $this->getWeekDates($week, $year);
        $encounters  = $this->encounters->getWeek($week, $year);
        $encounterIds = array_map(fn($e) => $e->id, $encounters);

        $playersByEncounter   = $this->getPlayersByEncounter($encounterIds);
        $arbitrageByEncounter = $this->arbitrage->getForEncounters($encounterIds);
        $barByDate            = $this->barDuties->getForDates($weekDates);

        $byDate = [];
        foreach ($encounters as $enc) {
            $enc->players       = $playersByEncounter[$enc->id] ?? [];
            $enc->arbitrageRows = $arbitrageByEncounter[$enc->id] ?? [];
            $byDate[$enc->match_date][] = $enc;
        }

        $nav = $this->getPrevNextWeek($week, $year);

        $adminUsers = $this->getAdminUsersList();

        return view('admin/schedule/index', [
            'title'       => 'Tableau des rencontres',
            'breadcrumbs' => [['title' => 'Tableau des rencontres']],
            'week'        => $week,
            'year'        => $year,
            'weekDates'   => $weekDates,
            'byDate'      => $byDate,
            'barByDate'   => $barByDate,
            'prev'        => $nav['prev'],
            'next'        => $nav['next'],
            'adminUsers'  => $adminUsers,
        ]);
    }

    public function create(): string
    {
        return view('admin/schedule/form', [
            'title'       => 'Nouvelle rencontre',
            'breadcrumbs' => [
                ['title' => 'Tableau des rencontres', 'url' => base_url('admin/schedule')],
                ['title' => 'Nouvelle rencontre'],
            ],
            'encounter'   => null,
            'existingPlayers' => [],
            'members'     => (new MemberModel())->where('is_active', 1)->orderBy('last_name')->orderBy('first_name')->findAll(),
        ]);
    }

    public function store()
    {
        if (!$this->validateEncounterForm()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $encounterId = $this->encounters->insert([
            'encounter_type' => $this->request->getPost('encounter_type') === 'finale' ? 'finale' : 'normal',
            'match_date'  => $this->request->getPost('match_date'),
            'match_time'  => $this->request->getPost('match_time'),
            'is_home'     => (int) $this->request->getPost('is_home'),
            'venue'       => $this->request->getPost('venue') ?: null,
            'competition' => $this->request->getPost('competition') ?? '',
            'notes'       => $this->request->getPost('notes') ?: null,
        ]);

        $this->savePlayers((int) $encounterId);

        return redirect()->to(base_url('admin/schedule'))->with('success', 'Rencontre créée avec succès.');
    }

    public function edit(int $id): string
    {
        $encounter = $this->encounters->getWithPlayers($id);
        if (!$encounter) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/schedule/form', [
            'title'       => 'Modifier la rencontre',
            'breadcrumbs' => [
                ['title' => 'Tableau des rencontres', 'url' => base_url('admin/schedule')],
                ['title' => 'Modifier la rencontre'],
            ],
            'encounter'       => $encounter,
            'existingPlayers' => $encounter->players,
            'members'         => (new MemberModel())->where('is_active', 1)->orderBy('last_name')->orderBy('first_name')->findAll(),
        ]);
    }

    public function update(int $id)
    {
        $encounter = $this->encounters->find($id);
        if (!$encounter) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validateEncounterForm()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->encounters->update($id, [
            'encounter_type' => $this->request->getPost('encounter_type') === 'finale' ? 'finale' : 'normal',
            'match_date'  => $this->request->getPost('match_date'),
            'match_time'  => $this->request->getPost('match_time'),
            'is_home'     => (int) $this->request->getPost('is_home'),
            'venue'       => $this->request->getPost('venue') ?: null,
            'competition' => $this->request->getPost('competition') ?? '',
            'notes'       => $this->request->getPost('notes') ?: null,
        ]);

        $this->players->deleteByEncounter($id);
        $this->savePlayers($id);

        $week = (int) (new \DateTime($this->request->getPost('match_date')))->format('W');
        $year = (int) (new \DateTime($this->request->getPost('match_date')))->format('o');

        return redirect()->to(base_url("admin/schedule/{$week}/{$year}"))
                         ->with('success', 'Rencontre modifiée avec succès.');
    }

    public function delete(int $id)
    {
        $encounter = $this->encounters->find($id);
        if (!$encounter) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $week = (int) (new \DateTime($encounter->match_date))->format('W');
        $year = (int) (new \DateTime($encounter->match_date))->format('o');

        $this->encounters->delete($id);

        return redirect()->to(base_url("admin/schedule/{$week}/{$year}"))
                         ->with('success', 'Rencontre supprimée.');
    }

    public function designateReferee(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $adminUserId = (int) $this->request->getPost('admin_user_id');
        if (!$adminUserId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Utilisateur invalide.']);
        }

        $round    = max(0, (int) $this->request->getPost('round'));
        $existing = $this->arbitrage->getUserSignup($encounterId, $adminUserId);

        if ($existing) {
            $this->arbitrage->update($existing->id, [
                'round'           => $round,
                'assignment_type' => 'designated',
                'confirmed'       => 0,
                'confirmed_at'    => null,
            ]);
            $arbId = $existing->id;
        } else {
            $arbId = $this->arbitrage->insert([
                'encounter_id'    => $encounterId,
                'round'           => $round,
                'admin_user_id'   => $adminUserId,
                'assignment_type' => 'designated',
                'confirmed'       => 0,
            ]);
        }

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
            'type'        => 'designated',
            'confirmed'   => 0,
        ]);
    }

    public function removeReferee(int $encounterId)
    {
        $this->response->setHeader('Content-Type', 'application/json');

        $arbId = (int) $this->request->getPost('arb_id');
        if ($arbId) {
            $row = $this->arbitrage->find($arbId);
            if ($row && $row->encounter_id == $encounterId) {
                $this->arbitrage->delete($arbId);
            }
        } else {
            // Fallback : normal match (1 seul arbitre)
            $existing = $this->arbitrage->where('encounter_id', $encounterId)->first();
            if ($existing) {
                $this->arbitrage->delete($existing->id);
            }
        }

        return $this->response->setJSON(['success' => true]);
    }

    private function validateEncounterForm(): bool
    {
        return $this->validate([
            'match_date' => 'required|valid_date[Y-m-d]',
            'match_time' => 'required',
        ]);
    }

    private function savePlayers(int $encounterId): void
    {
        $type          = $this->request->getPost('encounter_type') === 'finale' ? 'finale' : 'normal';
        $opponentNames = $this->request->getPost('opponent_name') ?? [];
        $now           = date('Y-m-d H:i:s');

        if ($type === 'finale') {
            $homeNames = $this->request->getPost('player_home_name') ?? [];
            foreach ($homeNames as $i => $homeName) {
                $homeName     = trim($homeName);
                $opponentName = trim($opponentNames[$i] ?? '');
                if ($homeName !== '' && $opponentName !== '') {
                    $this->players->insert([
                        'encounter_id'     => $encounterId,
                        'member_id'        => null,
                        'player_home_name' => $homeName,
                        'opponent_name'    => $opponentName,
                        'created_at'       => $now,
                    ]);
                }
            }
        } else {
            $memberIds = $this->request->getPost('member_id') ?? [];
            foreach ($memberIds as $i => $memberId) {
                $memberId     = (int) $memberId;
                $opponentName = trim($opponentNames[$i] ?? '');
                if ($memberId && $opponentName !== '') {
                    $this->players->insert([
                        'encounter_id'     => $encounterId,
                        'member_id'        => $memberId,
                        'player_home_name' => null,
                        'opponent_name'    => $opponentName,
                        'created_at'       => $now,
                    ]);
                }
            }
        }
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

    private function getAdminUsersList(): array
    {
        return \Config\Database::connect()
            ->table('admin_users')
            ->select('id, last_name, first_name')
            ->where('is_active', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get()->getResultObject();
    }
}
