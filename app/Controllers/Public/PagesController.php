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
                    ->select('au.id, au.first_name, au.last_name, au.member_id, m.photo')
                    ->join('members m', 'm.id = au.member_id', 'left')
                    ->where('au.is_active', 1)
                    ->get()->getResultObject();

        $allRoles = $db->table('admin_user_roles')
                       ->select('admin_user_id, role')
                       ->get()->getResultObject();

        $rolesMap = [];
        foreach ($allRoles as $r) {
            $rolesMap[$r->admin_user_id][] = $r->role;
        }

        $publicOrder = array_flip([
            'Président', 'Vice-Président', 'Secrétaire', 'Secrétaire Adjoint',
            'Directeur Sportif', 'Directeur Sportif Adjoint',
            'Trésorier', 'Trésorier Adjoint', 'Commissaire', 'Webmaster',
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
        return $this->placeholder('Notre école de billard', 'Le Club');
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

    // ── Archives ─────────────────────────────────────────────────────────

    public function archivesJournal(): string
    {
        return $this->placeholder('Journal "Partie Libre"', 'Archives');
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
