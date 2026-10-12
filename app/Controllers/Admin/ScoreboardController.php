<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ScoreboardInningModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardPlayerModel;
use App\Models\ScoreboardTableModel;
use App\Models\ScoreboardTeamEncounterModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class ScoreboardController extends BaseController
{
    private ScoreboardMatchModel $model;

    public function __construct()
    {
        $this->model = new ScoreboardMatchModel();
    }

    public function index(): string
    {
        $upcoming = $this->model->withTable()
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy("FIELD(status, 'live', 'scheduled')", '', false)
            ->orderBy('scheduled_at IS NULL', 'ASC', false)
            ->orderBy('scheduled_at', 'ASC')
            ->findAll();

        $finished = $this->model->withTable()
            ->where('status', 'finished')
            ->orderBy('finished_at', 'DESC')
            ->findAll(100);

        return view('admin/scoreboard/index', [
            'title'    => 'Marquoir — Rencontres',
            'upcoming' => $upcoming,
            'finished' => $finished,
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    /**
     * Nouveau match d'une rencontre d'équipe : date, clubs, discipline et format repris de la fiche.
     */
    public function createForTeam(int $teamEncounterId): string
    {
        $team = (new ScoreboardTeamEncounterModel())->find($teamEncounterId);
        if (!$team) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->form(null, $team, [
            'scheduled_at' => $team->scheduled_at,
            'discipline'   => $team->discipline,
            'format'       => $team->format,
            'p1_club'      => ScoreboardTeamEncounterModel::homeLabel($team),
            'p2_club'      => ScoreboardTeamEncounterModel::awayLabel($team),
        ]);
    }

    public function store()
    {
        [$data, $errors] = $this->buildData();
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data['type']       = 'official';
        $data['status']     = 'scheduled';
        $data['created_by'] = session()->get('member_id');
        $this->model->insert($data);
        ScoreboardMatchModel::rememberExternalPlayers($data);

        return $this->redirectAfterSave($data['team_encounter_id'] ?? null, 'Match ajouté.');
    }

    public function show(int $id): string
    {
        $match = $this->find($id);
        $table = $match->table_id ? (new ScoreboardTableModel())->find($match->table_id) : null;

        return view('admin/scoreboard/show', [
            'title'       => 'Feuille de match',
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => 'Feuille de match']],
            'match'       => $match,
            'table'       => $table,
            'sheet'       => $this->buildSheet($id),
        ]);
    }

    public function pdf(int $id)
    {
        $match = $this->find($id);

        $logo = FCPATH . 'assets/images/Ecusson_RBCD.png';
        $html = view('admin/scoreboard/pdf', [
            'match' => $match,
            'sheet' => $this->buildSheet($id),
            'logo'  => is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : null,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $date     = date('Y-m-d', strtotime($match->started_at ?? $match->scheduled_at ?? 'now'));
        $filename = 'feuille_match_' . $date . '_' . url_title($match->p1_last_name . '-' . $match->p2_last_name, '-', true) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Reprises indexées par numéro : [n° => [joueur => ['points' => série, 'total' => cumul]]].
     */
    private function buildSheet(int $id): array
    {
        $sheet = [];
        $cumul = [1 => 0, 2 => 0];
        foreach ((new ScoreboardInningModel())->getForMatch($id) as $row) {
            $p = (int) $row->player;
            $cumul[$p] += (int) $row->points;
            $sheet[(int) $row->inning_no][$p] = ['points' => (int) $row->points, 'total' => $cumul[$p]];
        }
        return $sheet;
    }

    public function edit(int $id): string
    {
        $match = $this->find($id);
        $team  = $match->team_encounter_id ? (new ScoreboardTeamEncounterModel())->find($match->team_encounter_id) : null;

        return $this->form($match, $team);
    }

    public function update(int $id)
    {
        $match = $this->find($id);

        [$data, $errors] = $this->buildData();
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->model->update($match->id, $data);
        ScoreboardMatchModel::rememberExternalPlayers($data);

        return $this->redirectAfterSave($data['team_encounter_id'] ?? $match->team_encounter_id, 'Match modifié.');
    }

    public function delete(int $id)
    {
        $this->model->delete($this->find($id)->id);

        return redirect()->to(base_url('admin/scoreboard'))->with('success', 'Rencontre supprimée.');
    }

    public function reset(int $id)
    {
        $this->model->resetToScheduled((int) $this->find($id)->id);

        return redirect()->to(base_url('admin/scoreboard'))->with('success', 'Rencontre remise à zéro.');
    }

    public function searchPlayers()
    {
        return $this->response->setJSON((new ScoreboardPlayerModel())->search((string) $this->request->getGet('q')));
    }

    private function redirectAfterSave(?int $teamEncounterId, string $message)
    {
        $url = $teamEncounterId ? 'admin/scoreboard/teams/' . $teamEncounterId : 'admin/scoreboard';
        return redirect()->to(base_url($url))->with('success', $message);
    }

    private function form(?object $match, ?object $team = null, array $prefill = []): string
    {
        $settings = new SiteSettingModel();

        $breadcrumbs = [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')]];
        if ($team) {
            $breadcrumbs[] = ['title' => "Rencontres d'équipe", 'url' => base_url('admin/scoreboard/teams')];
            $breadcrumbs[] = ['title' => $team->code, 'url' => base_url('admin/scoreboard/teams/' . $team->id)];
        }
        $breadcrumbs[] = ['title' => $match ? 'Modifier' : 'Nouveau match'];

        return view('admin/scoreboard/form', [
            'title'       => $match ? 'Modifier le match' : ($team ? 'Nouveau match — ' . $team->code : 'Nouvelle rencontre'),
            'breadcrumbs' => $breadcrumbs,
            'match'       => $match,
            'team'        => $team,
            'roster'      => $team ? (new ScoreboardTeamEncounterModel())->getRoster((int) $team->team_id) : [],
            'prefill'     => $prefill,
            'tables'      => (new ScoreboardTableModel())->getActive(),
            'defaults'    => [
                'shot_clock_seconds'    => (int) $settings->getSetting('scoreboard_shot_clock_seconds', 40),
                'shot_clock_extensions' => (int) $settings->getSetting('scoreboard_shot_clock_extensions', 3),
                'innings_limit_3c'   => (int) $settings->getSetting('scoreboard_innings_limit_3c', 60),
            ],
        ]);
    }

    private function buildData(): array
    {
        $post = $this->request->getPost();
        [$data, $errors] = ScoreboardMatchModel::dataFromInput($post);

        $date = trim((string) ($post['scheduled_date'] ?? ''));
        $time = trim((string) ($post['scheduled_time'] ?? '')) ?: '19:30';
        $data['scheduled_at'] = $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null;
        $competition = trim((string) ($post['competition'] ?? ''));
        $data['competition'] = $competition !== '' ? $competition : null;

        // Le champ n'existe que dans le formulaire d'un match d'équipe : absent, on ne touche pas au rattachement
        if (array_key_exists('team_encounter_id', $post)) {
            $teamId = (int) $post['team_encounter_id'];
            $data['team_encounter_id'] = $teamId > 0 && (new ScoreboardTeamEncounterModel())->find($teamId) ? $teamId : null;
        }

        return [$data, $errors];
    }

    private function find(int $id): object
    {
        $match = $this->model->withTable()->find($id);
        if (!$match) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $match;
    }
}
