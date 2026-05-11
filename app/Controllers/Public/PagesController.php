<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class PagesController extends BaseController
{
    // ── Le Club ──────────────────────────────────────────────────────────

    public function clubHistoire(): string
    {
        return view('public/pages/club_histoire', [
            'title'       => 'Histoire & présentation — RBC Disonais',
            'page_title'  => 'Histoire & présentation',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Le Club', 'url' => '#'],
                ['label' => 'Historique'],
            ],
        ]);
    }

    public function clubComite(): string
    {
        $db = \Config\Database::connect();

        $users = $db->table('admin_users au')
                    ->select('au.id, au.first_name, au.last_name, au.member_id, m.photo, m.gender')
                    ->join('members m', 'm.id = au.member_id', 'left')
                    ->where('au.is_active', 1)
                    ->get()->getResultObject();

        $allRoles = $db->table('admin_user_roles')
                       ->select('admin_user_id, role')
                       ->orderBy('admin_user_id', 'ASC')
                       ->orderBy('sort_order', 'ASC')
                       ->get()->getResultObject();

        $rolesMap = [];
        foreach ($allRoles as $r) {
            $rolesMap[$r->admin_user_id][] = $r->role;
        }

        $publicOrder = array_flip([
            'Président', 'Vice-Président', 'Secrétaire', 'Secrétaire Adjoint',
            'Directeur Sportif', 'Directeur Sportif Adjoint',
            'Trésorier', 'Trésorier Adjoint', 'Commissaire', 'PR & Communication', 'Webmaster',
        ]);

        usort($users, function ($a, $b) use ($rolesMap, $publicOrder) {
            $minA = min(array_map(fn($r) => $publicOrder[$r] ?? 99, $rolesMap[$a->id] ?? []));
            $minB = min(array_map(fn($r) => $publicOrder[$r] ?? 99, $rolesMap[$b->id] ?? []));
            return $minA - $minB;
        });

        return view('public/pages/club_comite', [
            'title'       => 'Notre Comité — RBC Disonais',
            'page_title'  => 'Notre Comité',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Le Club', 'url' => '#'],
                ['label' => 'Notre Comité'],
            ],
            'members'  => $users,
            'rolesMap' => $rolesMap,
        ]);
    }

    public function clubMembres(): string
    {
        $model   = new \App\Models\MemberModel();
        $members = $model->where('is_active', 1)
                         ->orderBy('last_name', 'ASC')
                         ->orderBy('first_name', 'ASC')
                         ->findAll();

        $totalFederated = count(array_filter($members, fn($m) => (bool) $m->is_federated));

        return view('public/pages/club_membres', [
            'title'          => 'Nos Membres — RBC Disonais',
            'page_title'     => 'Nos Membres',
            'breadcrumbs'    => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Le Club', 'url' => '#'],
                ['label' => 'Nos Membres'],
            ],
            'members'        => $members,
            'totalFederated' => $totalFederated,
        ]);
    }

    public function clubMembre(int $id): string
    {
        $model  = new \App\Models\MemberModel();
        $member = $model->where('is_active', 1)->find($id);

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $name = esc($member->last_name . ' ' . $member->first_name);

        return view('public/pages/club_membre', [
            'title'       => $name . ' — RBC Disonais',
            'page_title'  => $name,
            'breadcrumbs' => [
                ['label' => 'Accueil',      'url' => base_url('/')],
                ['label' => 'Le Club',      'url' => '#'],
                ['label' => 'Nos Membres',  'url' => base_url('club/membres')],
                ['label' => $name],
            ],
            'member' => $member,
        ]);
    }

    public function ecoleBillard(): string
    {
        $school   = (new \App\Models\SchoolSettingModel())->first();
        $treasury = (new \App\Models\TreasurySettingModel())->first();
        $memberModel = new \App\Models\MemberModel();

        $teacher = ($school && $school->teacher_member_id)
            ? $memberModel->find($school->teacher_member_id)
            : null;

        $contact = ($school && $school->contact_member_id)
            ? $memberModel->find($school->contact_member_id)
            : null;

        return view('public/pages/ecole_billard', [
            'title'       => 'École de Billard — RBC Disonais',
            'page_title'  => 'École de Billard',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Le Club', 'url' => '#'],
                ['label' => 'École de Billard'],
            ],
            'school'   => $school,
            'treasury' => $treasury,
            'teacher'  => $teacher,
            'contact'  => $contact,
        ]);
    }

    public function contact(): string
    {
        $hours = (new \App\Models\OpeningHourModel())->getAllOrdered();

        return view('public/pages/contact', [
            'title'       => 'Contact — RBC Disonais',
            'page_title'  => 'Contact',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Contact'],
            ],
            'hours' => $hours,
        ]);
    }

    // ── Saison ───────────────────────────────────────────────────────────

    public function saisonResultats(): string
    {
        return $this->placeholder('Résultats sportifs', 'Saison ' . ANNEE_1 . '-' . ANNEE_2);
    }

    // ── Saison ── Coupe des Régions ───────────────────────────────────────

    public function cupTeam(int $id): string
    {
        $db   = \Config\Database::connect();
        $team = $db->table('cup_teams t')
            ->select([
                't.*',
                'm1.id AS p1_id', 'm1.last_name AS p1_last', 'm1.first_name AS p1_first', 'm1.photo AS p1_photo',
                'm2.id AS p2_id', 'm2.last_name AS p2_last', 'm2.first_name AS p2_first', 'm2.photo AS p2_photo',
                'm3.id AS p3_id', 'm3.last_name AS p3_last', 'm3.first_name AS p3_first', 'm3.photo AS p3_photo',
            ])
            ->join('members m1', 'm1.id = t.player1_id')
            ->join('members m2', 'm2.id = t.player2_id')
            ->join('members m3', 'm3.id = t.player3_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowObject();

        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('public/pages/cup_team', [
            'title'       => esc($team->name) . ' — Coupe des Régions — RBC Disonais',
            'page_title'  => esc($team->name),
            'breadcrumbs' => [
                ['label' => 'Accueil',             'url' => base_url('/')],
                ['label' => 'Saison ' . ANNEE_1 . '-' . ANNEE_2, 'url' => '#'],
                ['label' => 'Coupe des Régions',   'url' => '#'],
                ['label' => esc($team->name)],
            ],
            'team' => $team,
        ]);
    }

    // ── Archives ─────────────────────────────────────────────────────────

    public function archivesJournal(): string
    {
        $isLoggedIn = (bool) session()->get('admin_logged_in');
        $byYear     = $isLoggedIn
            ? (new \App\Models\JournalIssueModel())->getPublishedGroupedByYear()
            : [];

        $db     = \Config\Database::connect();
        $editor = $db->table('admin_users au')
                     ->select('au.first_name, au.last_name, m.id as member_id, m.photo, m.gender')
                     ->join('admin_user_roles aur', 'aur.admin_user_id = au.id')
                     ->join('members m', 'm.id = au.member_id', 'left')
                     ->where('aur.role', 'PR & Communication')
                     ->where('au.is_active', 1)
                     ->limit(1)
                     ->get()->getRowObject();

        return view('public/pages/archives_journal', [
            'title'       => 'Journal "Partie Libre" — RBC Disonais',
            'page_title'  => 'Partie Libre',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Archives', 'url' => '#'],
                ['label' => 'Journal "Partie Libre"'],
            ],
            'byYear'      => $byYear,
            'editor'      => $editor,
            'isLoggedIn'  => $isLoggedIn,
        ]);
    }

    public function archivesResultats(): string
    {
        return $this->placeholder('Résultats sportifs', 'Archives');
    }

    public function galerie(): string
    {
        return $this->placeholder('Galeries photos', 'Archives');
    }

    // ── Documents utiles ─────────────────────────────────────────────────

    public function documents(): string
    {
        return $this->placeholder('Documents utiles');
    }

    public function documentsStatuts(): string
    {
        return $this->placeholder('Statuts du club', 'Documents utiles', base_url('documents'));
    }

    public function documentsRoi(): string
    {
        return $this->placeholder("Règlement d'ordre intérieur", 'Documents utiles', base_url('documents'));
    }

    public function documentsRgpd(): string
    {
        return $this->placeholder('R.G.P.D.', 'Documents utiles', base_url('documents'));
    }

    // ── Helper ───────────────────────────────────────────────────────────

    private function placeholder(string $title, string $section = '', string $sectionUrl = ''): string
    {
        $breadcrumbs = [['label' => 'Accueil', 'url' => base_url('/')]];
        if ($section) {
            $breadcrumbs[] = ['label' => $section, 'url' => $sectionUrl ?: '#'];
        }
        $breadcrumbs[] = ['label' => $title];

        return view('public/pages/placeholder', [
            'title'       => $title . ' — RBC Disonais',
            'page_title'  => $title,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
