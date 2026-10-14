<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\BarCallModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardTableModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class BarController extends BaseController
{
    // Couleur de l'encart selon l'attente (en secondes) et rappel sonore tant que l'appel n'est pas servi
    public const ORANGE_AFTER = 180;
    public const RED_AFTER    = 360;
    public const BEEP_EVERY   = 120;

    private BarCallModel $calls;
    private ScoreboardTableModel $tables;

    public function __construct()
    {
        $this->calls  = new BarCallModel();
        $this->tables = new ScoreboardTableModel();
    }

    public function index(): string
    {
        return view('public/bar/index', [
            'title'     => 'Bar',
            'brandSub'  => 'Bar',
            'noBarCall' => true,
            'config'    => [
                'state'       => base_url('marquoir/api/bar'),
                'serve'       => base_url('marquoir/api/bar/servi/'),
                'orangeAfter' => self::ORANGE_AFTER,
                'redAfter'    => self::RED_AFTER,
                'beepEvery'   => self::BEEP_EVERY,
            ],
        ]);
    }

    public function state()
    {
        $matches = new ScoreboardMatchModel();
        $pending = $this->calls->pendingByTable();
        $now     = ScoreboardMatchModel::nowTs();

        $tables = [];
        foreach ($this->tables->getActive() as $t) {
            $live = $matches->getLiveForTable((int) $t->id);
            $call = $pending[(int) $t->id] ?? null;
            $tables[] = [
                'id'      => (int) $t->id,
                'name'    => $t->name,
                'players' => $live ? [
                    trim($live->p1_first_name . ' ' . $live->p1_last_name),
                    trim($live->p2_first_name . ' ' . $live->p2_last_name),
                ] : null,
                'call'    => $call ? ['id' => (int) $call->id, 'waiting' => max(0, $now - strtotime($call->called_at))] : null,
            ];
        }

        return $this->response->setJSON(['tables' => $tables]);
    }

    public function call(int $tableId)
    {
        $table = $this->tables->find($tableId);
        if (!$table || !$table->is_active) {
            throw PageNotFoundException::forPageNotFound();
        }

        [$call, $created] = $this->calls->call($tableId);

        return $this->response->setJSON([
            'created' => $created,
            'waiting' => max(0, ScoreboardMatchModel::nowTs() - strtotime($call->called_at)),
        ]);
    }

    public function serve(int $callId)
    {
        $this->calls->serve($callId);
        return $this->response->setJSON(['ok' => true]);
    }
}
