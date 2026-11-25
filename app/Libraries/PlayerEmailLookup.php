<?php

namespace App\Libraries;

/**
 * Adresse e-mail d'un joueur, pour lui envoyer sa feuille de match (et son club, d'après le site fédéral).
 * Dans l'ordre : membre du RBCD, joueur déjà encodé au marquoir, puis site fédéral (www.frbb-kbbb.be) par licence.
 */
class PlayerEmailLookup
{
    /** @return array{email: string, source: string}|null source : club, marquoir ou frbb */
    public static function find(?string $license, ?int $memberId = null, ?string $lastName = null, ?string $firstName = null): ?array
    {
        $db  = db_connect();
        $key = FrbbMembersImport::licenseKey($license);

        $email = null;
        if ($memberId) {
            $email = $db->table('members')->select('email')->where('id', $memberId)->get()->getRow()?->email;
        }
        if (!self::valid($email) && $key) {
            foreach ($db->table('members')->select('email, frbb_license')->where('frbb_license IS NOT NULL')->like('frbb_license', $key)->get()->getResult() as $m) {
                if (FrbbMembersImport::licenseKey($m->frbb_license) === $key && self::valid($m->email)) {
                    $email = $m->email;
                    break;
                }
            }
        }
        if (self::valid($email)) {
            return ['email' => strtolower(trim($email)), 'source' => 'club'];
        }

        $players = $db->table('scoreboard_players')->select('email, license')->where('email IS NOT NULL')->where('email <>', '');
        if ($key) {
            $players->like('license', $key);
        } elseif ($lastName && $firstName) {
            $players->where('last_name', $lastName)->where('first_name', $firstName);
        } else {
            $players = null;
        }
        foreach ($players ? $players->get()->getResult() : [] as $p) {
            if ((!$key || FrbbMembersImport::licenseKey($p->license) === $key) && self::valid($p->email)) {
                return ['email' => strtolower(trim($p->email)), 'source' => 'marquoir'];
            }
        }

        $fed = $key ? self::federation((string) $license) : null;
        // Licence mal encodée : l'adresse serait celle d'un autre joueur
        if ($fed && $lastName && self::plain($fed['last_name']) !== self::plain($lastName)) {
            return null;
        }
        return $fed && $fed['email'] ? ['email' => $fed['email'], 'source' => 'frbb'] : null;
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'club'     => 'fiche du membre RBCD',
            'marquoir' => 'joueur déjà encodé au marquoir',
            default    => 'site de la fédération (FRBB)',
        };
    }

    /**
     * Fiche du joueur sur le site fédéral (www.frbb-kbbb.be) : nom, e-mail et club de la saison.
     * La lettre de la licence compte : « 4210B » renvoie le club de la carte B.
     * @return array{last_name: string, first_name: string, email: ?string, club: ?string, club_code: ?string}|null
     */
    public static function federation(string $license): ?array
    {
        static $cache = [];
        $license = strtoupper(preg_replace('/\s+/', '', $license));
        if ($license === '' || !preg_match('/\d/', $license)) {
            return null;
        }
        if (array_key_exists($license, $cache)) {
            return $cache[$license];
        }
        $url = rtrim((string) env('FRBB_KBBB_API_URL'), '/');
        $key = (string) env('FRBB_KBBB_API_KEY');
        if ($url === '' || $key === '') {
            return null;
        }

        $ch = curl_init($url . '/player?lic=' . rawurlencode($license));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $key, 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !is_string($body)) {
            if ($code !== 404) {
                log_message('warning', 'Recherche joueur FRBB (licence ' . $license . ') : HTTP ' . $code);
            }
            return $cache[$license] = null;
        }
        $d = (array) json_decode($body, true);

        return $cache[$license] = [
            'last_name'  => (string) ($d['last_name'] ?? ''),
            'first_name' => (string) ($d['first_name'] ?? ''),
            'email'      => self::valid($d['email'] ?? null) ? strtolower(trim($d['email'])) : null,
            'club'       => $d['club'] ?? null,
            'club_code'  => $d['club_code'] ?? null,
        ];
    }

    private static function plain(string $name): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        return preg_replace('/[^A-Z]/', '', strtoupper($ascii !== false ? $ascii : $name));
    }

    private static function valid(?string $email): bool
    {
        return $email !== null && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }
}
