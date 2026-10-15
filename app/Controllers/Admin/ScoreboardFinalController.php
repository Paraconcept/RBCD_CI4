<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FrbbCategoryModel;
use App\Models\ScoreboardFinalModel;
use App\Models\ScoreboardFinalPlayerModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardTableModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class ScoreboardFinalController extends BaseController
{
    private ScoreboardFinalModel $model;
    private ScoreboardFinalPlayerModel $players;

    public function __construct()
    {
        $this->model   = new ScoreboardFinalModel();
        $this->players = new ScoreboardFinalPlayerModel();
    }

    public function index(): string
    {
        return view('admin/scoreboard/finals/index', [
            'title'       => 'Marquoir — Finales et poules',
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => 'Finales et poules']],
            'finals'      => $this->model->withProgress()->orderBy('scheduled_at', 'DESC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function store()
    {
        [$data, $players, $errors] = $this->buildData();
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data['created_by'] = session()->get('member_id');
        $id = $this->model->insert($data);
        $this->savePlayers($id, $players);
        $count = $this->model->generateMatches($this->model->find($id), session()->get('member_id'));

        return redirect()->to(base_url('admin/scoreboard/finals/' . $id))
                         ->with('success', 'Finale créée : ' . $count . ' matchs générés.');
    }

    public function show(int $id): string
    {
        $final   = $this->find($id);
        $players = $this->players->getForFinal($id);
        $matches = $this->model->getMatches($id);

        return view('admin/scoreboard/finals/show', [
            'title'       => $final->name,
            'breadcrumbs' => [
                ['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')],
                ['title' => 'Finales et poules', 'url' => base_url('admin/scoreboard/finals')],
                ['title' => $final->name],
            ],
            'final'     => $final,
            'players'   => $players,
            'matches'   => $matches,
            'standings' => ScoreboardFinalModel::standings($players, $matches),
            'grid'      => ScoreboardFinalModel::crossTable($matches),
            'tables'    => $this->tableNames($final),
            'allTables' => (new ScoreboardTableModel())->getActive(),
            'started'   => $this->model->hasStarted($id),
        ]);
    }

    public function edit(int $id): string
    {
        return $this->form($this->find($id));
    }

    public function update(int $id)
    {
        $final   = $this->find($id);
        $started = $this->model->hasStarted($id);

        if ($started) {
            // Une fois la finale commencée, seuls le nom et la date changent (joueurs et règles sont figés)
            $name = trim((string) $this->request->getPost('name'));
            if ($name === '') {
                return redirect()->back()->withInput()->with('errors', ['Le nom de la finale est obligatoire.']);
            }
            $date = trim((string) $this->request->getPost('scheduled_date'));
            $time = trim((string) $this->request->getPost('scheduled_time')) ?: '09:00';
            $data = ['name' => $name, 'scheduled_at' => $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null];
            $this->model->update($id, $data);
            $this->db()->table('scoreboard_matches')->where('final_id', $id)->update(['competition' => $data['name']]);
            $this->db()->table('scoreboard_matches')->where('final_id', $id)->where('status', 'scheduled')->update(['scheduled_at' => $data['scheduled_at']]);
            return redirect()->to(base_url('admin/scoreboard/finals/' . $id))->with('success', 'Finale modifiée.');
        }

        [$data, $players, $errors] = $this->buildData();
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->model->update($id, $data);
        $this->players->where('final_id', $id)->delete();
        $this->savePlayers($id, $players);
        $count = $this->model->generateMatches($this->model->find($id), session()->get('member_id'));

        return redirect()->to(base_url('admin/scoreboard/finals/' . $id))
                         ->with('success', 'Finale modifiée : ' . $count . ' matchs régénérés.');
    }

    public function delete(int $id)
    {
        $final = $this->find($id);
        if ($this->model->hasStarted($id)) {
            return redirect()->back()->with('error', 'Cette finale a déjà commencé : elle ne peut plus être supprimée.');
        }
        $this->model->delete($final->id);

        return redirect()->to(base_url('admin/scoreboard/finals'))->with('success', 'Finale supprimée.');
    }

    public function comment(int $id)
    {
        $final   = $this->find($id);
        $comment = mb_substr(trim((string) $this->request->getPost('comment')), 0, 1000);
        $this->model->update($final->id, ['comment' => $comment !== '' ? $comment : null]);

        return redirect()->to(base_url('admin/scoreboard/finals/' . $id) . '#commentaire')->with('success', 'Commentaire enregistré.');
    }

    public function reorder(int $id)
    {
        $final = $this->find($id);
        $ids   = array_values(array_unique(array_filter(array_map('intval', explode(',', (string) $this->request->getPost('order'))))));
        if (!$this->model->reorderMatches($final, $ids)) {
            return redirect()->to(base_url('admin/scoreboard/finals/' . $id))->with('error', 'Ordre des matchs invalide, rechargez la page.');
        }

        return redirect()->to(base_url('admin/scoreboard/finals/' . $id) . '#ordre')->with('success', 'Ordre des matchs enregistré.');
    }

    /**
     * Billard, horaire et points à jouer d'un match pas encore commencé. Les joueurs restent ceux
     * générés : les changer fausserait le classement.
     */
    public function updateMatch(int $id, int $matchId)
    {
        $final = $this->find($id);
        $back  = redirect()->to(base_url('admin/scoreboard/finals/' . $id) . '#ordre');
        $match = (new ScoreboardMatchModel())->where('final_id', $final->id)->find($matchId);
        if (!$match) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($match->status !== 'scheduled') {
            return $back->with('error', 'Ce match a déjà commencé : il ne peut plus être modifié ici.');
        }

        $post    = $this->request->getPost();
        $tableId = (int) ($post['table_id'] ?? 0);
        if ($tableId > 0 && !(new ScoreboardTableModel())->find($tableId)) {
            return $back->with('error', 'Billard inconnu.');
        }
        $data = ['table_id' => $tableId > 0 ? $tableId : null];
        foreach (['p1_distance', 'p2_distance'] as $field) {
            $v = (int) ($post[$field] ?? 0);
            if ($v < 1 || $v > 9999) {
                return $back->with('error', 'Les points à jouer doivent être compris entre 1 et 9999.');
            }
            $data[$field] = $v;
        }
        $date = trim((string) ($post['scheduled_date'] ?? ''));
        $time = trim((string) ($post['scheduled_time'] ?? '')) ?: '09:00';
        $data['scheduled_at'] = $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null;

        (new ScoreboardMatchModel())->update($match->id, $data);

        return $back->with('success', 'Match n°' . (int) $match->final_order . ' modifié.');
    }

    public function pdf(int $id)
    {
        $final   = $this->find($id);
        $players = $this->players->getForFinal($id);
        $matches = $this->model->getMatches($id);

        $logo = FCPATH . 'assets/images/Ecusson_RBCD.png';
        $html = view('admin/scoreboard/finals/pdf', [
            'final'     => $final,
            'players'   => $players,
            'matches'   => $matches,
            'standings' => ScoreboardFinalModel::standings($players, $matches),
            'grid'      => ScoreboardFinalModel::crossTable($matches),
            'logo'      => is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : null,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'finale_' . date('Y-m-d', strtotime($final->scheduled_at ?? 'now')) . '_' . url_title($final->name, '-', true) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    private function form(?object $final): string
    {
        $settings = new SiteSettingModel();

        return view('admin/scoreboard/finals/form', [
            'title'       => $final ? 'Modifier la finale' : 'Nouvelle finale ou poule',
            'breadcrumbs' => [
                ['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')],
                ['title' => 'Finales et poules', 'url' => base_url('admin/scoreboard/finals')],
                ['title' => $final ? 'Modifier' : 'Nouvelle'],
            ],
            'final'      => $final,
            'players'    => $final ? $this->players->getForFinal((int) $final->id) : [],
            'started'    => $final ? $this->model->hasStarted((int) $final->id) : false,
            'tables'     => (new ScoreboardTableModel())->getActive(),
            'categories' => (new FrbbCategoryModel())->forScoreboard(),
            'defaults'   => [
                'shot_clock_seconds'    => (int) $settings->getSetting('scoreboard_shot_clock_seconds', 40),
                'shot_clock_extensions' => (int) $settings->getSetting('scoreboard_shot_clock_extensions', 3),
                'innings_limit_3c'      => (int) $settings->getSetting('scoreboard_innings_limit_3c', 60),
            ],
        ]);
    }

    /**
     * Retourne [données de la finale, joueurs, erreurs]. Les joueurs reprennent la validation des rencontres.
     */
    private function buildData(): array
    {
        $post   = $this->request->getPost();
        $errors = [];

        $name = trim((string) ($post['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Le nom de la finale est obligatoire.';
        }
        $date = trim((string) ($post['scheduled_date'] ?? ''));
        $time = trim((string) ($post['scheduled_time'] ?? '')) ?: '09:00';

        $players = [];
        $seen    = [];
        for ($k = 1; $k <= ScoreboardFinalModel::MAX_PLAYERS; $k++) {
            $f = 'f' . $k . '_';
            if (trim((string) ($post[$f . 'last_name'] ?? '')) === '') {
                continue;
            }
            // dataFromInput valide un joueur à la fois, placé en « joueur 1 » d'une rencontre fictive
            $in = ['discipline' => $post['discipline'] ?? '', 'format' => $post['format'] ?? '', 'p2_last_name' => '-', 'p2_first_name' => '-', 'p2_distance' => 1];
            foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'distance', 'category_id'] as $field) {
                $in['p1_' . $field] = $post[$f . $field] ?? '';
            }
            [$d, $e] = ScoreboardMatchModel::dataFromInput($in);
            foreach ($e as $msg) {
                if (!str_contains($msg, 'Discipline') && !str_contains($msg, 'Format')) {
                    $errors[] = 'Joueur n°' . (count($players) + 1) . ' : ' . str_replace(' du joueur 1', '', $msg);
                }
            }
            $key = $d['p1_member_id'] ? 'm' . $d['p1_member_id'] : mb_strtolower($d['p1_last_name'] . ' ' . $d['p1_first_name']);
            if (isset($seen[$key])) {
                $errors[] = $d['p1_last_name'] . ' ' . $d['p1_first_name'] . ' est inscrit deux fois.';
            }
            $seen[$key] = true;
            $players[] = [
                'member_id' => $d['p1_member_id'], 'last_name' => $d['p1_last_name'], 'first_name' => $d['p1_first_name'],
                'club' => $d['p1_club'], 'license' => $d['p1_license'], 'distance' => $d['p1_distance'],
                'category_id' => $d['p1_category_id'], 'category' => $d['p1_category'], 'average_min' => $d['p1_average_min'],
            ];
        }
        if (count($players) < ScoreboardFinalModel::MIN_PLAYERS) {
            $errors[] = 'Une finale compte au moins ' . ScoreboardFinalModel::MIN_PLAYERS . ' joueurs.';
        }

        // Règles communes à tous les matchs, validées comme pour une rencontre
        [$rules, $e] = ScoreboardMatchModel::dataFromInput($post + ['p1_last_name' => '-', 'p1_first_name' => '-', 'p1_distance' => 1,
                                                                   'p2_last_name' => '-', 'p2_first_name' => '-', 'p2_distance' => 1]);
        $errors = array_merge($errors, $e);

        $tableIds = array_values(array_filter(array_map('intval', (array) ($post['table_ids'] ?? []))));

        $data = [
            'name'                  => $name,
            'scheduled_at'          => $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null,
            'discipline'            => $rules['discipline'],
            'format'                => $rules['format'],
            'innings_limit'         => $rules['innings_limit'],
            'unequal_innings'       => $rules['unequal_innings'],
            'shot_clock_enabled'    => $rules['shot_clock_enabled'],
            'shot_clock_seconds'    => $rules['shot_clock_seconds'],
            'shot_clock_extensions' => $rules['shot_clock_extensions'],
            'table_ids'             => $tableIds ? implode(',', $tableIds) : null,
        ];

        return [$data, $players, array_values(array_unique($errors))];
    }

    private function savePlayers(int $finalId, array $players): void
    {
        foreach ($players as $i => $p) {
            $this->players->insert($p + ['final_id' => $finalId, 'position' => $i + 1]);
        }
        // Joueurs externes mémorisés pour l'auto-complétion, comme pour les rencontres
        foreach ($players as $p) {
            if ($p['member_id'] === null) {
                ScoreboardMatchModel::rememberExternalPlayers([
                    'p1_member_id' => null, 'p1_last_name' => $p['last_name'], 'p1_first_name' => $p['first_name'],
                    'p1_club' => $p['club'], 'p1_license' => $p['license'], 'p1_category_id' => $p['category_id'],
                    'p2_member_id' => 0,
                ]);
            }
        }
    }

    private function tableNames(object $final): array
    {
        $names = [];
        $all   = [];
        foreach ((new ScoreboardTableModel())->findAll() as $t) {
            $all[(int) $t->id] = $t->name;
        }
        foreach (ScoreboardFinalModel::tableIds($final) as $id) {
            if (isset($all[$id])) {
                $names[] = $all[$id];
            }
        }
        return $names;
    }

    private function db()
    {
        return db_connect();
    }

    private function find(int $id): object
    {
        $final = $this->model->find($id);
        if (!$final) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $final;
    }
}
