<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FrbbCdrClient;
use App\Libraries\FrbbMembersImport;
use App\Libraries\TeamRecapMailer;
use App\Libraries\TeamRecapPdf;
use App\Models\FrbbCategoryModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardPlayerModel;
use App\Models\ScoreboardTableModel;
use App\Models\ScoreboardTeamEncounterModel;
use App\Models\ScoreboardTeamSignatureModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ScoreboardTeamController extends BaseController
{
    private const POSITIONS = 3;

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

        [$rows, $compErrors] = $this->compositionData($data, null);
        if ($compErrors) {
            return redirect()->back()->withInput()->with('errors', $compErrors);
        }

        $data['created_by'] = session()->get('member_id');
        $id = $this->model->insert($data);
        $count = $this->saveComposition((int) $id, $rows);

        return redirect()->to(base_url('admin/scoreboard/teams/' . $id))
                         ->with('success', "Rencontre d'équipe créée" . ($count ? ' avec ses ' . $count . ' match' . ($count > 1 ? 's' : '') . '. Il reste à choisir les billards si besoin.' : '. Ajoutez-y maintenant ses matchs.'));
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
            'recipients' => TeamRecapMailer::recipients($encounter),
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
        try {
            $sent = TeamRecapMailer::send($encounter);
        } catch (\RuntimeException $e) {
            return $back->with('error', $e->getMessage() === "L'envoi de l'e-mail a échoué." ? "L'envoi de l'e-mail a échoué. Détails dans les logs du serveur." : $e->getMessage());
        }

        return $back->with('success', 'Feuille récapitulative et ' . $sent['sheets'] . ' feuille' . ($sent['sheets'] > 1 ? 's' : '') . ' de match envoyées à ' . implode(', ', $sent['recipients']) . '.');
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

        [$rows, $compErrors] = $this->compositionData($data, $encounter);
        if ($compErrors) {
            return redirect()->back()->withInput()->with('errors', $compErrors);
        }

        $this->model->update($encounter->id, $data);
        $this->saveComposition((int) $encounter->id, $rows);

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
            'composition' => $this->compositionRows($encounter),
            'tables'      => (new ScoreboardTableModel())->getActive(),
        ]);
    }

    /**
     * Rencontres CDR de l'équipe et joueurs des deux équipes (inscrits, puis réserves du club), d'après FRBB Liège-Lux.
     * Sans le site (injoignable, équipe non reliée) : nos joueurs inscrits seulement, l'adversaire se tape à la main.
     */
    public function frbb()
    {
        $team = null;
        foreach ($this->model->getCdrTeams() as $t) {
            if ((int) $t->id === (int) $this->request->getGet('team_id')) {
                $team = $t;
            }
        }
        if (!$team) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Équipe inconnue']);
        }

        $modes  = FrbbCategoryModel::gameModesFor($team->discipline, $team->format);
        $catIds = [];
        foreach ((new FrbbCategoryModel())->whereIn('game_mode', $modes ?: ['-'])->findAll() as $c) {
            $catIds[$c->categories] = ['id' => (int) $c->id, 'points' => (int) $c->points];
        }
        $members = [];
        foreach (db_connect()->table('members')->select('id, frbb_license')->where('frbb_license IS NOT NULL')->get()->getResult() as $m) {
            if ($k = FrbbMembersImport::licenseKey($m->frbb_license)) {
                $members[$k] = (int) $m->id;
            }
        }
        $player = static function (array $p, bool $home) use ($catIds, $members): array {
            $cat = $catIds[$p['category'] ?? ''] ?? null;
            return [
                'license'     => (string) ($p['licence'] ?? $p['license'] ?? ''),
                'last_name'   => mb_strtoupper((string) $p['last_name'], 'UTF-8'),
                'first_name'  => (string) $p['first_name'],
                'category'    => $p['category'] ?? null,
                'category_id' => $cat['id'] ?? null,
                'points'      => $p['points'] ?? ($cat['points'] ?? null),
                'registered'  => $p['registered'] ?? null,
                'member_id'   => $home ? ($members[FrbbMembersImport::licenseKey($p['licence'] ?? $p['license'] ?? '') ?? ''] ?? null) : null,
            ];
        };

        $data = $team->frbb_team_id ? (new FrbbCdrClient())->getTeamEncounters((int) $team->frbb_team_id) : null;
        if (!$data) {
            $cats = FrbbCategoryModel::memberCategories(array_column($team->roster, 'id'));
            $home = [];
            foreach ($team->roster as $i => $r) {
                $code   = $cats[(int) $r->id][$modes[0] ?? ''] ?? null;
                $home[] = ['license' => (string) $r->frbb_license, 'last_name' => mb_strtoupper($r->last_name, 'UTF-8'), 'first_name' => $r->first_name,
                           'category' => $code, 'category_id' => $catIds[$code]['id'] ?? null, 'points' => $catIds[$code]['points'] ?? null,
                           'registered' => $i + 1, 'member_id' => (int) $r->id];
            }
            return $this->response->setJSON(['available' => false, 'rules' => null, 'encounters' => [], 'home' => $home, 'teams' => (object) []]);
        }

        $homeCode = strtoupper(str_replace(' ', '', (string) (new SiteSettingModel())->getSetting('frbb_club_code', '')));
        $teams = [];
        foreach ($data['teams'] ?? [] as $id => $t) {
            $ours = strtoupper((string) $t['club_code']) === $homeCode;
            $teams[$id] = [
                'club'    => $ours ? ScoreboardPlayerModel::HOME_CLUB : mb_strtoupper((string) ($t['club_name'] ?: $t['name']), 'UTF-8'),
                'number'  => $t['number'] ?: null,
                'email'   => $t['email'] ?: null,
                'players' => array_map(static fn ($p) => $player($p, $ours), $t['players'] ?? []),
            ];
        }
        // Rencontres jouées au club (à domicile) et pas encore encodées sur le site fédéral
        $encounters = array_values(array_filter(array_map(static fn ($e) => [
            'code' => $e['code'], 'date' => $e['date'], 'time' => $e['time'], 'away' => (string) $e['away'], 'played' => (bool) $e['has_result'],
        ], array_filter($data['encounters'] ?? [], static fn ($e) => $e['is_home']))));

        return $this->response->setJSON([
            'available'  => true,
            'rules'      => $data['rules'] ?? null,
            'encounters' => $encounters,
            'home'       => $teams[(string) $team->frbb_team_id]['players'] ?? [],
            'teams'      => (object) $teams,
        ]);
    }

    /** Composition (3 positions) d'une rencontre existante, pour le formulaire : joueur visité / visiteur de chaque match. */
    private function compositionRows(?object $encounter): array
    {
        $rows = [];
        foreach ($encounter ? $this->model->getMatches((int) $encounter->id) : [] as $i => $m) {
            if ($i >= self::POSITIONS) {
                break;
            }
            $side = ScoreboardTeamEncounterModel::homeSide($encounter, $m);
            $absent = static fn (int $n): bool => (bool) ((int) $m->absent & $n);
            $pick = static fn (int $n): array => [
                'last_name' => $absent($n) ? '' : (string) $m->{'p' . $n . '_last_name'}, 'first_name' => (string) $m->{'p' . $n . '_first_name'},
                'license' => (string) $m->{'p' . $n . '_license'}, 'member_id' => $m->{'p' . $n . '_member_id'},
                'category_id' => $m->{'p' . $n . '_category_id'}, 'distance' => (int) $m->{'p' . $n . '_distance'},
                'absent' => (bool) ((int) $m->absent & $n),
            ];
            $rows[$i + 1] = [
                'match_id' => (int) $m->id,
                'table_id' => $m->table_id,
                'locked'   => $m->status === 'live' || ($m->status === 'finished' && !(int) $m->absent),
                'home'     => $pick($side),
                'away'     => $pick(3 - $side),
            ];
        }
        return $rows;
    }

    /**
     * Matchs à créer ou modifier d'après la card « Composition » (une ligne par position, joueur absent = forfait).
     * @return array{0: array<int, array{data: array, match: ?object}>, 1: string[]}
     */
    private function compositionData(array $encounter, ?object $current): array
    {
        $post     = $this->request->getPost();
        $existing = $current ? $this->model->getMatches((int) $current->id) : [];
        $e        = (object) $encounter;
        $rows     = [];
        $errors   = [];

        for ($i = 1; $i <= self::POSITIONS; $i++) {
            $match = $existing[$i - 1] ?? null;
            if ($match && ($match->status === 'live' || ($match->status === 'finished' && !(int) $match->absent))) {
                continue;
            }
            $sides = ['p1' => (array) ($post['home'][$i] ?? []), 'p2' => (array) ($post['away'][$i] ?? [])];
            $empty = true;
            foreach ($sides as $side) {
                $empty = $empty && trim((string) ($side['last_name'] ?? '')) === '' && empty($side['absent']);
            }
            if ($empty) {
                continue;
            }

            $in = ['discipline' => $e->discipline, 'format' => $e->format, 'table_id' => $post['table'][$i] ?? null,
                   'p1_club' => ScoreboardTeamEncounterModel::homeLabel($e), 'p2_club' => ScoreboardTeamEncounterModel::awayLabel($e)];
            foreach ($sides as $p => $side) {
                foreach (['last_name', 'first_name', 'license', 'member_id', 'category_id', 'distance', 'absent'] as $f) {
                    $in[$p . '_' . $f] = $side[$f] ?? '';
                }
            }
            // Notre joueur reconnu à sa licence : membre du club (envoi de la feuille, statistiques)
            if (empty($in['p1_member_id']) && ($key = FrbbMembersImport::licenseKey($in['p1_license']))) {
                foreach (db_connect()->table('members')->select('id, frbb_license')->like('frbb_license', $key)->get()->getResult() as $m) {
                    if (FrbbMembersImport::licenseKey($m->frbb_license) === $key) {
                        $in['p1_member_id'] = $m->id;
                    }
                }
            }

            [$data, $errs] = ScoreboardMatchModel::dataFromInput($in);
            foreach ($errs as $err) {
                $errors[] = 'Match ' . $i . ' : ' . str_replace(['du joueur 1', 'du joueur 2', 'joueur 1', 'joueur 2'], ['de notre joueur', 'du joueur visiteur', 'notre joueur', 'joueur visiteur'], $err);
            }
            $data['scheduled_at'] = $e->scheduled_at;
            $rows[$i] = ['data' => $data, 'match' => $match];
        }

        return [$rows, $errors];
    }

    /** @return int matchs créés */
    private function saveComposition(int $encounterId, array $rows): int
    {
        $model    = new ScoreboardMatchModel();
        $created  = 0;
        $encounter = $this->model->find($encounterId);
        foreach ($rows as $row) {
            $data = $row['data'];
            $match = $row['match'];
            if ($match) {
                // Les réglages du match (limite, chrono, destinataires) restent ceux du match
                $data = array_filter($data, static fn ($k) => preg_match('/^(p[12]_|absent$|table_id$|scheduled_at$)/', $k), ARRAY_FILTER_USE_KEY);
                if ($data['absent']) {
                    $data += ScoreboardMatchModel::forfeitData($data['absent'], $data['scheduled_at']);
                } elseif ((int) $match->absent > 0) {
                    $data += ['status' => 'scheduled', 'winner' => null, 'finished_at' => null];
                }
                $model->update($match->id, $data);
            } else {
                $data += [
                    'type' => 'official', 'status' => 'scheduled', 'team_encounter_id' => $encounterId, 'signature_required' => 0,
                    'send_to_club_ds' => 1, 'send_to_cdr' => ScoreboardMatchModel::isCdr(null, $encounter->competition) ? 1 : 0,
                    'send_to_p1' => 1, 'send_to_p2' => 1, 'created_by' => session()->get('member_id'),
                ];
                if ($data['absent']) {
                    $data = array_merge($data, ScoreboardMatchModel::forfeitData($data['absent'], $data['scheduled_at']));
                }
                $model->insert($data);
                $created++;
            }
            ScoreboardMatchModel::rememberExternalPlayers($data);
        }
        return $created;
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

        $opponent = mb_strtoupper(trim((string) ($post['opponent_club'] ?? '')), 'UTF-8');
        if ($opponent === '') {
            $errors[] = 'Le club adverse est obligatoire.';
        }
        $teamClub = mb_strtoupper(trim((string) ($post['team_club'] ?? '')), 'UTF-8') ?: ScoreboardPlayerModel::HOME_CLUB;
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
