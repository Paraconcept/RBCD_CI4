<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MatchResultMailer;
use App\Libraries\MatchSheetPdf;
use App\Libraries\TeamRecapPdf;
use App\Models\FrbbCategoryModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardPlayerModel;
use App\Models\ScoreboardSignatureModel;
use App\Models\ScoreboardTableModel;
use App\Models\ScoreboardTeamEncounterModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ScoreboardController extends BaseController
{
    private ScoreboardMatchModel $model;

    // Destinataires des résultats : case à cocher, champ e-mail, libellé des messages d'erreur
    private const RECIPIENTS = [
        'club'      => ['send_to_club_ds', 'club_ds_email', 'directeur sportif du RBC Disonais'],
        'dsr'       => ['send_to_dsr', 'dsr_email', 'directeur sportif régional'],
        'cdr'       => ['send_to_cdr', 'cdr_email', 'responsable CDR'],
        'organizer' => ['send_to_organizer_ds', 'organizer_ds_email', 'directeur sportif du club organisateur'],
    ];

    public function __construct()
    {
        $this->model = new ScoreboardMatchModel();
    }

    public function index(): string
    {
        $this->model->purgeOldFriendlies();

        $upcoming = $this->model->withTable()
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy("FIELD(status, 'live', 'scheduled')", '', false)
            ->orderBy('scheduled_at IS NULL', 'ASC', false)
            ->orderBy('scheduled_at', 'ASC')
            ->paginate(25, 'upcoming');

        // Seules les officielles restent jusqu'au nettoyage de fin de saison (les amicales sont effacées le lendemain).
        $finished = $this->model->withTable()
            ->where('status', 'finished')
            ->where('type', 'official')
            ->orderBy('finished_at', 'DESC')
            ->paginate(25, 'finished');

        return view('admin/scoreboard/index', [
            'pager'     => $this->model->pager,
            'title'     => 'Marquoir — Rencontres',
            'upcoming'  => $upcoming,
            'finished'  => $finished,
            'purgeable' => $this->model->purgeable(),
        ]);
    }

    public function purge()
    {
        $p = $this->model->purgeFinished();
        $parts = [count($p['matches']) . ' partie(s)'];
        if ($p['encounters']) $parts[] = count($p['encounters']) . ' rencontre(s) d\'équipe';
        if ($p['finals'])     $parts[] = count($p['finals']) . ' finale(s)';

        return redirect()->to(base_url('admin/scoreboard'))->with('success', 'Nettoyage terminé, effacé : ' . implode(', ', $parts) . '.');
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
        if ($data['absent']) {
            $data = array_merge($data, ScoreboardMatchModel::forfeitData($data['absent'], $data['scheduled_at']));
        }
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
            'sheets'      => MatchSheetPdf::sheets($id),
            'signature'   => (new ScoreboardSignatureModel())->select('referee_name, signed_at')->where('match_id', $id)->first(),
            'recipients'  => MatchResultMailer::recipients($match),
        ]);
    }

    public function pdf(int $id)
    {
        $match = $this->find($id);
        // Pas de feuille de match pour les parties amicales
        if ($match->type !== 'official') {
            return redirect()->to(base_url('admin/scoreboard/' . $id));
        }
        [$pdf, $filename] = MatchSheetPdf::render($match);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    public function comment(int $id)
    {
        $match   = $this->find($id);
        $comment = mb_substr(trim((string) $this->request->getPost('comment')), 0, 500);
        $this->model->update($match->id, ['comment' => $comment !== '' ? $comment : null]);

        return redirect()->to(base_url('admin/scoreboard/' . $id))->with('success', 'Commentaire enregistré.');
    }

    /** Porte de secours : renvoi manuel, sans exiger de signature (comme la feuille récapitulative). */
    public function sendResult(int $id)
    {
        $match = $this->find($id);
        $back  = redirect()->to(base_url('admin/scoreboard/' . $id));
        if ($match->type !== 'official' || $match->status !== 'finished' || ScoreboardMatchModel::isTrio($match)) {
            return $back->with('error', 'Seuls les résultats des matchs officiels terminés peuvent être envoyés.');
        }

        try {
            $sent = MatchResultMailer::send($match);
        } catch (\Throwable $e) {
            return $back->with('error', "L'envoi de l'e-mail a échoué. Détails dans les logs du serveur.");
        }
        if (!$sent) {
            return $back->with('error', 'Aucun destinataire coché : modifiez le match pour choisir à qui envoyer les résultats.');
        }

        return $back->with('success', 'Résultats envoyés à ' . implode(', ', $sent) . '.');
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

        $played = (int) $match->p1_innings + (int) $match->p2_innings > 0 || $match->status === 'live';
        if ($data['absent'] && $played) {
            return redirect()->back()->withInput()->with('errors', ['Ce match a déjà été joué : remettez-le à zéro avant de le déclarer forfait.']);
        }
        if ($data['absent']) {
            $data = array_merge($data, ScoreboardMatchModel::forfeitData($data['absent'], $data['scheduled_at']));
        } elseif ((int) $match->absent > 0) {
            // Forfait annulé : le match redevient à jouer
            $data += ['status' => 'scheduled', 'winner' => null, 'finished_at' => null];
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
            'roster'      => $roster = $team ? (new ScoreboardTeamEncounterModel())->getRoster((int) $team->team_id) : [],
            'rosterCategories' => FrbbCategoryModel::memberCategories(array_column($roster, 'id')),
            'categories'  => (new FrbbCategoryModel())->forScoreboard(),
            'prefill'     => $prefill,
            'tables'      => (new ScoreboardTableModel())->getActive(),
            'dsr'         => MatchSheetPdf::regionalDirector(),
            'cdr'         => TeamRecapPdf::cdr(),
            'clubDsName'  => $this->clubDirectorName(),
            'knownOrganizerEmails' => array_column($this->model->builder()->distinct()->select('organizer_ds_email')
                ->where('organizer_ds_email IS NOT NULL')->orderBy('organizer_ds_email')->get()->getResultArray(), 'organizer_ds_email'),
            'defaults'    => [
                'shot_clock_seconds'    => (int) $settings->getSetting('scoreboard_shot_clock_seconds', 40),
                'shot_clock_extensions' => (int) $settings->getSetting('scoreboard_shot_clock_extensions', 3),
                'innings_limit_3c'   => (int) $settings->getSetting('scoreboard_innings_limit_3c', 60),
                'set_points'         => ScoreboardMatchModel::DEFAULT_SET_POINTS,
                'sets_to_win'        => ScoreboardMatchModel::DEFAULT_SETS_TO_WIN,
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
        $number = mb_substr(trim((string) ($post['match_number'] ?? '')), 0, 30);
        $data['match_number'] = $number !== '' ? $number : null;
        $data['signature_required'] = !empty($post['signature_required']) ? 1 : 0;

        foreach (self::RECIPIENTS as $key => [$flag, $field, $label]) {
            $data[$flag] = !empty($post[$flag]) ? 1 : 0;
            $address = mb_strtolower(trim((string) ($post[$field] ?? '')));
            $data[$field] = $address !== '' ? mb_substr($address, 0, 150) : null;
            if ($address !== '' && !filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse e-mail du {$label} est invalide.";
            } elseif ($data[$flag] && $address === '') {
                $errors[] = "Indiquez l'adresse e-mail du {$label}, ou décochez-le.";
            }
        }

        // Le champ n'existe que dans le formulaire d'un match d'équipe : absent, on ne touche pas au rattachement
        if (array_key_exists('team_encounter_id', $post)) {
            $teamId = (int) $post['team_encounter_id'];
            $data['team_encounter_id'] = $teamId > 0 && (new ScoreboardTeamEncounterModel())->find($teamId) ? $teamId : null;
        }

        return [$data, $errors];
    }

    private function clubDirectorName(): string
    {
        $m = db_connect()->table('admin_user_roles r')->select('m.last_name, m.first_name')
            ->join('members m', 'm.id = r.member_id')->where('r.role', 'Directeur Sportif')->where('m.is_active', 1)
            ->orderBy('r.sort_order', 'ASC')->get()->getRow();

        return $m ? mb_strtoupper($m->last_name) . ' ' . $m->first_name : '';
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
