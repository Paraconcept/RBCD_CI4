<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\SkittlesPoolMailer;
use App\Libraries\SkittlesPoolPdf;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardSkittlesPoolModel as P;
use App\Models\ScoreboardSkittlesSignatureModel;
use App\Models\ScoreboardTableModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Soirées Poule 5 Quilles encodées par le DS : trois joueurs, trois matchs générés, feuille récapitulative.
 */
class ScoreboardSkittlesController extends BaseController
{
    private P $model;

    public function __construct()
    {
        $this->model = new P();
    }

    public function index(): string
    {
        return view('admin/scoreboard/skittles/index', [
            'title'       => 'Marquoir — ' . P::NAME,
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => 'Poules 5 Quilles']],
            'pools'       => $this->model->withProgress()->orderBy('scheduled_at', 'DESC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function store()
    {
        [$data, $errors] = $this->buildData(false);
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data['created_by'] = session()->get('member_id');
        $id = $this->model->insert($data);
        $this->model->generateMatches($this->model->find($id), session()->get('member_id'));
        $this->rememberPlayers($data);

        return redirect()->to(base_url('admin/scoreboard/skittles/' . $id))->with('success', P::NAME . ' créée : 3 matchs générés (B/C, C/A, A/B).');
    }

    public function show(int $id): string
    {
        $pool = $this->find($id);

        return view('admin/scoreboard/skittles/show', [
            'title'       => P::NAME . ' — ' . $pool->name,
            'breadcrumbs' => [
                ['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')],
                ['title' => 'Poules 5 Quilles', 'url' => base_url('admin/scoreboard/skittles')],
                ['title' => $pool->name],
            ],
            'pool'       => $pool,
            'sheet'      => $this->model->sheet($pool),
            'table'      => $pool->table_id ? (new ScoreboardTableModel())->find($pool->table_id) : null,
            'signedAt'   => (new ScoreboardSkittlesSignatureModel())->signedAt($id),
            'recipients' => SkittlesPoolMailer::recipients($pool),
            'progress'   => SkittlesPoolMailer::progress($pool),
        ]);
    }

    public function edit(int $id): string
    {
        return $this->form($this->find($id));
    }

    public function update(int $id)
    {
        $pool    = $this->find($id);
        $started = $this->model->hasStarted($id);
        [$data, $errors] = $this->buildData($started);
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->model->update($id, $data);
        $pool = $this->model->find($id);
        if (!$started) {
            $this->model->generateMatches($pool, session()->get('member_id'));
            $this->rememberPlayers($data);
        } else {
            // Poule commencée : joueurs et points figés ; nom, date et billard suivent sur les matchs pas encore joués
            $this->db()->table('scoreboard_matches')->where('skittles_pool_id', $id)
                ->update(['competition' => P::NAME . ' — ' . $pool->name]);
            $this->db()->table('scoreboard_matches')->where('skittles_pool_id', $id)->where('status', 'scheduled')
                ->update(['scheduled_at' => $pool->scheduled_at, 'table_id' => $pool->table_id]);
        }

        return redirect()->to(base_url('admin/scoreboard/skittles/' . $id))->with('success', P::NAME . ' modifiée.');
    }

    public function delete(int $id)
    {
        $pool = $this->find($id);
        if ($this->model->hasStarted($id)) {
            return redirect()->back()->with('error', 'Cette poule a déjà commencé : elle ne peut plus être supprimée.');
        }
        $this->model->delete($pool->id);

        return redirect()->to(base_url('admin/scoreboard/skittles'))->with('success', P::NAME . ' supprimée.');
    }

    public function comment(int $id)
    {
        $pool    = $this->find($id);
        $comment = mb_substr(trim((string) $this->request->getPost('comment')), 0, 1000);
        $this->model->update($pool->id, ['comment' => $comment !== '' ? $comment : null]);

        return redirect()->to(base_url('admin/scoreboard/skittles/' . $id) . '#commentaire')->with('success', 'Commentaire enregistré.');
    }

    /**
     * Forfait d'un match (choisi par le DS) : le joueur absent reste dans la poule avec 0 partout,
     * son adversaire reçoit les points de match du forfait. Uniquement sur un match pas encore joué.
     */
    public function forfeit(int $id, int $matchId)
    {
        $pool  = $this->find($id);
        $back  = redirect()->to(base_url('admin/scoreboard/skittles/' . $id));
        $model = new ScoreboardMatchModel();
        $match = $model->where('skittles_pool_id', $pool->id)->find($matchId);
        $slot  = (int) $this->request->getPost('slot');
        if (!$match || !in_array($slot, [(int) $match->p1_pool_slot, (int) $match->p2_pool_slot], true)) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($match->status !== 'scheduled' && !(int) $match->absent) {
            return $back->with('error', 'Ce match a déjà été joué : remettez-le à zéro avant de déclarer un forfait.');
        }

        // Absents cumulés : les deux joueurs peuvent être absents du même match
        $absent = (int) $match->absent | ((int) $match->p1_pool_slot === $slot ? 1 : 2);
        $model->update($match->id, ScoreboardMatchModel::forfeitData($absent, null));

        return $back->with('success', 'Forfait de ' . P::playerName($pool, $slot) . ' (joueur ' . P::letter($slot) . ') au match n°' . P::matchNumber($match) . '.');
    }

    public function cancelForfeit(int $id, int $matchId)
    {
        $pool  = $this->find($id);
        $model = new ScoreboardMatchModel();
        $match = $model->where('skittles_pool_id', $pool->id)->find($matchId);
        if (!$match) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ((int) $match->absent) {
            $model->resetToScheduled((int) $match->id);
        }

        return redirect()->to(base_url('admin/scoreboard/skittles/' . $id))->with('success', 'Forfait annulé : le match n°' . P::matchNumber($match) . ' est de nouveau à jouer.');
    }

    public function pdf(int $id)
    {
        [$pdf, $filename] = SkittlesPoolPdf::render($this->find($id));

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    /** Porte de secours : envoi ou renvoi manuel, sans exiger les signatures. */
    public function send(int $id)
    {
        $pool = $this->find($id);
        $back = redirect()->to(base_url('admin/scoreboard/skittles/' . $id));
        try {
            $sent = SkittlesPoolMailer::send($pool);
        } catch (\Throwable $e) {
            return $back->with('error', $e->getMessage());
        }

        return $back->with('success', 'Feuille récapitulative envoyée à ' . implode(', ', $sent) . '.');
    }

    private function form(?object $pool): string
    {
        return view('admin/scoreboard/skittles/form', [
            'title'       => $pool ? 'Modifier la poule' : 'Nouvelle ' . P::NAME,
            'breadcrumbs' => [
                ['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')],
                ['title' => 'Poules 5 Quilles', 'url' => base_url('admin/scoreboard/skittles')],
                ['title' => $pool ? 'Modifier' : 'Nouvelle'],
            ],
            'pool'     => $pool,
            'started'  => $pool ? $this->model->hasStarted((int) $pool->id) : false,
            'tables'   => (new ScoreboardTableModel())->getActive(),
            'regional' => P::regional(),
        ]);
    }

    /**
     * Retourne [données, erreurs]. Poule commencée ($locked) : joueurs, billard et points restent ceux de la poule.
     */
    private function buildData(bool $locked): array
    {
        $post   = $this->request->getPost();
        $errors = [];

        $name = mb_substr(trim((string) ($post['name'] ?? '')), 0, 150);
        if ($name === '') {
            $errors[] = 'Le nom de la poule est obligatoire (ex. « Série 2 »).';
        }
        $date = trim((string) ($post['scheduled_date'] ?? ''));
        if ($date === '') {
            $errors[] = 'La date de la soirée est obligatoire.';
        }
        $time    = trim((string) ($post['scheduled_time'] ?? '')) ?: '19:30';
        $tableId = (int) ($post['table_id'] ?? 0);
        if ($tableId > 0 && !(new ScoreboardTableModel())->find($tableId)) {
            $errors[] = 'Billard inconnu.';
        }

        $data = [
            'name'               => $name,
            'scheduled_at'       => $date !== '' ? date('Y-m-d H:i:s', strtotime($date . ' ' . $time)) : null,
            'table_id'           => $tableId > 0 ? $tableId : null,
            'signature_required' => !empty($post['signature_required']) ? 1 : 0,
        ];

        foreach (['regional' => 'responsable régional', 'club_ds' => 'DS du club'] as $key => $label) {
            $flag    = 'send_to_' . $key;
            $field   = $key . '_email';
            $address = mb_strtolower(trim((string) ($post[$field] ?? '')));
            $data[$flag]  = !empty($post[$flag]) ? 1 : 0;
            $data[$field] = $address !== '' ? mb_substr($address, 0, 150) : null;
            if ($address !== '' && !filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse e-mail du {$label} est invalide.";
            }
        }

        if ($locked) {
            return [$data, $errors];
        }

        $billiard = (string) ($post['billiard'] ?? '');
        if (!isset(P::BILLIARDS[$billiard])) {
            $errors[] = 'Choisissez le billard (2,30 ou 2,84).';
            $billiard = '284';
        }
        $points = (int) ($post['set_points'] ?? 0) ?: P::BILLIARDS[$billiard]['points'];
        if ($points < 1 || $points > 999) {
            $errors[] = "Les points d'un set doivent être compris entre 1 et 999.";
        }
        $data['billiard']   = $billiard;
        $data['set_points'] = $points;

        $seen = [];
        foreach (P::SLOTS as $slot => $letter) {
            $k     = P::key($slot);
            $last  = mb_strtoupper(trim((string) ($post[$k . '_last_name'] ?? '')), 'UTF-8');
            $first = trim((string) ($post[$k . '_first_name'] ?? ''));
            $club  = mb_strtoupper(trim((string) ($post[$k . '_club'] ?? '')), 'UTF-8');
            $lic   = mb_substr(trim((string) ($post[$k . '_license'] ?? '')), 0, 25);
            $mid   = (int) ($post[$k . '_member_id'] ?? 0);
            if ($last === '' || $first === '') {
                $errors[] = 'Nom et prénom du joueur ' . $letter . ' obligatoires.';
            }
            $who = $mid > 0 ? 'm' . $mid : mb_strtolower($last . ' ' . $first);
            if ($last !== '' && isset($seen[$who])) {
                $errors[] = $last . ' ' . $first . ' est inscrit deux fois.';
            }
            $seen[$who] = true;
            $data += [
                $k . '_member_id' => $mid > 0 ? $mid : null, $k . '_last_name' => mb_substr($last, 0, 100),
                $k . '_first_name' => mb_substr($first, 0, 100), $k . '_club' => $club !== '' ? mb_substr($club, 0, 100) : null,
                $k . '_license' => $lic !== '' ? $lic : null,
            ];
        }

        return [$data, array_values(array_unique($errors))];
    }

    /** Joueurs externes mémorisés pour l'auto-complétion, comme pour les rencontres. */
    private function rememberPlayers(array $data): void
    {
        foreach (P::SLOTS as $slot => $letter) {
            $k = P::key($slot);
            if (($data[$k . '_member_id'] ?? null) === null && isset($data[$k . '_last_name'])) {
                ScoreboardMatchModel::rememberExternalPlayers([
                    'p1_member_id' => null, 'p1_last_name' => $data[$k . '_last_name'], 'p1_first_name' => $data[$k . '_first_name'],
                    'p1_club' => $data[$k . '_club'], 'p1_license' => $data[$k . '_license'], 'p2_member_id' => 0,
                ]);
            }
        }
    }

    private function db()
    {
        return db_connect();
    }

    private function find(int $id): object
    {
        $pool = $this->model->find($id);
        if (!$pool) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $pool;
    }
}
