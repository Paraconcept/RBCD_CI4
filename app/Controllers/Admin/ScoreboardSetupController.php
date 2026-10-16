<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ScoreboardPlayerModel;
use App\Models\ScoreboardTableModel;
use App\Models\SiteSettingModel;

class ScoreboardSetupController extends BaseController
{
    public function tables(): string
    {
        return view('admin/scoreboard/tables', [
            'title'       => 'Marquoir — Billards',
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => 'Billards']],
            'tables'      => (new ScoreboardTableModel())->orderBy('sort_order')->orderBy('name')->findAll(),
        ]);
    }

    public function storeTable()
    {
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->with('error', 'Le nom du billard est obligatoire.');
        }

        $model = new ScoreboardTableModel();
        $model->insert([
            'name'       => $name,
            'format'     => $this->request->getPost('format') === 'GB' ? 'GB' : 'PB',
            'sort_order' => (int) $model->selectMax('sort_order')->first()->sort_order + 1,
            'is_active'  => 1,
        ]);

        return redirect()->to(base_url('admin/scoreboard/tables'))->with('success', 'Billard ajouté.');
    }

    public function updateTable(int $id)
    {
        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->with('error', 'Le nom du billard est obligatoire.');
        }

        (new ScoreboardTableModel())->update($id, [
            'name'       => $name,
            'format'     => $this->request->getPost('format') === 'GB' ? 'GB' : 'PB',
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/scoreboard/tables'))->with('success', 'Billard modifié.');
    }

    public function deleteTable(int $id)
    {
        (new ScoreboardTableModel())->delete($id);

        return redirect()->to(base_url('admin/scoreboard/tables'))->with('success', 'Billard supprimé.');
    }

    public function players(): string
    {
        return view('admin/scoreboard/players', [
            'title'       => 'Marquoir — Joueurs externes',
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => 'Joueurs externes']],
            'players'     => (new ScoreboardPlayerModel())->orderBy('last_name')->orderBy('first_name')->findAll(),
        ]);
    }

    public function updatePlayer(int $id)
    {
        $last  = mb_strtoupper(trim((string) $this->request->getPost('last_name')), 'UTF-8');
        $first = trim((string) $this->request->getPost('first_name'));
        $club  = trim((string) $this->request->getPost('club'));
        if ($last === '' || $first === '') {
            return redirect()->back()->with('error', 'Nom et prénom obligatoires.');
        }

        try {
            (new ScoreboardPlayerModel())->update($id, ['last_name' => $last, 'first_name' => $first, 'club' => $club !== '' ? $club : null]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Ce joueur existe déjà.');
        }

        return redirect()->to(base_url('admin/scoreboard/players'))->with('success', 'Joueur modifié.');
    }

    public function deletePlayer(int $id)
    {
        (new ScoreboardPlayerModel())->delete($id);

        return redirect()->to(base_url('admin/scoreboard/players'))->with('success', 'Joueur supprimé.');
    }

    /**
     * Guide du directeur sportif : rangé hors du dossier public, servi uniquement aux membres connectés.
     */
    public function howto()
    {
        $path = APPPATH . 'Docs/Marquoirs_RBCD_guide_DS.pdf';
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="Marquoirs_RBCD_guide_DS.pdf"')
            ->setBody(file_get_contents($path));
    }

    public function settings(): string
    {
        $s = new SiteSettingModel();

        return view('admin/scoreboard/settings', [
            'title'       => 'Marquoir — Paramètres',
            'breadcrumbs' => [['title' => 'Marquoir', 'url' => base_url('admin/scoreboard')], ['title' => 'Paramètres']],
            'shotClockSeconds'    => (int) $s->getSetting('scoreboard_shot_clock_seconds', 40),
            'shotClockExtensions' => (int) $s->getSetting('scoreboard_shot_clock_extensions', 3),
            'inningsLimit3c'      => (int) $s->getSetting('scoreboard_innings_limit_3c', 60),
            'warmupMinutes'       => (int) $s->getSetting('scoreboard_warmup_minutes', 5),
            'startWindowMinutes'  => (int) $s->getSetting('scoreboard_start_window_minutes', 60),
            'clubEmail'           => (string) $s->getSetting('scoreboard_club_email', 'ds@rbcd.be'),
            'cdrName'             => (string) $s->getSetting('scoreboard_cdr_name', 'Christian JEUNEAU'),
            'cdrEmail'            => (string) $s->getSetting('scoreboard_cdr_email', 'cdregions@gmail.com'),
            'dsr'                 => \App\Libraries\MatchSheetPdf::regionalDirector(),
            'pin'                 => (string) $s->getSetting('scoreboard_pin', ''),
        ]);
    }

    public function saveSettings()
    {
        $s   = new SiteSettingModel();
        $pin = trim((string) $this->request->getPost('pin'));
        if ($pin !== '' && !preg_match('/^\d{4,8}$/', $pin)) {
            return redirect()->back()->with('error', 'Le code PIN doit contenir de 4 à 8 chiffres.');
        }
        $clubEmail = trim((string) $this->request->getPost('club_email'));
        if ($clubEmail !== '' && !filter_var($clubEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', "L'adresse e-mail du club est invalide.");
        }
        $cdrEmail = trim((string) $this->request->getPost('cdr_email'));
        if ($cdrEmail !== '' && !filter_var($cdrEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', "L'adresse e-mail du responsable CDR est invalide.");
        }
        $dsrEmail = trim((string) $this->request->getPost('dsr_email'));
        if ($dsrEmail !== '' && !filter_var($dsrEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', "L'adresse e-mail du directeur sportif régional est invalide.");
        }

        $s->setSetting('scoreboard_shot_clock_seconds', max(5, min(300, (int) $this->request->getPost('shot_clock_seconds'))));
        $s->setSetting('scoreboard_shot_clock_extensions', max(0, min(10, (int) $this->request->getPost('shot_clock_extensions'))));
        $s->setSetting('scoreboard_innings_limit_3c', max(1, min(999, (int) $this->request->getPost('innings_limit_3c'))));
        $s->setSetting('scoreboard_warmup_minutes', max(1, min(15, (int) $this->request->getPost('warmup_minutes'))));
        $s->setSetting('scoreboard_club_email', $clubEmail);
        $s->setSetting('scoreboard_cdr_name', trim((string) $this->request->getPost('cdr_name')));
        $s->setSetting('scoreboard_cdr_email', $cdrEmail);
        $s->setSetting('scoreboard_dsr_name', mb_substr(trim((string) $this->request->getPost('dsr_name')), 0, 100));
        $s->setSetting('scoreboard_dsr_email', $dsrEmail);
        $s->setSetting('scoreboard_dsr_mobile', mb_substr(trim((string) $this->request->getPost('dsr_mobile')), 0, 30));
        $s->setSetting('scoreboard_start_window_minutes', max(0, min(720, (int) $this->request->getPost('start_window_minutes'))));
        $s->setSetting('scoreboard_pin', $pin);

        return redirect()->to(base_url('admin/scoreboard/settings'))->with('success', 'Paramètres du marquoir enregistrés.');
    }
}
