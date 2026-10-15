<?php
use App\Models\ScoreboardMatchModel as M;

// Spectateur (page publique « En direct ») : lecture seule, mise à jour par interrogation du serveur
$spectator = $spectator ?? false;
$trio      = M::isTrio($match);
$numbers   = M::playerNumbers($match);
$byPlayer  = static fn (callable $f): array => array_combine($numbers, array_map($f, $numbers));

$config = [
    'id'           => (int) $match->id,
    'startedAt'    => (string) $match->started_at,
    'starter'      => (int) ($match->starting_player ?: 1),
    'discipline'   => (string) $match->discipline,
    'count'        => count($numbers),
    'distances'    => $byPlayer(static fn (int $n): int => (int) $match->{'p' . $n . '_distance'}),
    'inningsLimit' => $match->innings_limit ? (int) $match->innings_limit : null,
    'unequal'      => (bool) $match->unequal_innings,
    'sets'         => M::isSets($match) ? (int) $match->sets_to_win : null,
    'avgMin'       => $byPlayer(static fn (int $n): ?float => $match->{'p' . $n . '_average_min'} !== null ? (float) $match->{'p' . $n . '_average_min'} : null),
    'clock'        => [
        'enabled'    => (bool) $match->shot_clock_enabled,
        'seconds'    => (int) $match->shot_clock_seconds,
        'extensions' => (int) $match->shot_clock_extensions,
        'autoStart'  => $match->type === 'friendly' && !empty($match->shot_clock_auto_start),
    ],
    'players' => $byPlayer(static fn (int $n): array => ['last' => $match->{'p' . $n . '_last_name'}, 'first' => $match->{'p' . $n . '_first_name'}]),
    'server'    => $state,
    'spectator' => $spectator,
    'signed'    => !empty($signed),
    'urls'      => [
        'save'  => base_url('marquoir/api/match/' . $match->id),
        'table' => base_url($match->table_id ? 'marquoir/billard/' . $match->table_id : 'marquoir'),
        'state' => base_url('en-direct/api/' . $match->id),
        'edit'  => base_url('marquoir/match/' . $match->id . '/modifier'),
        'list'  => base_url('en-direct'),
        'rematch' => $match->type === 'friendly' && $match->table_id ? base_url('marquoir/match/' . $match->id . '/rejouer') : null,
        'sign'    => $match->type === 'official' && !$trio && $match->signature_required && empty($signed) ? base_url('marquoir/match/' . $match->id . '/signer') : null,
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(implode(' – ', array_map(static fn (int $n) => $match->{'p' . $n . '_last_name'}, $numbers))) ?> | Marquoir RBCD</title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicons/favicon.ico') ?>">
    <?php if (str_starts_with(uri_string(), 'marquoir')): // pas sur les pages publiques « En direct » ?>
    <link rel="manifest" href="<?= base_url('marquoir.webmanifest') ?>">
    <meta name="theme-color" content="#0a1020">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/pwa/marquoir-180.png') ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/scoreboard/scoreboard.css') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard.css') ?>">
</head>
<body>
<div class="sbd<?= $spectator ? ' spectator' : '' ?><?= M::isSets($match) ? ' sets' : '' ?><?= $trio ? ' trio' : '' ?>" id="sbd">

    <header class="sbd-head">
        <div class="sbd-head-left">
            <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" alt="">
            <span>RBC Disonais</span>
            <?php if ($spectator): ?><a href="<?= base_url('en-direct') ?>" class="sbd-back"><i class="fas fa-arrow-left me-1"></i>Rencontres du jour</a><?php endif; ?>
            <?php if ($table): ?><span class="sbd-muted">· <?= esc($table->name) ?></span><?php endif; ?>
        </div>
        <div class="sbd-discipline">
            <?= esc(M::disciplineLabel($match)) ?>
            <?php if (M::isSets($match)): ?><span class="sbd-muted">· <?= esc(M::distanceLabel($match)) ?></span><?php endif; ?>
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

    <?php ob_start(); ?>
        <section class="sbd-center">
            <?php if (M::isSets($match)): ?>
            <div class="sbd-box sbd-setsbox">
                <span id="sbdSetNo">Set 1</span>
                <b id="sbdSetsScore"></b>
                <small>en <?= (int) $match->p1_distance ?> points</small>
            </div>
            <?php endif; ?>
            <div class="sbd-box sbd-innings">
                <span>Reprises</span>
                <b id="sbdInnings">0</b>
                <small id="sbdInningsLimit"></small>
            </div>
            <div class="sbd-notice" id="sbdNotice"></div>
            <div class="sbd-box sbd-run" id="sbdRunBox">
                <span>Série</span>
                <b id="sbdRun">0</b>
                <div class="sbd-hint" id="sbdHint"></div>
            </div>
        </section>
    <?php $center = ob_get_clean(); ?>
    <main class="sbd-main">
        <?php foreach ($numbers as $n): $p = 'p' . $n; $right = $n === 2 && !$trio; ?>
        <?php if ($n === 2 && !$trio) echo $center; ?>
        <section class="sbd-player <?= $p ?>" id="sbdPlayer<?= $n ?>">
            <div class="sbd-name">
                <?php if ($right): ?><span class="sbd-ball"></span><?php endif; ?>
                <div class="sbd-name-text" style="<?= $right ? 'text-align:right;flex:1' : 'flex:1' ?>">
                    <div class="sbd-last"><?= esc($match->{$p . '_last_name'}) ?></div>
                    <div class="sbd-first"><?= esc($match->{$p . '_first_name'}) ?></div>
                    <div class="sbd-club"><?= esc($match->{$p . '_club'} ?? '') ?></div>
                    <?php if (M::isSets($match)): ?>
                    <div class="sbd-target">Sets : <span class="sbd-setdots" data-sets></span></div>
                    <?php else: ?>
                    <div class="sbd-target">À réaliser : <b><?= (int) $match->{$p . '_distance'} ?></b></div>
                    <?php endif; ?>
                </div>
                <?php if (!$right): ?><span class="sbd-ball"></span><?php endif; ?>
            </div>
            <div class="sbd-body">
                <?php if (!$trio): ?>
                <div class="sbd-last5" data-last5 title="5 dernières reprises (la plus récente en haut)"></div>
                <?php endif; ?>
                <div class="sbd-score" data-score>0</div>
            </div>
            <div class="sbd-stats">
                <div class="sbd-stat"><span>Moyenne</span><b data-avg>0,000</b></div>
                <div class="sbd-stat"><span>Série max</span><b data-hr>0</b></div>
            </div>
            <div class="sbd-ext" data-ext></div>
        </section>
        <?php endforeach; ?>
        <?php if ($trio) echo $center; ?>
    </main>

    <div class="sbd-pour" id="sbdPour" aria-live="polite"><div><small></small><b></b></div></div>

    <div class="sbd-pause" id="sbdPause" aria-live="polite">
        <div>
            <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" alt="">
            <b>Pause</b>
            <small>RBC Disonais</small>
        </div>
    </div>

    <div class="sbd-clock" id="sbdClock" hidden>
        <div class="sbd-clock-track"><div class="sbd-clock-bar" id="sbdClockBar"></div></div>
        <div class="sbd-clock-num" id="sbdClockNum"></div>
    </div>

    <?php if ($spectator): ?>
    <!-- Suggestion mode paysage (smartphone), identique à la page Coupe des Régions -->
    <div class="sbd-rotate-hint" id="sbdRotateHint" role="alert">
        <button type="button" class="sbd-rotate-hint-close" aria-label="Fermer" onclick="document.getElementById('sbdRotateHint').remove()">&times;</button>
        <i class="fas fa-mobile-alt fa-rotate-90"></i>
        <p>Pour un meilleur confort de lecture, tournez votre téléphone en mode paysage.</p>
    </div>
    <?php endif; ?>

    <?php if (!$spectator): ?>
    <footer class="sbd-help">
        <span><kbd>+</kbd> point</span>
        <span><kbd>−</kbd> correction</span>
        <span><kbd>0-9</kbd> série directe</span>
        <span><kbd>Entrée</kbd> fin de reprise</span>
        <?php if ($match->shot_clock_enabled): ?>
        <span><kbd>*</kbd> chrono départ/pause</span>
        <?php endif; ?>
        <span><kbd>/</kbd> effacer la saisie</span>
        <span><kbd>.</kbd> pause</span>
        <span><kbd>⌫</kbd> menu</span>
    </footer>
    <?php endif; ?>

    <div class="sbd-overlay" id="sbdResult">
        <div class="sbd-result">
            <h2>Partie terminée</h2>
            <div class="sbd-winner" id="sbdWinner"></div>
            <div class="sbd-decision" id="sbdDecision" hidden>Départagés à la moyenne proportionnelle</div>
            <table>
                <thead><tr id="sbdResultHead"><th></th><th>Points</th><th>Reprises</th><th>Moyenne</th><th>MS</th><th id="sbdPropHead" hidden>Moy. prop.</th></tr></thead>
                <tbody id="sbdResultRows"></tbody>
            </table>
            <div class="sbd-small">
                <?php if ($spectator): ?>
                <a href="<?= base_url('en-direct') ?>" class="btn btn-outline-light"><i class="fas fa-arrow-left me-2"></i>Rencontres du jour</a>
                <?php elseif (!empty($signed)): ?>
                <div class="sbd-signed mb-2"><i class="fas fa-file-signature me-2"></i>Feuille de match signée — résultat définitif</div>
                <kbd>Entrée</kbd> retour au billard
                <?php else: ?>
                <kbd>Entrée</kbd> retour au billard &nbsp;·&nbsp; <kbd>−</kbd> corriger la dernière reprise
                <?php if ($config['urls']['sign']): ?>
                &nbsp;·&nbsp; <kbd>+</kbd> signer la feuille de match
                <div class="mt-3"><button type="button" class="btn btn-sb btn-lg" id="sbdSignBtn"><i class="fas fa-signature me-2"></i>Signer la feuille de match</button></div>
                <?php endif; ?>
                <?php if ($config['urls']['rematch']): ?>
                &nbsp;·&nbsp; <kbd>+</kbd> rejouer la même partie
                <form method="post" id="sbdRematchForm" action="<?= esc($config['urls']['rematch']) ?>" hidden></form>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$spectator && M::isSets($match)): ?>
    <div class="sbd-overlay" id="sbdSetBreak">
        <div class="sbd-result">
            <h2 id="sbdSetBreakTitle"></h2>
            <div class="sbd-winner" id="sbdSetBreakWinner"></div>
            <div class="sbd-setbreak-score" id="sbdSetBreakScore"></div>
            <table>
                <thead><tr><th></th><th>Points</th><th>Reprises</th><th>Moyenne</th><th>MS</th></tr></thead>
                <tbody id="sbdSetBreakRows"></tbody>
            </table>
            <div class="sbd-setbreak-next" id="sbdSetBreakNext"></div>
            <div class="sbd-small">
                <kbd>Entrée</kbd> <span id="sbdSetBreakKey"></span> &nbsp;·&nbsp; <kbd>−</kbd> corriger la dernière reprise
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$spectator): ?>
    <div class="sbd-overlay" id="sbdFix">
        <div class="sbd-result sbd-fix">
            <h2>Corriger une reprise</h2>
            <div class="sbd-fix-scroll">
                <table>
                    <thead><tr><th>Reprise</th><?php foreach ($numbers as $n): ?><th style="color:var(--sb-<?= $trio ? 'p1' : 'p' . $n ?>)"><?= esc($match->{'p' . $n . '_last_name'}) ?></th><?php endforeach; ?></tr></thead>
                    <tbody id="sbdFixRows"></tbody>
                </table>
            </div>
            <div class="sbd-fix-msg" id="sbdFixMsg"></div>
            <div class="sbd-small" id="sbdFixHelp"></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$spectator): ?>
    <div class="sbd-overlay" id="sbdMenu">
        <div class="sbd-menu">
            <button type="button" class="btn btn-light" data-action="fix" id="sbdFixBtn"><i class="fas fa-pen-to-square me-2"></i>Corriger une reprise</button>
            <button type="button" class="btn btn-light" data-action="edit"><i class="fas fa-user-pen me-2"></i>Modifier la partie en cours</button>
            <button type="button" class="btn btn-light" data-action="fullscreen"><i class="fas fa-expand me-2"></i>Plein écran</button>
            <a href="<?= esc($config['urls']['table']) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour au billard (la partie reste en cours)</a>
            <form method="post" id="sbdSwapForm" action="<?= base_url('marquoir/match/' . $match->id . '/inverser') ?>" hidden>
                <button type="submit" class="btn btn-warning w-100"><i class="fas fa-right-left me-2"></i><?= $trio ? "Changer l'ordre de jeu" : 'Inverser les deux joueurs' ?></button>
            </form>
            <form method="post" id="sbdCancelForm" action="<?= base_url('marquoir/match/' . $match->id . '/annuler') ?>"
                  data-title="<?= $match->type === 'friendly' ? 'Annuler cette partie amicale ?' : 'Remettre la rencontre à zéro ?' ?>"
                  data-text="<?= $match->type === 'friendly' ? 'La partie sera abandonnée et ne figurera pas dans les résultats.' : 'Toutes les reprises seront effacées et la rencontre redeviendra « prévue ».' ?>"
                  data-confirm="<?= $match->type === 'friendly' ? 'Oui, annuler la partie' : 'Oui, remettre à zéro' ?>">
                <button type="button" class="btn btn-danger w-100" data-action="cancel"><i class="fas fa-ban me-2"></i><?= $match->type === 'friendly' ? 'Annuler la partie' : 'Remettre la rencontre à zéro' ?></button>
            </form>
            <button type="button" class="btn btn-outline-light" data-action="close"><i class="fas fa-xmark me-2"></i>Fermer</button>
            <div class="sbd-menu-keys"><kbd>8</kbd> <kbd>2</kbd> se déplacer &nbsp;·&nbsp; <kbd>Entrée</kbd> valider &nbsp;·&nbsp; <kbd>⌫</kbd> fermer</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!$spectator): ?>
<?= $this->include('public/scoreboard/_bar_call') ?>
<?php endif; ?>
<script>window.SB_CONFIG = <?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script src="<?= base_url('assets/scoreboard/scoreboard-display.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-display.js') ?>"></script>
</body>
</html>
