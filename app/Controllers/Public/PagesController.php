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
        return $this->placeholder('Nos Membres', 'Le Club');
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
