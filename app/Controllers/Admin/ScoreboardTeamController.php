<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MatchSheetPdf;
use App\Libraries\TeamRecapPdf;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardTeamEncounterModel;
use App\Models\ScoreboardTeamSignatureModel;
use App\Models\SiteSettingModel;
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
            'matches'    => $matches = $this->model->getMatches($id),
            'attachable' => $attachable,
            'result'     => ScoreboardTeamEncounterModel::computeResult($encounter, $matches),
            'recipients' => $this->recapRecipients($encounter),
            'recapSignedAt' => (new ScoreboardTeamSignatureModel())->signedAt($id),
        ]);
    }

    public function pdf(int $id)
    {
        [$pdf, $filename] = TeamRecapPdf::render($this->find($id));

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    public function sendRecap(int $id)
    {
        $encounter = $this->find($id);
        $back      = redirect()->to(base_url('admin/scoreboard/teams/' . $id));
        $result    = ScoreboardTeamEncounterModel::computeResult($encounter, $this->model->getMatches($id));
        if (!$result['complete']) {
            return $back->with('error', 'La feuille récapitulative ne peut être envoyée que lorsque tous les matchs sont terminés.');
        }

        $recipients = $this->recapRecipients($encounter);
        if (!$recipients) {
            return $back->with('error', 'Aucune adresse e-mail valide pour envoyer la feuille récapitulative.');
        }

        [$pdf, $filename] = TeamRecapPdf::render($encounter);
        $sheets = array_map(static fn ($m) => MatchSheetPdf::render($m),
            array_filter($this->model->getMatches($id), static fn ($m) => $m->status === 'finished'));

        $email = \Config\Services::email();
        // L'expéditeur doit rester le compte SMTP (send.one.com peut refuser un autre expéditeur) ; les réponses vont au DS.
        $email->setFrom(config('Email')->fromEmail, 'RBC Disonais');
        $email->setReplyTo('ds@rbcd.be', 'Directeur sportif — RBC Disonais');
        $email->setTo($recipients);
        $email->setSubject('Feuille récapitulative — ' . ScoreboardTeamEncounterModel::homeLabel($encounter)
            . ' / ' . ScoreboardTeamEncounterModel::awayLabel($encounter) . ' (' . $encounter->code . ')');
        $email->setMessage(view('emails/team_recap', ['encounter' => $encounter, 'result' => $result]));
        $email->attach($pdf, 'attachment', $filename, 'application/pdf');
        foreach (array_values($sheets) as $i => [$sheet, $sheetName]) {
            // Préfixe d'ordre : deux matchs peuvent opposer les mêmes noms et l'ordre des pièces jointes suit la feuille
            $email->attach($sheet, 'attachment', sprintf('%d_%s', $i + 1, $sheetName), 'application/pdf');
        }

        if (!$email->send(false)) {
            log_message('error', 'Envoi feuille récapitulative ' . $encounter->code . ' : ' . $email->printDebugger(['headers']));
            return $back->with('error', "L'envoi de l'e-mail a échoué. Détails dans les logs du serveur.");
        }

        $this->model->update($id, ['recap_sent_at' => date('Y-m-d H:i:s'), 'recap_sent_to' => implode(', ', $recipients)]);

        return $back->with('success', 'Feuille récapitulative et ' . count($sheets) . ' feuille' . (count($sheets) > 1 ? 's' : '') . ' de match envoyées à ' . implode(', ', $recipients) . '.');
    }

    private function recapRecipients(object $encounter): array
    {
        $emails = [$encounter->team_email, $encounter->opponent_email, TeamRecapPdf::cdr()->email];
        $emails = array_filter(array_map(static fn ($e) => strtolower(trim((string) $e)), $emails),
            static fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        return array_values(array_unique($emails));
    }

    public function forfeit(int $id)
    {
        $encounter = $this->find($id);
        $side      = $this->request->getPost('side') === 'home' ? 'home' : 'away';
        $count     = $this->model->declareTeamForfeit($encounter, $side, session()->get('member_id'));
        $label     = $side === 'home' ? ScoreboardTeamEncounterModel::homeLabel($encounter) : ScoreboardTeamEncounterModel::awayLabel($encounter);

        return redirect()->to(base_url('admin/scoreboard/teams/' . $id))
                         ->with('success', 'Forfait de ' . $label . ' : ' . $count . ' match' . ($count > 1 ? 's' : '') . ' déclaré' . ($count > 1 ? 's' : '') . ' forfait.');
    }

    public function cancelForfeit(int $id)
    {
        $this->model->cancelForfeits($this->find($id));

        return redirect()->to(base_url('admin/scoreboard/teams/' . $id))->with('success', 'Forfaits annulés : les matchs sont de nouveau à jouer.');
    }

    public function comment(int $id)
    {
        $encounter = $this->find($id);
        $comment   = mb_substr(trim((string) $this->request->getPost('recap_comment')), 0, 1000);
        $this->model->update($encounter->id, ['recap_comment' => $comment !== '' ? $comment : null]);

        return redirect()->to(base_url('admin/scoreboard/teams/' . $id) . '#commentaire')->with('success', 'Commentaire enregistré.');
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
            'teams'       => $this->model->getCdrTeams(),
            'clubEmail'   => (string) (new SiteSettingModel())->getSetting('scoreboard_club_email', 'ds@rbcd.be'),
            'knownEmails' => $this->model->knownOpponentEmails(),
            'cdr'         => TeamRecapPdf::cdr(),
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
        $teamClub = trim((string) ($post['team_club'] ?? '')) ?: 'RBC Disonais';
        $number   = static fn ($v): ?int => (int) $v > 0 ? min(255, (int) $v) : null;
        $email    = static function ($v) use (&$errors): ?string {
            $v = trim((string) $v);
            if ($v === '') {
                return null;
            }
            if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Adresse e-mail invalide : ' . $v;
            }
            return mb_substr($v, 0, 150);
        };

        $date = trim((string) ($post['scheduled_date'] ?? ''));
        $time = trim((string) ($post['scheduled_time'] ?? '')) ?: '19:30';
        $notes = trim((string) ($post['notes'] ?? ''));

        return [[
            'code'          => $code,
            'competition'   => $competition,
            'team_id'       => $team->id ?? null,
            'team_name'       => $team->name ?? '',
            'team_club'       => $teamClub,
            'team_number'     => $number($post['team_number'] ?? null),
            'opponent_club'   => $opponent,
            'opponent_number' => $number($post['opponent_number'] ?? null),
            'team_email'      => $email($post['team_email'] ?? ''),
            'opponent_email'  => $email($post['opponent_email'] ?? '')
                                 ?? ($this->model->knownOpponentEmails()[ScoreboardTeamEncounterModel::normalizeClub($opponent)] ?? null),
            'scheduled_at'  => $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null,
            'discipline'    => $team->discipline ?? 'libre',
            'format'        => $team->format ?? 'PB',
            'notes'         => $notes !== '' ? $notes : null,
            'recap_signature_required' => !empty($post['recap_signature_required']) ? 1 : 0,
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
