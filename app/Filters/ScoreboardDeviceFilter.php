<?php

namespace App\Filters;

use App\Models\SiteSettingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ScoreboardDeviceFilter implements FilterInterface
{
    public const COOKIE = 'rbcd_scoreboard';
    // Billard de ce Chromebook : un cookie n'est pas synchronisé par le compte Google, contrairement aux favoris
    public const TABLE_COOKIE = 'rbcd_scoreboard_table';

    public static function tokenFor(string $pin): string
    {
        return hash('sha256', 'rbcd-scoreboard|' . $pin);
    }

    public static function isAuthorized(): bool
    {
        if (session()->get('admin_logged_in')) {
            return true;
        }

        $pin = (string) (new SiteSettingModel())->getSetting('scoreboard_pin', '');
        if ($pin === '') {
            return false;
        }

        helper('cookie');
        return hash_equals(self::tokenFor($pin), (string) get_cookie(self::COOKIE));
    }

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        if (self::isAuthorized()) {
            return null;
        }

        if ($request->isAJAX() || str_contains($request->getUri()->getPath(), 'marquoir/api/')) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'Appareil non autorisé']);
        }

        // Depuis Internet, le marquoir n'existe pas : seul un Chromebook autorisé (ou le DS connecté) y entre.
        // Un nouveau Chromebook s'autorise par l'adresse directe marquoir/acces (code PIN).
        return redirect()->to(base_url('/'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
