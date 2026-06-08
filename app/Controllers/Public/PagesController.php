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

        $allRoles = $db->table('admin_user_roles')
                       ->select('member_id, role')
                       ->orderBy('member_id', 'ASC')
                       ->orderBy('sort_order', 'ASC')
                       ->get()->getResultObject();

        $rolesMap = [];
        foreach ($allRoles as $r) {
            $rolesMap[(int) $r->member_id][] = $r->role;
        }

        $users = $db->table('members m')
                    ->select('m.id, m.id AS member_id, m.first_name, m.last_name, m.photo, m.gender, m.email, m.mobile, m.show_email, m.show_mobile')
                    ->join('admin_user_roles aur', 'aur.member_id = m.id')
                    ->where('m.is_active', 1)
                    ->groupBy('m.id')
                    ->get()->getResultObject();

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

        $referer   = $this->request->getServer('HTTP_REFERER') ?? '';
        $siteBase  = base_url();
        $backUrl   = base_url('club/membres');
        $backLabel = 'Retour à la liste';

        if (str_starts_with($referer, $siteBase)) {
            $path = substr($referer, strlen($siteBase));
            if (str_contains($path, 'saison/intm/') || str_contains($path, 'saison/coupe-des-regions/')) {
                $backUrl   = $referer;
                $backLabel = 'Retour à l\'équipe';
            }
        }

        $currentSeason = ANNEE_1 . '-' . ANNEE_2;
        $sportResults  = (new \App\Models\SportResultModel())->getByMember($id, $currentSeason, publishedOnly: true);

        return view('public/pages/club_membre', [
            'title'       => $name . ' — RBC Disonais',
            'page_title'  => $name,
            'breadcrumbs' => [
                ['label' => 'Accueil',      'url' => base_url('/')],
                ['label' => 'Le Club',      'url' => '#'],
                ['label' => 'Nos Membres',  'url' => base_url('club/membres')],
                ['label' => $name],
            ],
            'member'        => $member,
            'backUrl'       => $backUrl,
            'backLabel'     => $backLabel,
            'sportResults'  => $sportResults,
            'currentSeason' => $currentSeason,
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

    public function clubTarifs(): string
    {
        $treasury = (new \App\Models\TreasurySettingModel())->first();

        return view('public/pages/club_tarifs', [
            'title'       => 'Tarifs & Fonctionnement — RBC Disonais',
            'page_title'  => 'Tarifs & Fonctionnement',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Le Club', 'url' => '#'],
                ['label' => 'Tarifs & Fonctionnement'],
            ],
            'treasury' => $treasury,
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

    // ── Actualités ───────────────────────────────────────────────────────

    public function newsIndex()
    {
        return redirect()->to(base_url('/'));
    }

    public function newsDetail(string $slug): string
    {
        $news = (new \App\Models\NewsModel())->getBySlug($slug);

        if (!$news) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('public/pages/news_detail', [
            'title'       => esc($news->title) . ' — RBC Disonais',
            'page_title'  => esc($news->title),
            'breadcrumbs' => [
                ['label' => 'Accueil',  'url' => base_url('/')],
                ['label' => esc($news->title)],
            ],
            'news' => $news,
        ]);
    }

    // ── Saison ───────────────────────────────────────────────────────────

    public function saisonResultats(): string
    {
        $season  = ANNEE_1 . '-' . ANNEE_2;
        $results = (new \App\Models\SportResultModel())->getBySeasonWithWinner($season, publishedOnly: true);

        return view('public/pages/saison_resultats', [
            'title'       => 'Résultats sportifs ' . $season . ' — RBC Disonais',
            'page_title'  => 'Résultats sportifs',
            'breadcrumbs' => [
                ['label' => 'Accueil',                              'url' => base_url('/')],
                ['label' => 'Saison ' . ANNEE_1 . '-' . ANNEE_2,  'url' => '#'],
                ['label' => 'Résultats sportifs'],
            ],
            'season'  => $season,
            'results' => $results,
        ]);
    }

    // ── Saison ── Coupe des Régions ───────────────────────────────────────

    public function cdrTeam(int $id): string
    {
        $db   = \Config\Database::connect();
        $team = $db->table('cdr_teams t')
            ->select([
                't.*',
                'm1.id AS p1_id', 'm1.last_name AS p1_last', 'm1.first_name AS p1_first', 'm1.photo AS p1_photo', 'm1.gender AS p1_gender',
                'm2.id AS p2_id', 'm2.last_name AS p2_last', 'm2.first_name AS p2_first', 'm2.photo AS p2_photo', 'm2.gender AS p2_gender',
                'm3.id AS p3_id', 'm3.last_name AS p3_last', 'm3.first_name AS p3_first', 'm3.photo AS p3_photo', 'm3.gender AS p3_gender',
            ])
            ->join('members m1', 'm1.id = t.player1_id')
            ->join('members m2', 'm2.id = t.player2_id')
            ->join('members m3', 'm3.id = t.player3_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowObject();

        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('public/pages/cdr_team', [
            'title'       => esc($team->name) . ' — Coupe des Régions — RBC Disonais',
            'page_title'  => esc($team->name),
            'breadcrumbs' => [
                ['label' => 'Accueil',             'url' => base_url('/')],
                ['label' => 'Saison ' . ANNEE_1 . '-' . ANNEE_2, 'url' => '#'],
                ['label' => 'Coupe des Régions',   'url' => '#'],
                ['label' => esc($team->name)],
            ],
            'team'         => $team,
            'sportResults' => (new \App\Models\SportResultModel())->getByCdrTeam($id, publishedOnly: true),
        ]);
    }

    public function intmTeam(int $id): string
    {
        $db   = \Config\Database::connect();
        $team = $db->table('intm_teams t')
            ->select([
                't.*',
                'm1.id AS p1_id', 'm1.last_name AS p1_last', 'm1.first_name AS p1_first', 'm1.photo AS p1_photo', 'm1.gender AS p1_gender',
                'm2.id AS p2_id', 'm2.last_name AS p2_last', 'm2.first_name AS p2_first', 'm2.photo AS p2_photo', 'm2.gender AS p2_gender',
                'm3.id AS p3_id', 'm3.last_name AS p3_last', 'm3.first_name AS p3_first', 'm3.photo AS p3_photo', 'm3.gender AS p3_gender',
                'm4.id AS p4_id', 'm4.last_name AS p4_last', 'm4.first_name AS p4_first', 'm4.photo AS p4_photo', 'm4.gender AS p4_gender',
            ])
            ->join('members m1', 'm1.id = t.player1_id')
            ->join('members m2', 'm2.id = t.player2_id')
            ->join('members m3', 'm3.id = t.player3_id')
            ->join('members m4', 'm4.id = t.player4_id')
            ->where('t.id', $id)
            ->get()->getRowObject();

        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('public/pages/intm_team', [
            'title'       => esc($team->name) . ' — I.N.T.M. — RBC Disonais',
            'page_title'  => esc($team->name),
            'breadcrumbs' => [
                ['label' => 'Accueil',             'url' => base_url('/')],
                ['label' => 'Saison ' . ANNEE_1 . '-' . ANNEE_2, 'url' => '#'],
                ['label' => 'I.N.T.M.',            'url' => '#'],
                ['label' => esc($team->name)],
            ],
            'team'         => $team,
            'sportResults' => (new \App\Models\SportResultModel())->getByIntmTeam($id, publishedOnly: true),
        ]);
    }

    // ── Archives ─────────────────────────────────────────────────────────

    public function archivesJournal(): string
    {
        $isLoggedIn = (bool) session()->get('member_logged_in');
        $byYear     = $isLoggedIn
            ? (new \App\Models\JournalIssueModel())->getPublishedGroupedByYear()
            : [];

        $editorId = (int) (new \App\Models\SiteSettingModel())->getSetting('journal_editor_member_id', 0);
        $editor   = null;
        if ($editorId > 0) {
            $editor = \Config\Database::connect()
                ->table('members')
                ->select('first_name, last_name, id as member_id, photo, gender')
                ->where('id', $editorId)
                ->where('is_active', 1)
                ->get()->getRowObject();
        }

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
        $bySeasonData = (new \App\Models\SportResultModel())->getGroupedBySeasonWithWinner(publishedOnly: true);

        return view('public/pages/archives_resultats', [
            'title'       => 'Résultats sportifs — Archives — RBC Disonais',
            'page_title'  => 'Résultats sportifs',
            'breadcrumbs' => [
                ['label' => 'Accueil',  'url' => base_url('/')],
                ['label' => 'Archives', 'url' => '#'],
                ['label' => 'Résultats sportifs'],
            ],
            'bySeasonData' => $bySeasonData,
        ]);
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

    public function documentsRemboursementsMutuelle(): string
    {
        return view('public/pages/remboursements_mutuelle', [
            'title'      => 'Remboursements Mutuelle — RBC Disonais',
            'page_title' => 'Remboursements Mutuelle',
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => base_url('/')],
                ['label' => 'Documents utiles', 'url' => base_url('documents')],
                ['label' => 'Remboursements Mutuelle'],
            ],
        ]);
    }

    public function documentShow(string $slug)
    {
        $doc = (new \App\Models\ClubDocumentModel())->findBySlug($slug);

        if ($doc && $doc->filename) {
            $path = FCPATH . 'uploads/PDF/Documents/' . $doc->filename;
            if (file_exists($path)) {
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'inline; filename="' . basename($doc->filename) . '"')
                    ->setHeader('Content-Length', (string) filesize($path))
                    ->setBody(file_get_contents($path));
            }
        }

        $title = $doc->title ?? ucwords(str_replace('-', ' ', $slug));
        return $this->placeholder($title, 'Documents utiles', base_url('documents'));
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
