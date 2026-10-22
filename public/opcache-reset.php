<?php

/*
 * Vide le cache OPcache de PHP après un déploiement (appelé par .github/workflows/deploy.yml).
 * Le serveur web garde sinon en mémoire les anciennes versions des fichiers PHP : un git pull ne suffit pas.
 * Fichier autonome, hors CodeIgniter : il fonctionne même quand le routeur en cache est encore l'ancien.
 * Protégé par un jeton à usage unique que le script de déploiement écrit juste avant l'appel.
 */

header('Content-Type: application/json');

$tokenFile = __DIR__ . '/../writable/deploy_token';
$expected  = is_file($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';
$given     = (string) ($_GET['token'] ?? '');
if ($expected === '' || !hash_equals($expected, $given)) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}
@unlink($tokenFile);

$enabled = function_exists('opcache_get_status') && @opcache_get_status(false) !== false;
echo json_encode([
    'ok'                  => true,
    'opcache'             => $enabled,
    'reset'               => $enabled && opcache_reset(),
    'validate_timestamps' => ini_get('opcache.validate_timestamps'),
    'revalidate_freq'     => ini_get('opcache.revalidate_freq'),
]);
