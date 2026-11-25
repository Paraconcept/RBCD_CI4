<?php

namespace App\Libraries;

/**
 * Adresse e-mail d'un joueur, pour lui envoyer sa feuille de match.
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

        $email = $key ? self::federation($key) : null;
        return $email ? ['email' => $email, 'source' => 'frbb'] : null;
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'club'     => 'fiche du membre RBCD',
            'marquoir' => 'joueur déjà encodé au marquoir',
            default    => 'site de la fédération (FRBB)',
        };
    }

    private static function federation(string $license): ?string
    {
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
                log_message('warning', 'Recherche e-mail FRBB (licence ' . $license . ') : HTTP ' . $code);
            }
            return null;
        }
        $email = json_decode($body, true)['email'] ?? null;

        return self::valid($email) ? strtolower(trim($email)) : null;
    }

    private static function valid(?string $email): bool
    {
        return $email !== null && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }
}
