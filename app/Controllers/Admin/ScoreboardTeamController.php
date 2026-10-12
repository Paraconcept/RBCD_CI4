<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardTeamEncounterModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ScoreboardTeamController extends BaseController
{
    private ScoreboardTeamEncounterModel $model;

    public function __construct()
    {
        $this->model = new ScoreboardTeamEncounterModel();
    }

    public function index(): string
    {
        return view('admin/scoreboard/teams/index', [
            'title'       => "Marquoir — Rencontres d'équipe",
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => "Rencontres d'équipe"]],
            'encounters'  => $this->model->withProgress()->orderBy('scheduled_at', 'DESC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function store()
    {
        [$data, $errors] = $this->buildData(null);
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data['created_by'] = session()->get('member_id');
        $id = $this->model->insert($data);

        return redirect()->to(base_url('admin/scoreboard/teams/' . $id))
                         ->with('success', "Rencontre d'équipe créée. Ajoutez-y maintenant ses matchs.");
    }

    public function show(int $id): string
    {
        $encounter = $this->find($id);

        $attachable = (new ScoreboardMatchModel())->withTable()
            ->where('team_encounter_id', null)
            ->where('type', 'official')
            ->whereIn('status', ['scheduled', 'live', 'finished'])
            ->orderBy('scheduled_at', 'DESC')
            ->findAll(50);

        return view('admin/scoreboard/teams/show', [
            'title'       => "Rencontre d'équipe " . $encounter->code,
            'breadcrumbs' => [
                ['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')],
                ['title' => "Rencontres d'équipe", 'url' => base_url('admin/scoreboard/teams')],
                ['title' => $encounter->code],
            ],
            'encounter'  => $encounter,
            'matches'    => $this->model->getMatches($id),
            'attachable' => $attachable,
        ]);
    }

    public function edit(int $id): string
    {
        return $this->form($this->find($id));
    }

    public function update(int $id)
    {
        $encounter = $this->find($id);

        [$data, $errors] = $this->buildData($encounter);
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->model->update($encounter->id, $data);

        return redirect()->to(base_url('admin/scoreboard/teams/' . $encounter->id))->with('success', 'Rencontre modifiée.');
    }

    public function delete(int $id)
    {
        $this->model->delete($this->find($id)->id);

        return redirect()->to(base_url('admin/scoreboard/teams'))
                         ->with('success', "Rencontre d'équipe supprimée. Ses matchs restent disponibles comme matchs individuels.");
    }

    public function attach(int $id)
    {
        $encounter = $this->find($id);
        $matchId   = (int) $this->request->getPost('match_id');
        $matches   = new ScoreboardMatchModel();
        $match     = $matches->find($matchId);

        if (!$match || $match->team_encounter_id) {
            return redirect()->back()->with('error', 'Match introuvable ou déjà rattaché à une rencontre.');
        }

        $matches->update($matchId, ['team_encounter_id' => $encounter->id]);

        return redirect()->to(base_url('admin/scoreboard/teams/' . $encounter->id))->with('success', 'Match rattaché à la rencontre.');
    }

    public function detach(int $id, int $matchId)
    {
        $encounter = $this->find($id);
        $matches   = new ScoreboardMatchModel();
        $match     = $matches->find($matchId);

        if ($match && (int) $match->team_encounter_id === (int) $encounter->id) {
            $matches->update($matchId, ['team_encounter_id' => null]);
        }

        return redirect()->to(base_url('admin/scoreboard/teams/' . $encounter->id))
                         ->with('success', 'Match détaché : il reste disponible comme match individuel.');
    }

    private function form(?object $encounter): string
    {
        return view('admin/scoreboard/teams/form', [
            'title'       => $encounter ? 'Modifier la rencontre ' . $encounter->code : "Nouvelle rencontre d'équipe",
            'breadcrumbs' => [
                ['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')],
                ['title' => "Rencontres d'équipe", 'url' => base_url('admin/scoreboard/teams')],
                ['title' => $encounter ? $encounter->code : 'Nouvelle'],
            ],
            'encounter' => $encounter,
            'teams'     => $this->model->getCdrTeams(),
        ]);
    }

    private function buildData(?object $current): array
    {
        $post   = $this->request->getPost();
        $errors = [];

        $code = mb_strtoupper(preg_replace('/\s+/', '', (string) ($post['code'] ?? '')), 'UTF-8');
        if ($code === '' || mb_strlen($code) > 20) {
            $errors[] = 'Le numéro de rencontre est obligatoire (20 caractères maximum).';
        } else {
            $dup = $this->model->where('code', $code);
            if ($current) {
                $dup->where('id <>', $current->id);
            }
            if ($dup->countAllResults() > 0) {
                $errors[] = 'Le numéro de rencontre ' . $code . ' existe déjà.';
            }
        }

        $competition = (string) ($post['competition'] ?? '');
        if (!array_key_exists($competition, ScoreboardTeamEncounterModel::COMPETITIONS)) {
            $errors[] = 'Compétition invalide.';
        }

        $team = null;
        foreach ($this->model->getCdrTeams() as $t) {
            if ((int) $t->id === (int) ($post['team_id'] ?? 0)) {
                $team = $t;
            }
        }
        if (!$team) {
            $errors[] = 'Choisissez notre équipe.';
        }

        $opponent = trim((string) ($post['opponent_club'] ?? ''));
        if ($opponent === '') {
            $errors[] = 'Le club adverse est obligatoire.';
        }

        $date = trim((string) ($post['scheduled_date'] ?? ''));
        $time = trim((string) ($post['scheduled_time'] ?? '')) ?: '19:30';
        $notes = trim((string) ($post['notes'] ?? ''));

        return [[
            'code'          => $code,
            'competition'   => $competition,
            'team_id'       => $team->id ?? null,
            'team_name'     => $team->name ?? '',
            'opponent_club' => $opponent,
            'scheduled_at'  => $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null,
            'discipline'    => $team->discipline ?? 'libre',
            'format'        => $team->format ?? 'PB',
            'notes'         => $notes !== '' ? $notes : null,
        ], $errors];
    }

    private function find(int $id): object
    {
        $encounter = $this->model->find($id);
        if (!$encounter) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $encounter;
    }
}
