<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Filters\ScoreboardDeviceFilter;
use App\Models\ScoreboardInningModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardPlayerModel;
use App\Models\ScoreboardTableModel;
use App\Models\SiteSettingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ScoreboardController extends BaseController
{
    private ScoreboardMatchModel $matches;
    private ScoreboardTableModel $tables;
    private SiteSettingModel $settings;

    public function __construct()
    {
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

    public function index(): string
    {
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

        $lastFinished = $this->matches->where('table_id', $tableId)->where('status', 'finished')
                                      ->orderBy('finished_at', 'DESC')->findAll(5);

        return view('public/scoreboard/table', [
            'title'        => $table->name,
            'table'        => $table,
            'startable'    => $this->matches->getStartableForTable($tableId),
            'upcoming'     => $this->matches->getUpcomingForTable($tableId),
            'lastFinished' => $lastFinished,
            'startWindow'  => $this->startWindow(),
        ]);
    }

    public function friendly(int $tableId): string
    {
        return view('public/scoreboard/friendly', [
            'title'    => 'Partie amicale',
            'table'    => $this->findTable($tableId),
            'defaults' => $this->defaults(),
        ]);
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

        $starter = (int) $this->request->getPost('starting_player') === 2 ? 2 : 1;
        $this->matches->startWith($match, $starter, $tableId);

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
            'state' => $this->stateFor($match),
        ]);
    }

    public function cancel(int $matchId)
    {
        $match = $this->findMatch($matchId);
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
        return $this->response->setJSON($this->stateFor($this->findMatch($matchId)));
    }

    public function saveState(int $matchId)
    {
        $match = $this->findMatch($matchId);
        if (!in_array($match->status, ['live', 'finished'], true)) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'Partie non démarrée ou annulée']);
        }

        $rev = $this->matches->applyState($match, (array) $this->request->getJSON(true));

        return $this->response->setJSON(['rev' => $rev]);
    }

    private function stateFor(object $match): array
    {
        $rows    = (new ScoreboardInningModel())->getForMatch((int) $match->id);
        $starter = (int) ($match->starting_player ?: 1);
        $byPlayer = [1 => [], 2 => []];
        foreach ($rows as $r) {
            $byPlayer[(int) $r->player][(int) $r->inning_no] = (int) $r->points;
        }

        $innings = [];
        $other   = $starter === 1 ? 2 : 1;
        $count   = max(count($byPlayer[1]), count($byPlayer[2]));
        for ($n = 1; $n <= $count; $n++) {
            if (isset($byPlayer[$starter][$n])) $innings[] = $byPlayer[$starter][$n];
            if (isset($byPlayer[$other][$n]))   $innings[] = $byPlayer[$other][$n];
        }

        return [
            'rev'        => (int) $match->rev,
            'status'     => $match->status,
            'innings'    => $innings,
            'current_run' => (int) $match->current_run,
            'extensions' => [(int) $match->p1_extensions_used, (int) $match->p2_extensions_used],
        ];
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
