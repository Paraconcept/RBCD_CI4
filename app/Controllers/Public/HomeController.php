<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'           => 'RBC Disonais — Club de Billard Carambole à Dison',
            'news'            => $this->getFakeNews(),
            'upcoming_matches' => $this->getFakeMatches(),
            'birthdays'       => [],
        ];

        return view('public/home/index', $data);
    }

    private function getFakeNews(): array
    {
        return [
            [
                'id'         => 1,
                'title'      => 'Victoire de l\'équipe A en Intercommunales',
                'slug'       => 'victoire-equipe-a-intercommunales',
                'excerpt'    => 'Notre équipe A a décroché une belle victoire lors de la dernière rencontre des Intercommunales. Une performance remarquable de l\'ensemble de l\'équipe.',
                'content'    => '',
                'image'      => null,
                'category'   => 'Résultats',
                'created_at' => '2026-05-01',
            ],
            [
                'id'         => 2,
                'title'      => 'Assemblée générale — compte rendu',
                'slug'       => 'assemblee-generale-compte-rendu',
                'excerpt'    => 'Retour sur l\'assemblée générale annuelle du RBC Disonais. Le bilan de la saison, les projets pour l\'an prochain et l\'élection du nouveau comité.',
                'content'    => '',
                'image'      => null,
                'category'   => 'Club',
                'created_at' => '2026-04-15',
            ],
            [
                'id'         => 3,
                'title'      => 'Nouvelle saison 2026-2027 — ouverture des inscriptions',
                'slug'       => 'inscriptions-saison-2026-2027',
                'excerpt'    => 'Les inscriptions pour la saison 2026-2027 sont ouvertes. N\'attendez plus pour renouveler votre adhésion ou rejoindre le club.',
                'content'    => '',
                'image'      => null,
                'category'   => 'Inscriptions',
                'created_at' => '2026-04-01',
            ],
        ];
    }

    private function getFakeMatches(): array
    {
        return [
            ['date' => '10/05/2026 19h00', 'home' => 'RBCD A', 'away' => 'BC Verviers'],
            ['date' => '12/05/2026 19h00', 'home' => 'BC Liège', 'away' => 'RBCD B'],
            ['date' => '17/05/2026 19h00', 'home' => 'RBCD A', 'away' => 'BC Spa'],
        ];
    }
}
