<?php

namespace App\Filters;

use App\Models\ScoreboardDeviceModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Le marquoir et la page du bar ne s'ouvrent qu'aux membres du comité connectés et aux appareils du club
 * (Chromebooks, tablette du bar) autorisés un par un par l'un d'eux. Pour un internaute, ils n'existent pas.
 */
class ScoreboardDeviceFilter implements FilterInterface
{
    // Clé de l'appareil (seule son empreinte est en base) ; le cookie n'est pas synchronisé par le compte Google
    public const COOKIE = 'rbcd_scoreboard';
    public const TABLE_COOKIE = 'rbcd_scoreboard_table';
    private const COOKIE_LIFETIME = 10 * 365 * 86400;

    public static function isCommittee(): bool
    {
        return (bool) session()->get('admin_logged_in');
    }

    /** Appareil autorisé qui fait la requête, ou null. */
    public static function device(): ?object
    {
        static $device = false;
        if ($device === false) {
            helper('cookie');
            $token  = (string) get_cookie(self::COOKIE);
            $device = strlen($token) === 64 ? (new ScoreboardDeviceModel())->where('token_hash', hash('sha256', $token))->first() : null;
        }
        return $device;
    }

    public static function isAuthorized(): bool
    {
        return self::isCommittee() || self::device() !== null;
    }

    /** Crée la clé d'un nouvel appareil et la dépose dans son navigateur. */
    public static function authorize(ResponseInterface $response, string $name, ?int $memberId): void
    {
        $token = bin2hex(random_bytes(32));
        (new ScoreboardDeviceModel())->insert([
            'name' => $name, 'token_hash' => hash('sha256', $token), 'created_by' => $memberId,
            'last_seen_at' => date('Y-m-d H:i:s'), 'last_ip' => service('request')->getIPAddress(),
        ]);
        $response->setCookie(self::COOKIE, $token, self::COOKIE_LIFETIME, '', '/', '', null, true, 'Lax');
    }

    public function before(RequestInterface $request, $arguments = null): mixed
    {
        if (self::isAuthorized()) {
            $this->touch();
            return null;
        }

        if ($request->isAJAX() || str_contains($request->getUri()->getPath(), 'marquoir/api/') || str_contains($request->getUri()->getPath(), 'caisse/api/')) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'Appareil non autorisé']);
        }

        return redirect()->to(base_url('/'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }

    /** Dernière utilisation de l'appareil, au plus une écriture toutes les 5 minutes (le marquoir interroge l'API sans cesse). */
    private function touch(): void
    {
        $device = self::device();
        if ($device && (!$device->last_seen_at || strtotime($device->last_seen_at) < time() - 300)) {
            (new ScoreboardDeviceModel())->update($device->id, ['last_seen_at' => date('Y-m-d H:i:s'), 'last_ip' => service('request')->getIPAddress()]);
        }
    }
}
