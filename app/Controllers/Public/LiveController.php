<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\ScoreboardMatchModel;

/**
 * Suivi des parties en direct depuis chez soi : lecture seule, sans code PIN.
 */
class LiveController extends BaseController
{
    private ScoreboardMatchModel $matches;

    public function __construct()
    {
        $this->matches = new ScoreboardMatchModel();
    }

    public function index(): string
    {
        return view('public/scoreboard/live_index', [
            'title'    => 'En direct',
            'brandUrl' => base_url('en-direct'),
            'brandSub' => 'En direct',
            'matches'  => $this->matches->getTodayForSpectators(),
        ]);
    }

    public function watch(int $matchId)
    {
        $match = $this->matches->withTable()->find($matchId);
        if (!$match || $match->status === 'cancelled' || $match->type !== 'official') {
            return redirect()->to(base_url('en-direct'))->with('error', "Cette partie n'est pas disponible.");
        }

        if ($match->status === 'scheduled') {
            return view('public/scoreboard/live_waiting', [
                'title'    => 'En direct',
                'brandUrl' => base_url('en-direct'),
                'brandSub' => 'En direct',
                'match'    => $match,
            ]);
        }

        return view('public/scoreboard/display', [
            'title'     => 'En direct',
            'match'     => $match,
            'table'     => null,
            'state'     => $this->matches->stateFor($match),
            'spectator' => true,
        ]);
    }

    public function state(int $matchId)
    {
        $match = $this->matches->find($matchId);
        if (!$match || $match->status === 'cancelled' || $match->type !== 'official') {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'cancelled']);
        }

        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON($this->matches->stateFor($match));
    }
}
