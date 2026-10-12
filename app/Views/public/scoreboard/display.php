<?php
use App\Models\ScoreboardMatchModel as M;

$sameDistance = (int) $match->p1_distance === (int) $match->p2_distance;
// Spectateur (page publique « En direct ») : lecture seule, mise à jour par interrogation du serveur
$spectator = $spectator ?? false;

$config = [
    'id'           => (int) $match->id,
    'startedAt'    => (string) $match->started_at,
    'starter'      => (int) ($match->starting_player ?: 1),
    'distances'    => [1 => (int) $match->p1_distance, 2 => (int) $match->p2_distance],
    'inningsLimit' => $match->innings_limit ? (int) $match->innings_limit : null,
    'unequal'      => (bool) $match->unequal_innings,
    'clock'        => [
        'enabled'    => (bool) $match->shot_clock_enabled,
        'seconds'    => (int) $match->shot_clock_seconds,
        'extensions' => (int) $match->shot_clock_extensions,
    ],
    'players' => [
        1 => ['last' => $match->p1_last_name, 'first' => $match->p1_first_name],
        2 => ['last' => $match->p2_last_name, 'first' => $match->p2_first_name],
    ],
    'server'    => $state,
    'spectator' => $spectator,
    'urls'      => [
        'save'  => base_url('marquoir/api/match/' . $match->id),
        'table' => base_url($match->table_id ? 'marquoir/billard/' . $match->table_id : 'marquoir'),
        'state' => base_url('en-direct/api/' . $match->id),
        'list'  => base_url('en-direct'),
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($match->p1_last_name . ' – ' . $match->p2_last_name) ?> | Marquoir RBCD</title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicons/favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/scoreboard/scoreboard.css') ?>">
</head>
<body>
<div class="sbd<?= $spectator ? ' spectator' : '' ?>" id="sbd">

    <header class="sbd-head">
        <div class="sbd-head-left">
            <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" alt="">
            <span>RBC Disonais</span>
            <?php if ($spectator): ?><a href="<?= base_url('en-direct') ?>" class="sbd-back"><i class="fas fa-arrow-left me-1"></i>Rencontres du jour</a><?php endif; ?>
            <?php if ($table): ?><span class="sbd-muted">· <?= esc($table->name) ?></span><?php endif; ?>
        </div>
        <div class="sbd-discipline">
            <?= esc(M::disciplineLabel($match)) ?>
            <?php if ($sameDistance): ?>· <b><?= (int) $match->p1_distance ?></b> points<?php endif; ?>
            <?php if ($competition = M::competitionLabel($match)): ?><span class="sbd-muted">· <?= esc($competition) ?></span><?php endif; ?>
        </div>
        <div class="sbd-head-right">
            <span id="sbdTime"></span>
            <?php if ($spectator): ?>
            <span class="sbd-live" id="sbdLive"><i class="fas fa-circle me-1"></i>EN DIRECT</span>
            <?php else: ?>
            <span class="sbd-sync" id="sbdSync" title="Synchronisation"></span>
            <?php endif; ?>
        </div>
    </header>

    <main class="sbd-main">
        <?php foreach ([1, 2] as $n): $p = 'p' . $n; ?>
        <?php if ($n === 2): ?>
        <section class="sbd-center">
            <div class="sbd-box sbd-innings">
                <span>Reprise</span>
                <b id="sbdInnings">1</b>
                <small id="sbdInningsLimit"></small>
            </div>
            <div class="sbd-notice" id="sbdNotice"></div>
            <div class="sbd-box sbd-run" id="sbdRunBox">
                <span>Série</span>
                <b id="sbdRun">0</b>
                <div class="sbd-hint" id="sbdHint"></div>
            </div>
        </section>
        <?php endif; ?>
        <section class="sbd-player <?= $p ?>" id="sbdPlayer<?= $n ?>">
            <div class="sbd-name">
                <?php if ($n === 2): ?><span class="sbd-ball"></span><?php endif; ?>
                <div class="sbd-name-text" style="<?= $n === 2 ? 'text-align:right;flex:1' : 'flex:1' ?>">
                    <div class="sbd-last"><?= esc($match->{$p . '_last_name'}) ?></div>
                    <div class="sbd-first"><?= esc($match->{$p . '_first_name'}) ?></div>
                    <div class="sbd-club"><?= esc($match->{$p . '_club'} ?? '') ?></div>
                    <?php if (!$sameDistance): ?>
                    <div class="sbd-target">À réaliser : <b><?= (int) $match->{$p . '_distance'} ?></b></div>
                    <?php endif; ?>
                </div>
                <?php if ($n === 1): ?><span class="sbd-ball"></span><?php endif; ?>
            </div>
            <div class="sbd-body">
                <div class="sbd-last5" data-last5 title="5 dernières reprises (la plus récente en haut)"></div>
                <div class="sbd-score" data-score>0</div>
            </div>
            <div class="sbd-stats">
                <div class="sbd-stat"><span>Moyenne</span><b data-avg>0,000</b></div>
                <div class="sbd-stat"><span>Série max</span><b data-hr>0</b></div>
            </div>
            <div class="sbd-ext" data-ext></div>
        </section>
        <?php endforeach; ?>
    </main>

    <div class="sbd-clock" id="sbdClock" hidden>
        <div class="sbd-clock-track"><div class="sbd-clock-bar" id="sbdClockBar"></div></div>
        <div class="sbd-clock-num" id="sbdClockNum"></div>
    </div>

    <?php if (!$spectator): ?>
    <footer class="sbd-help">
        <span><kbd>+</kbd> point</span>
        <span><kbd>−</kbd> correction</span>
        <span><kbd>0-9</kbd> série directe</span>
        <span><kbd>Entrée</kbd> fin de reprise</span>
        <?php if ($match->shot_clock_enabled): ?>
        <span><kbd>*</kbd> chrono départ/pause</span>
        <span><kbd>,</kbd> time-out</span>
        <?php endif; ?>
        <span><kbd>/</kbd> effacer la saisie</span>
        <span><kbd>Échap</kbd> menu</span>
    </footer>
    <?php endif; ?>

    <div class="sbd-overlay" id="sbdResult">
        <div class="sbd-result">
            <h2>Partie terminée</h2>
            <div class="sbd-winner" id="sbdWinner"></div>
            <table>
                <thead><tr><th></th><th>Points</th><th>Reprises</th><th>Moyenne</th><th>MS</th></tr></thead>
                <tbody id="sbdResultRows"></tbody>
            </table>
            <div class="sbd-small">
                <?php if ($spectator): ?>
                <a href="<?= base_url('en-direct') ?>" class="btn btn-outline-light"><i class="fas fa-arrow-left me-2"></i>Rencontres du jour</a>
                <?php else: ?>
                <kbd>Entrée</kbd> retour au billard &nbsp;·&nbsp; <kbd>−</kbd> corriger la dernière reprise
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$spectator): ?>
    <div class="sbd-overlay" id="sbdMenu">
        <div class="sbd-menu">
            <button type="button" class="btn btn-light" data-action="fullscreen"><i class="fas fa-expand me-2"></i>Plein écran</button>
            <a href="<?= esc($config['urls']['table']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour au billard (la partie reste en cours)</a>
            <form method="post" id="sbdCancelForm" action="<?= base_url('marquoir/match/' . $match->id . '/annuler') ?>"
                  data-title="<?= $match->type === 'friendly' ? 'Annuler cette partie amicale ?' : 'Remettre la rencontre à zéro ?' ?>"
                  data-text="<?= $match->type === 'friendly' ? 'La partie sera abandonnée et ne figurera pas dans les résultats.' : 'Toutes les reprises seront effacées et la rencontre redeviendra « prévue ».' ?>"
                  data-confirm="<?= $match->type === 'friendly' ? 'Oui, annuler la partie' : 'Oui, remettre à zéro' ?>">
                <button type="button" class="btn btn-danger w-100" data-action="cancel"><i class="fas fa-ban me-2"></i><?= $match->type === 'friendly' ? 'Annuler la partie' : 'Remettre la rencontre à zéro' ?></button>
            </form>
            <button type="button" class="btn btn-outline-light" data-action="close"><i class="fas fa-xmark me-2"></i>Fermer (Échap)</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>window.SB_CONFIG = <?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script src="<?= base_url('assets/scoreboard/scoreboard-display.js') ?>"></script>
</body>
</html>
