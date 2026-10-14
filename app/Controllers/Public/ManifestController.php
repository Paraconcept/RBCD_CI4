<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * Manifestes d'application : le site et le marquoir s'installent sur les Chromebooks (étagère),
 * chacun avec sa propre icône. Générés pour que les adresses suivent base_url (local / production).
 */
class ManifestController extends BaseController
{
    public function site()
    {
        return $this->manifest([
            'id'          => base_url('/'),
            'name'        => 'RBC Disonais',
            'short_name'  => 'RBC Disonais',
            'description' => 'Royal Billard Club Disonais — site du club.',
            'start_url'   => base_url('/'),
            'scope'       => base_url('/'),
        ], 'site');
    }

    public function marquoir()
    {
        return $this->manifest([
            'id'          => base_url('marquoir'),
            'name'        => 'Marquoir RBC Disonais',
            'short_name'  => 'Marquoir',
            'description' => 'Marquoirs électroniques des billards du RBC Disonais.',
            'start_url'   => base_url('marquoir'),
            'scope'       => base_url('marquoir'),
            // Demandé au lancement depuis l'étagère ; un navigateur qui ne le gère pas retombe sur « standalone » (fenêtre sans barre d'adresse)
            'display'     => 'fullscreen',
        ], 'marquoir');
    }

    private function manifest(array $data, string $icons)
    {
        $icon = static fn (string $file, int $size, string $purpose) => [
            'src' => base_url('assets/images/pwa/' . $file), 'sizes' => $size . 'x' . $size, 'type' => 'image/png', 'purpose' => $purpose,
        ];

        $data += [
            'lang'             => 'fr',
            'display'          => 'standalone',
            'background_color' => '#0a1020',
            'theme_color'      => '#0a1020',
            'icons'            => [
                $icon($icons . '-192.png', 192, 'any'),
                $icon($icons . '-512.png', 512, 'any'),
                $icon($icons . '-maskable-512.png', 512, 'maskable'),
            ],
        ];

        return $this->response
            ->setHeader('Content-Type', 'application/manifest+json; charset=utf-8')
            ->setBody(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
