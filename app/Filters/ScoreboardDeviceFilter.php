<?php

namespace App\Filters;

use App\Models\SiteSettingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ScoreboardDeviceFilter implements FilterInterface
{
    public const COOKIE = 'rbcd_scoreboard';

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

        return redirect()->to(base_url('marquoir/acces'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
