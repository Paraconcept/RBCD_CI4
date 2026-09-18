<?php

namespace App\Libraries;

/**
 * Va chercher le calendrier CDR d'une équipe sur l'API de frbb-liege-lux.be.
 * Mis en cache pour ne pas dépendre de la disponibilité du site FRBB à
 * chaque affichage de la page ; retourne null en cas d'échec (site FRBB
 * injoignable, équipe pas encore mappée) pour que la page RBCD s'affiche
 * quand même sans le calendrier.
 */
class FrbbCdrClient
{
    private const CACHE_TTL = 300; // 5 min

    public function getTeamCalendar(int $frbbTeamId): ?array
    {
        $cache = \Config\Services::cache();
        $key   = "frbb_cdr_team_{$frbbTeamId}";

        $cached = $cache->get($key);
        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        $data = $this->fetch($frbbTeamId);
        $cache->save($key, $data ?? false, self::CACHE_TTL);

        return $data;
    }

    private function fetch(int $frbbTeamId): ?array
    {
        $base = rtrim((string) env('FRBB_API_BASE_URL'), '/');
        $key  = (string) env('FRBB_API_KEY');
        if (!$base || !$key) {
            return null;
        }

        $ch = curl_init("{$base}/api/cdr/team/{$frbbTeamId}/calendar");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . $key],
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $httpCode !== 200) {
            return null;
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }
}
