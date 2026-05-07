<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class PagesController extends BaseController
{
    // ── Le Club ──────────────────────────────────────────────────────────

    public function clubHistoire(): string
    {
        return $this->placeholder('Histoire & présentation', 'Le Club');
    }

    public function clubComite(): string
    {
        return $this->placeholder('Le Comité', 'Le Club');
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
        return $this->placeholder('Contact');
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
