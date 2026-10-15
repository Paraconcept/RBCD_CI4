<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\FrbbCategoryModel;
use App\Filters\ScoreboardDeviceFilter;
use App\Models\ScoreboardInningModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardPlayerModel;
use App\Models\ScoreboardSignatureModel;
use App\Models\ScoreboardTableModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ScoreboardController extends BaseController
{
    private ScoreboardMatchModel $matches;
    private ScoreboardTableModel $tables;
    private SiteSettingModel $settings;
    private ScoreboardSignatureModel $signatures;

    public function __construct()
    {
        $this->signatures = new ScoreboardSignatureModel();
        $this->matches  = new ScoreboardMatchModel();
        $this->tables   = new ScoreboardTableModel();
        $this->settings = new SiteSettingModel();
    }

    public function access()
    {
        if (ScoreboardDeviceFilter::isAuthorized()) {
            return redirect()->to(base_url('marquoir'));
        }

        return view('public/scoreboard/access', [
            'title'      => 'Accès au marquoir',
            'pinDefined' => (string) $this->settings->getSetting('scoreboard_pin', '') !== '',
        ]);
    }

    public function accessPost()
    {
        $pin = (string) $this->settings->getSetting('scoreboard_pin', '');
        if ($pin === '' || !hash_equals($pin, trim((string) $this->request->getPost('pin')))) {
            return redirect()->to(base_url('marquoir/acces'))->with('error', 'Code PIN incorrect.');
        }

        return redirect()->to(base_url('marquoir'))
            ->setCookie(ScoreboardDeviceFilter::COOKIE, ScoreboardDeviceFilter::tokenFor($pin), 5 * 365 * 86400);
    }

    public function index()
    {
        // Un Chromebook qui connaît son billard s'y ouvre directement (?tous=1 pour voir la liste)
        if (!$this->request->getGet('tous') && ($own = $this->deviceTable())) {
            return redirect()->to(base_url('marquoir/billard/' . $own->id));
        }

        $tables = $this->tables->getActive();
        foreach ($tables as $t) {
            $t->live = $this->matches->getLiveForTable((int) $t->id);
        }

        return view('public/scoreboard/index', [
            'title'  => 'Marquoirs',
            'tables' => $tables,
        ]);
    }

    public function table(int $tableId)
    {
        $table = $this->findTable($tableId);

        $live = $this->matches->getLiveForTable($tableId);
        if ($live) {
            return redirect()->to(base_url('marquoir/match/' . $live->id));
        }

        $this->matches->purgeOldFriendlies();
        $lastFinished = $this->matches->getRecentForTable($tableId);

        return view('public/scoreboard/table', [
            'title'        => $table->name,
            'table'        => $table,
            'startable'    => $this->matches->getStartableForTable($tableId),
            'upcoming'     => $this->matches->getUpcomingForTable($tableId),
            'lastFinished' => $lastFinished,
            'startWindow'  => $this->startWindow(),
            'deviceTable'  => $this->deviceTable(),
        ]);
    }

    /** « Ce Chromebook est le billard n° X » : retenu sur l'appareil pendant 5 ans. */
    public function rememberTable(int $tableId)
    {
        $table = $this->findTable($tableId);

        return redirect()->to(base_url('marquoir/billard/' . $table->id))
            ->setCookie(ScoreboardDeviceFilter::TABLE_COOKIE, (string) $table->id, 5 * 365 * 86400)
            ->with('success', 'Ce Chromebook est désormais « ' . $table->name . ' » : le marquoir s\'ouvrira directement ici.');
    }

    public function forgetTable()
    {
        return redirect()->to(base_url('marquoir?tous=1'))
            ->deleteCookie(ScoreboardDeviceFilter::TABLE_COOKIE)
            ->with('success', 'Ce Chromebook n\'est plus associé à un billard.');
    }

    private function deviceTable(): ?object
    {
        helper('cookie');
        $id = (int) get_cookie(ScoreboardDeviceFilter::TABLE_COOKIE);
        $table = $id > 0 ? $this->tables->find($id) : null;
        return $table && $table->is_active ? $table : null;
    }

    public function friendly(int $tableId, int $players = 2): string
    {
        return view('public/scoreboard/friendly', [
            'title'    => $players === 3 ? 'Partie amicale à 3' : 'Partie amicale',
            'table'    => $this->findTable($tableId),
            'defaults' => $this->defaults(),
            'players'  => $players === 3 ? 3 : 2,
        ]);
    }

    public function friendlyTrio(int $tableId): string
    {
        return $this->friendly($tableId, 3);
    }

    public function friendlyStore(int $tableId)
    {
        $this->findTable($tableId);

        [$data, $errors] = ScoreboardMatchModel::dataFromInput($this->request->getPost());
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data['type']     = 'friendly';
        $data['status']   = 'scheduled';
        // Réservé aux amicales : en officiel, c'est toujours l'arbitre qui lance le chrono.
        $data['shot_clock_auto_start'] = $data['shot_clock_enabled'] && $this->request->getPost('shot_clock_auto_start') ? 1 : 0;
        $data['table_id'] = $tableId;
        $id = $this->matches->insert($data);
        ScoreboardMatchModel::rememberExternalPlayers($data);

        return redirect()->to(base_url('marquoir/billard/' . $tableId . '/demarrer/' . $id));
    }

    public function start(int $tableId, int $matchId)
    {
        $table = $this->findTable($tableId);
        $match = $this->findMatch($matchId);
        if ($match->status !== 'scheduled') {
            return redirect()->to(base_url('marquoir/billard/' . $tableId));
        }
        if ($tooEarly = $this->tooEarly($match, $tableId)) {
            return $tooEarly;
        }

        return view('public/scoreboard/start', [
            'title'         => 'Qui commence la partie ?',
            'trio'          => ScoreboardMatchModel::isTrio($match),
            'table'         => $table,
            'match'         => $match,
            'warmupMinutes' => (int) $this->settings->getSetting('scoreboard_warmup_minutes', 5),
        ]);
    }

    public function startPost(int $tableId, int $matchId)
    {
        $this->findTable($tableId);
        $match = $this->findMatch($matchId);

        if ($match->status !== 'scheduled') {
            return redirect()->to(base_url('marquoir/billard/' . $tableId));
        }
        if ($tooEarly = $this->tooEarly($match, $tableId)) {
            return $tooEarly;
        }
        if ($this->matches->getLiveForTable($tableId)) {
            return redirect()->to(base_url('marquoir/billard/' . $tableId))
                             ->with('error', 'Une partie est déjà en cours sur ce billard.');
        }

        $order = [];
        if (ScoreboardMatchModel::isTrio($match)) {
            $order = explode(',', (string) $this->request->getPost('order'));
            if (!ScoreboardMatchModel::validOrder($order)) {
                return redirect()->to(base_url('marquoir/billard/' . $tableId . '/demarrer/' . $matchId));
            }
        }
        $starter = (int) $this->request->getPost('starting_player') === 2 ? 2 : 1;
        $this->matches->startWith($match, $starter, $tableId, $order);

        return redirect()->to(base_url('marquoir/match/' . $matchId));
    }

    public function display(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (in_array($match->status, ['scheduled', 'cancelled'], true)) {
            return redirect()->to(base_url($match->table_id ? 'marquoir/billard/' . $match->table_id : 'marquoir'));
        }

        return view('public/scoreboard/display', [
            'title' => 'Marquoir',
            'match' => $match,
            'table' => $match->table_id ? $this->tables->find($match->table_id) : null,
            'state'  => $this->matches->stateFor($match),
            'signed' => $this->signatures->isSigned((int) $match->id),
        ]);
    }

    public function swap(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (ScoreboardMatchModel::isTrio($match)) {
            if ($this->matches->backToOrderChoice($match)) {
                return redirect()->to(base_url('marquoir/billard/' . $match->table_id . '/demarrer/' . $matchId));
            }
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }
        $this->matches->swapPlayers($match);

        return redirect()->to(base_url('marquoir/match/' . $matchId));
    }

    /**
     * Amicale terminée : nouvelle partie avec les mêmes joueurs et les mêmes règles,
     * qui repasse par « Qui commence ? » (et l'échauffement).
     */
    public function rematch(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if ($match->type !== 'friendly' || $match->status !== 'finished') {
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }

        $copy = [
            'competition', 'discipline', 'format', 'innings_limit', 'unequal_innings', 'sets_to_win',
            'shot_clock_enabled', 'shot_clock_seconds', 'shot_clock_extensions', 'shot_clock_auto_start', 'created_by',
        ];
        foreach (ScoreboardMatchModel::playerKeys($match) as $p) {
            foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'category_id', 'category', 'average_min', 'distance'] as $f) {
                $copy[] = $p . '_' . $f;
            }
        }

        $data = ['type' => 'friendly', 'status' => 'scheduled', 'table_id' => $match->table_id];
        foreach ($copy as $f) {
            $data[$f] = $match->{$f};
        }
        $id = $this->matches->insert($data);

        return redirect()->to(base_url('marquoir/billard/' . $match->table_id . '/demarrer/' . $id));
    }

    /**
     * Rencontre officielle terminée : signature au doigt des deux joueurs et de l'arbitre,
     * reprises sur la feuille de match PDF. Facultative, et définitive une fois enregistrée.
     */
    public function sign(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (!$this->signable($match)) {
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }

        return view('public/scoreboard/sign', [
            'title' => 'Signer la feuille de match',
            'match' => $match,
            'table' => $match->table_id ? $this->tables->find($match->table_id) : null,
        ]);
    }

    public function signPost(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (!$this->signable($match)) {
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }

        $referee = trim(preg_replace('/\s+/', ' ', (string) $this->request->getPost('referee_name')));
        $data    = [
            'match_id'          => $matchId,
            'referee_name'      => mb_substr($referee, 0, 100),
            'p1_signature'      => (string) $this->request->getPost('p1_signature'),
            'p2_signature'      => (string) $this->request->getPost('p2_signature'),
            'referee_signature' => (string) $this->request->getPost('referee_signature'),
            'signed_at'         => ScoreboardMatchModel::now(),
        ];

        $errors = [];
        if ($referee === '') {
            $errors[] = "Le nom de l'arbitre est obligatoire.";
        }
        foreach (['p1_signature', 'p2_signature', 'referee_signature'] as $f) {
            if (!ScoreboardSignatureModel::isValidImage($data[$f])) {
                $errors[] = 'Les trois signatures sont obligatoires.';
                break;
            }
        }
        // Pas de withInput() : les images ne doivent pas transiter par la session (le formulaire les vérifie déjà)
        if ($errors) {
            return redirect()->to(base_url('marquoir/match/' . $matchId . '/signer'))->with('errors', $errors);
        }

        $this->signatures->insert($data);

        return redirect()->to(base_url($match->table_id ? 'marquoir/billard/' . $match->table_id : 'marquoir'))
                         ->with('success', 'Feuille de match signée.');
    }

    private function signable(object $match): bool
    {
        return $match->type === 'official' && $match->status === 'finished' && $match->signature_required
            && !ScoreboardMatchModel::isTrio($match) && !$this->signatures->isSigned((int) $match->id);
    }

    /**
     * Correction des joueurs ou des points d'une partie en cours, sans perdre les reprises jouées.
     */
    public function edit(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (!in_array($match->status, ['live', 'finished'], true) || $this->signatures->isSigned($matchId)) {
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }

        return view('public/scoreboard/edit', [
            'title'      => 'Modifier la partie en cours',
            'match'      => $match,
            'table'      => $match->table_id ? $this->tables->find($match->table_id) : null,
            'categories' => (new FrbbCategoryModel())->forScoreboard(),
            'scored'     => $this->matches->pointsInPlay($match),
        ]);
    }

    public function update(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (!in_array($match->status, ['live', 'finished'], true) || $this->signatures->isSigned($matchId)) {
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }

        // Discipline, format et règles restent ceux de la partie : seuls joueurs, points et limite changent
        $in = array_merge($this->request->getPost(), [
            'discipline'            => $match->discipline,
            'format'                => $match->format,
            'sets_enabled'          => ScoreboardMatchModel::isSets($match) ? 1 : 0,
            'sets_to_win'           => $match->sets_to_win,
            'unequal_innings'       => $match->unequal_innings,
            'shot_clock_enabled'    => $match->shot_clock_enabled,
            'shot_clock_seconds'    => $match->shot_clock_seconds,
            'shot_clock_extensions' => $match->shot_clock_extensions,
            'table_id'              => $match->table_id,
            'players'               => ScoreboardMatchModel::playerCount($match),
        ]);
        [$data, $errors] = ScoreboardMatchModel::dataFromInput($in);

        $scored = $this->matches->pointsInPlay($match);
        foreach (ScoreboardMatchModel::playerNumbers($match) as $n) {
            if ($data['p' . $n . '_distance'] < $scored[$n]) {
                $errors[] = 'Le joueur ' . $n . ' a déjà ' . $scored[$n] . ' points : ses points à réaliser ne peuvent pas être inférieurs.';
            }
        }
        $played = max(array_map(static fn (string $p): int => (int) $match->{$p . '_innings'}, ScoreboardMatchModel::playerKeys($match)));
        if ($data['innings_limit'] !== null && $data['innings_limit'] < $played) {
            $errors[] = $played . ' reprises sont déjà jouées : la limite ne peut pas être inférieure.';
        }
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $update = [];
        foreach (ScoreboardMatchModel::playerKeys($match) as $p) {
            foreach (['member_id', 'last_name', 'first_name', 'club', 'license', 'distance', 'category_id', 'category', 'average_min'] as $f) {
                $update[$p . '_' . $f] = $data[$p . '_' . $f];
            }
        }
        if (!ScoreboardMatchModel::isSets($match)) {
            $update['innings_limit'] = $data['innings_limit'];
        }
        if ($match->status === 'finished' && ScoreboardMatchModel::isTrio($match)) {
            $after = (object) array_merge((array) $match, $update);
            $first = array_keys(ScoreboardMatchModel::storedRanking($after), 1, true);
            $update['winner'] = count($first) === 1 ? $first[0] : 0;
        } elseif ($match->status === 'finished' && !ScoreboardMatchModel::isSets($match)) {
            $after = (object) array_merge((array) $match, $update);
            $update['winner'] = ScoreboardMatchModel::winnerOf($after, (int) $match->p1_points, (int) $match->p1_innings, (int) $match->p2_points, (int) $match->p2_innings);
        }
        $this->matches->update($matchId, $update);
        ScoreboardMatchModel::rememberExternalPlayers($data);

        return redirect()->to(base_url('marquoir/match/' . $matchId));
    }

    public function cancel(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if ($this->signatures->isSigned($matchId)) {
            return redirect()->to(base_url('marquoir/match/' . $matchId));
        }
        $this->matches->resetOrCancel($match);

        return redirect()->to(base_url($match->table_id ? 'marquoir/billard/' . $match->table_id : 'marquoir'))
                         ->with('success', 'Partie annulée.');
    }

    public function searchPlayers()
    {
        $players = new ScoreboardPlayerModel();
        return $this->response->setJSON($players->search((string) $this->request->getGet('q')));
    }

    public function state(int $matchId)
    {
        return $this->response->setJSON($this->matches->stateFor($this->findMatch($matchId)));
    }

    public function saveState(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (!in_array($match->status, ['live', 'finished'], true)) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Partie non démarrée ou annulée']);
        }
        if ($this->signatures->isSigned($matchId)) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Feuille de match signée']);
        }

        $rev = $this->matches->applyState($match, (array) $this->request->getJSON(true));

        return $this->response->setJSON(['rev' => $rev]);
    }

    private function startWindow(): int
    {
        return (int) $this->settings->getSetting('scoreboard_start_window_minutes', 60);
    }

    /**
     * Empêche de démarrer une rencontre programmée trop tôt (ex. un joueur qui s'entraîne l'après-midi).
     */
    private function tooEarly(object $match, int $tableId)
    {
        if (ScoreboardMatchModel::canStartNow($match, $this->startWindow())) {
            return null;
        }

        $from = ScoreboardMatchModel::startAllowedAt($match, $this->startWindow());
        $when = date('Y-m-d', $from) === ScoreboardMatchModel::now('Y-m-d') ? 'de ' . date('H:i', $from) : 'du ' . date('d/m/Y à H:i', $from);

        return redirect()->to(base_url('marquoir/billard/' . $tableId))
                         ->with('error', "Cette rencontre ne pourra être démarrée qu'à partir " . $when . '.');
    }

    private function defaults(): array
    {
        return [
            'shot_clock_seconds'    => (int) $this->settings->getSetting('scoreboard_shot_clock_seconds', 40),
            'shot_clock_extensions' => (int) $this->settings->getSetting('scoreboard_shot_clock_extensions', 3),
            'innings_limit_3c'   => (int) $this->settings->getSetting('scoreboard_innings_limit_3c', 60),
            'set_points'         => ScoreboardMatchModel::DEFAULT_SET_POINTS,
            'sets_to_win'        => ScoreboardMatchModel::DEFAULT_SETS_TO_WIN,
        ];
    }

    private function findTable(int $id): object
    {
        $table = $this->tables->find($id);
        if (!$table || !$table->is_active) {
            throw PageNotFoundException::forPageNotFound('Billard introuvable');
        }
        return $table;
    }

    private function findMatch(int $id): object
    {
        $match = $this->matches->withTable()->find($id);
        if (!$match) {
            throw PageNotFoundException::forPageNotFound('Partie introuvable');
        }
        return $match;
    }
}
