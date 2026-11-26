<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$tableId = (int) $table->id;
$frDate = static function (string $dt): string {
    $days   = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $months = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $t = strtotime($dt);
    return ucfirst($days[(int) date('w', $t)]) . ' ' . date('j', $t) . ' ' . $months[(int) date('n', $t)] . ' · ' . date('H:i', $t);
};
$startWindow = (int) $startWindow;
$matchCard = static function (object $m, bool $today) use ($tableId, $frDate, $startWindow): string {
    ob_start(); ?>
        <div class="sb-card sb-match mb-3<?= $today ? '' : ' sb-match-upcoming' ?>">
            <div class="sb-match-meta">
                <span class="badge <?= $m->type === 'official' ? 'bg-sb' : 'bg-secondary' ?>"><?= $m->type === 'official' ? 'Officielle' : 'Amicale' ?></span>
                <?php if ($m->scheduled_at): ?>
                <span><i class="far fa-clock me-1"></i><?= date('Y-m-d', strtotime($m->scheduled_at)) === M::now('Y-m-d') ? date('H:i', strtotime($m->scheduled_at)) : $frDate($m->scheduled_at) ?></span>
                <?php endif; ?>
                <span><?= esc(M::disciplineLabel($m)) ?><?= M::isPendingPlacement($m) ? '' : ' · ' . esc(M::distanceLabel($m)) ?><?= $m->innings_limit ? ' · ' . (int) $m->innings_limit . ' rep.' : '' ?></span>
                <?php if ($competition = M::competitionLabel($m)): ?><span><?= esc($competition) ?></span><?php endif; ?>
            </div>
            <div class="sb-match-players">
                <?php foreach (M::playerKeys($m) as $i => $p): ?>
                <?php if ($i > 0): ?><span class="sb-vs">vs</span><?php endif; ?>
                <div><strong><?= esc(M::playerLabel($m, $p)) ?></strong><small><?= esc($m->{$p . '_club'}) ?></small></div>
                <?php endforeach; ?>
            </div>
            <?php if ($today && M::isPendingPlacement($m)): ?>
            <span class="btn btn-outline-secondary disabled sb-start-locked">
                <i class="fas fa-hourglass-half me-2"></i>En attente du classement de la poule
            </span>
            <?php elseif ($today && M::canStartNow($m, $startWindow)): ?>
            <a href="<?= base_url('marquoir/billard/' . $tableId . '/demarrer/' . $m->id) ?>" class="btn btn-sb">
                <i class="fas fa-play me-2"></i>Démarrer
            </a>
            <?php elseif ($today): ?>
            <span class="btn btn-outline-secondary disabled sb-start-locked">
                <i class="fas fa-lock me-2"></i>Disponible à partir de <?= date('H:i', M::startAllowedAt($m, $startWindow)) ?>
            </span>
            <?php endif; ?>
        </div>
    <?php return (string) ob_get_clean();
};
?>
<?= view('public/scoreboard/_device', ['suggestedName' => $table->name]) ?>
<script>
// Envois de feuilles de match en retard (Chromebook fermé avant l'envoi, signature jamais faite) : sans bloquer la page
fetch(<?= json_encode(base_url('marquoir/api/envois')) ?>, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => {});
</script>
<div class="row g-4">
    <div class="col-lg-8">
        <h2 class="sb-title mb-3"><i class="fas fa-calendar-check me-2 sb-accent"></i>Rencontres du jour</h2>
        <?php if (!$startable): ?>
            <div class="sb-card text-secondary mb-3">Aucune rencontre prévue pour aujourd'hui.</div>
        <?php endif; ?>
        <?php foreach ($startable as $m): ?>
            <?= $matchCard($m, true) ?>
        <?php endforeach; ?>

        <?php if ($upcoming): ?>
        <h2 class="sb-title mt-5 mb-3"><i class="fas fa-calendar-alt me-2 sb-accent"></i>Rencontres programmées</h2>
        <?php foreach ($upcoming as $m): ?>
            <?= $matchCard($m, false) ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="col-lg-4">
        <?php foreach ($encounters as $enc): $e = $enc['encounter']; $st = $enc['state']; ?>
        <?php $labels = ['scheduled' => 'à jouer', 'live' => 'en cours', 'to_sign' => 'terminé — à signer', 'signed' => 'terminé, signé', 'done' => 'terminé', 'forfeit' => 'forfait']; ?>
        <div class="sb-card sb-encounter mb-4">
            <div class="sb-match-meta mb-1">
                <span class="badge bg-sb"><?= esc($e->competition) ?> · <?= esc($e->code) ?></span>
                <span>Rencontre d'équipe du jour</span>
            </div>
            <div class="sb-tile-name mb-2"><?= esc(\App\Models\ScoreboardTeamEncounterModel::homeLabel($e)) ?> – <?= esc(\App\Models\ScoreboardTeamEncounterModel::awayLabel($e)) ?></div>
            <?php foreach ($st['matches'] as $i => $item): $ok = in_array($item['state'], ['signed', 'done', 'forfeit'], true); ?>
            <div class="sb-encounter-match">
                <i class="<?= $ok ? 'fas fa-circle-check text-success' : ($item['state'] === 'live' ? 'fas fa-play text-warning' : ($item['state'] === 'to_sign' ? 'fas fa-signature text-warning' : 'far fa-clock text-secondary')) ?>"></i>
                <span>Match <?= $i + 1 ?></span> <small><?= $labels[$item['state']] ?></small>
            </div>
            <?php endforeach; ?>
            <?php $rc = $enc['recap']; ?>
            <?php if ($rc['state'] === 'sent'): ?>
            <div class="sb-encounter-mail ok"><i class="fas fa-envelope-circle-check me-2"></i>Feuille récapitulative envoyée à <?= $rc['at'] ?></div>
            <?php elseif ($rc['state'] === 'error'): ?>
            <div class="sb-encounter-mail ko"><i class="fas fa-triangle-exclamation me-2"></i>Échec de l'envoi de la feuille récapitulative : prévenez le DS</div>
            <?php elseif ($rc['state'] === 'waiting'): ?>
            <div class="sb-encounter-mail wait"><i class="fas fa-hourglass-half me-2"></i>Feuille récapitulative : envoi après les signatures, au plus tard à <?= $rc['due'] ?></div>
            <?php elseif ($rc['state'] === 'sending'): ?>
            <div class="sb-encounter-mail wait"><i class="fas fa-paper-plane me-2"></i>Envoi de la feuille récapitulative…</div>
            <?php elseif ($rc['state'] === 'matches'): ?>
            <div class="sb-encounter-mail wait"><i class="fas fa-envelope me-2"></i>Feuille récapitulative envoyée par e-mail après les <?= $rc['total'] ?> matchs</div>
            <?php endif; ?>
            <?php if ($enc['signedAt']): ?>
            <a href="<?= base_url('marquoir/rencontre/' . $e->id . '?billard=' . $table->id) ?>" class="btn btn-outline-success w-100 mt-3"><i class="fas fa-file-signature me-2"></i>Clôturée — feuille récapitulative</a>
            <?php elseif ($st['ready']): ?>
            <a href="<?= base_url('marquoir/rencontre/' . $e->id . '?billard=' . $table->id) ?>" class="btn btn-sb w-100 mt-3"><i class="fas fa-flag-checkered me-2"></i>Clôturer la rencontre</a>
            <?php else: ?>
            <a href="<?= base_url('marquoir/rencontre/' . $e->id . '?billard=' . $table->id) ?>" class="btn btn-outline-secondary w-100 mt-3"><i class="fas fa-lock me-2"></i>Clôture après les <?= count($st['matches']) ?> matchs</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php foreach ($pools as $pl): $pool = $pl['pool']; $sh = $pl['sheet']; $rc = $pl['mail']; $url = base_url('marquoir/poule-5-quilles/' . $pool->id . '?billard=' . $table->id); ?>
        <div class="sb-card sb-encounter mb-4">
            <div class="sb-match-meta mb-1">
                <span class="badge bg-sb">5 Quilles · <?= esc($pool->name) ?></span>
                <span><?= \App\Models\ScoreboardSkittlesPoolModel::NAME ?></span>
            </div>
            <?php foreach (\App\Models\ScoreboardSkittlesPoolModel::SLOTS as $slot => $letter): ?>
            <div class="small"><b><?= $letter ?></b> <?= esc(\App\Models\ScoreboardSkittlesPoolModel::playerName($pool, $slot)) ?></div>
            <?php endforeach; ?>
            <div class="mt-2"></div>
            <?php foreach ($sh['matches'] as $no => $item): [$a, $b] = $item['players']; $ok = $item['status'] === 'finished'; ?>
            <div class="sb-encounter-match">
                <i class="<?= $ok ? 'fas fa-circle-check text-success' : ($item['status'] === 'live' ? 'fas fa-play text-warning' : 'far fa-clock text-secondary') ?>"></i>
                <span>Match <?= $no ?> · <?= \App\Models\ScoreboardSkittlesPoolModel::letter($a) ?>/<?= \App\Models\ScoreboardSkittlesPoolModel::letter($b) ?></span>
                <small><?= $ok ? 'terminé' : ($item['status'] === 'live' ? 'en cours' : 'à jouer') ?></small>
            </div>
            <?php endforeach; ?>
            <?php if ($rc['state'] === 'sent'): ?>
            <div class="sb-encounter-mail ok"><i class="fas fa-envelope-circle-check me-2"></i>Feuille récapitulative envoyée à <?= $rc['at'] ?></div>
            <?php elseif ($rc['state'] === 'error'): ?>
            <div class="sb-encounter-mail ko"><i class="fas fa-triangle-exclamation me-2"></i>Échec de l'envoi de la feuille récapitulative : prévenez le DS</div>
            <?php elseif ($rc['state'] === 'waiting'): ?>
            <div class="sb-encounter-mail wait"><i class="fas fa-hourglass-half me-2"></i>Feuille récapitulative : envoi après les signatures, au plus tard à <?= $rc['due'] ?></div>
            <?php elseif ($rc['state'] === 'sending'): ?>
            <div class="sb-encounter-mail wait"><i class="fas fa-paper-plane me-2"></i>Envoi de la feuille récapitulative…</div>
            <?php endif; ?>
            <?php if ($pl['signedAt']): ?>
            <a href="<?= $url ?>" class="btn btn-outline-success w-100 mt-3"><i class="fas fa-file-signature me-2"></i>Clôturée — feuille récapitulative</a>
            <?php elseif ($sh['complete']): ?>
            <a href="<?= $url ?>" class="btn btn-sb w-100 mt-3"><i class="fas fa-flag-checkered me-2"></i>Clôturer la poule<?= $pool->signature_required ? ' (signatures)' : '' ?></a>
            <?php else: ?>
            <a href="<?= $url ?>" class="btn btn-outline-secondary w-100 mt-3"><i class="fas fa-lock me-2"></i>Clôture après les 3 matchs</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <button type="button" class="sb-tile sb-tile-cta mb-4" data-sb-friendly-open>
            <i class="fas fa-handshake"></i>
            <span class="sb-tile-name">Partie amicale</span>
            <small class="text-secondary">Choisir le type de partie</small>
        </button>
        <div class="sb-card sb-device mb-4">
            <?php if ($deviceTable && (int) $deviceTable->id === (int) $table->id): ?>
                <p class="mb-2"><i class="fas fa-laptop me-2 sb-accent"></i>Ce Chromebook est <b><?= esc($table->name) ?></b> : le marquoir s'ouvre directement ici.</p>
                <form method="post" action="<?= base_url('marquoir/billard/oublier') ?>" onsubmit="return confirm('Ce Chromebook ne sera plus associé à <?= esc($table->name, 'js') ?>.')">
                    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-unlink me-1"></i>Oublier</button>
                    <a href="<?= base_url('marquoir?tous=1') ?>" class="btn btn-sm btn-outline-secondary ms-1"><i class="fas fa-th-large me-1"></i>Tous les billards</a>
                </form>
            <?php else: ?>
                <?php if ($deviceTable): ?>
                <p class="small text-warning mb-2"><i class="fas fa-triangle-exclamation me-1"></i>Ce Chromebook est actuellement associé à <?= esc($deviceTable->name) ?>.</p>
                <?php endif; ?>
                <form method="post" action="<?= base_url('marquoir/billard/' . $table->id . '/ce-chromebook') ?>">
                    <button class="btn btn-outline-light w-100"><i class="fas fa-laptop me-2"></i>Ce Chromebook est « <?= esc($table->name) ?> »</button>
                </form>
                <small class="text-secondary d-block mt-2">Le marquoir s'ouvrira ensuite directement sur ce billard.</small>
            <?php endif; ?>
        </div>
        <?php if ($lastFinished): ?>
        <h3 class="h6 text-secondary text-uppercase mb-2">Dernières parties</h3>
        <ul class="list-unstyled sb-last">
            <?php foreach ($lastFinished as $m): ?>
            <li>
                <a href="<?= base_url('marquoir/match/' . $m->id) ?>">
                    <?php if (M::isTrio($m)): ?>
                    <span><?= implode(' <span class="text-secondary">·</span> ', array_map(static fn ($p) => esc($m->{$p . '_last_name'}) . ' <b>' . M::score($m, $p) . '</b>', M::playerKeys($m))) ?></span>
                    <?php else: ?>
                    <span><?= esc($m->p1_last_name) ?> <b><?= M::score($m, 'p1') ?></b></span>
                    <span class="text-secondary">–</span>
                    <span><b><?= M::score($m, 'p2') ?></b> <?= esc($m->p2_last_name) ?></span>
                    <?php endif; ?>
                    <?php if ($m->type === 'official'): ?>
                    <?php if (!M::isTrio($m) && !(int) $m->absent): ?>
                    <i class="ms-auto fas <?= $m->result_sent_at ? 'fa-envelope-circle-check text-success' : ($m->result_send_error ? 'fa-triangle-exclamation text-danger' : 'fa-envelope text-secondary') ?>"
                       title="<?= $m->result_sent_at ? 'Feuille de match envoyée à ' . date('H:i', strtotime($m->result_sent_at)) : ($m->result_send_error ? "Échec de l'envoi de la feuille de match" : 'Feuille de match pas encore envoyée') ?>"></i>
                    <span class="badge sb-badge-official ms-2">Officielle</span>
                    <?php else: ?>
                    <span class="badge sb-badge-official ms-auto">Officielle</span>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="badge text-bg-secondary ms-auto">Amicale</span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
<?php
$friendlyChoices = [
    ['amicale',                  'fas fa-handshake','Partie amicale à deux',              'Libre, cadre, bande ou 3 bandes', false],
    ['amicale-a-3',              'fas fa-users',    'Partie amicale à trois',             'Trois joueurs, à tour de rôle', false],
    ['amicale-5-quilles',        'skittles', 'Partie amicale aux 5 quilles',       'Coup par coup, fautes à l\'adversaire', false],
    ['amicale-salade',           'far fa-list-alt', 'Partie amicale à la Salade',         'Direct, Rouge, 1 - 2 ou 3 bandes, Bricoles, ...', false],
    ['amicale-salade-quick-pick','fas fa-shuffle',  'Partie amicale à la Salade « Quick Pick »', 'Comme la Salade MAIS le marquoir choisit la figure à jouer', false],
];
?>
<div class="sb-modal" id="sbFriendlyModal" role="dialog" aria-modal="true" aria-labelledby="sbFriendlyTitle">
    <div class="sb-modal-box">
        <div class="sb-modal-head">
            <h2 class="sb-title mb-0" id="sbFriendlyTitle"><i class="fas fa-handshake me-2 sb-accent"></i>Quelle partie amicale ?</h2>
            <button type="button" class="btn btn-outline-light" data-sb-friendly-close aria-label="Fermer"><i class="fas fa-xmark"></i></button>
        </div>
        <?php foreach ($friendlyChoices as $i => [$slug, $icon, $label, $sub, $soon]): ?>
        <a href="<?= base_url('marquoir/billard/' . $table->id . '/' . $slug) ?>" class="sb-choice<?= $soon ? ' soon' : '' ?>">
            <span class="sb-choice-key"><?= $i + 1 ?></span>
            <?php if ($icon === 'skittles'): ?>
            <?= view('public/scoreboard/_skittles', ['variant' => 'icon', 'uid' => 'skc', 'class' => 'sb-choice-svg']) ?>
            <?php else: ?>
            <i class="<?= $icon ?>"></i>
            <?php endif; ?>
            <span>
                <span class="sb-choice-name"><?= esc($label) ?></span>
                <small data-sub="<?= esc($sub) ?>"><?php if ($soon): ?><span class="badge text-bg-warning me-1"><i class="fas fa-person-digging me-1"></i>En construction</span><?php else: ?><?= esc($sub) ?><?php endif; ?></small>
            </span>
        </a>
        <?php endforeach; ?>
        <div class="sb-modal-keys"><kbd>1</kbd>–<kbd>5</kbd> choisir &nbsp;·&nbsp; <kbd>8</kbd> <kbd>2</kbd> se déplacer &nbsp;·&nbsp; <kbd>Entrée</kbd> valider &nbsp;·&nbsp; <kbd>⌫</kbd> fermer</div>
    </div>
</div>
<script>
// Choix du type de partie amicale : au doigt ou au pavé numérique (comme le menu du marquoir)
(function () {
    const modal = document.getElementById('sbFriendlyModal');
    const items = [...modal.querySelectorAll('.sb-choice')];
    let index = 0;
    const focus = (i) => {
        index = (i + items.length) % items.length;
        items.forEach((el, k) => el.classList.toggle('kbd-focus', k === index));
        items[index].focus({ preventScroll: true });
    };
    const open = () => { modal.classList.add('open'); focus(0); };
    const close = () => modal.classList.remove('open');
    document.querySelectorAll('[data-sb-friendly-open]').forEach((b) => b.addEventListener('click', open));
    modal.querySelector('[data-sb-friendly-close]').addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
    window.addEventListener('keydown', function (e) {
        if (!modal.classList.contains('open')) return;
        const code = e.code, key = e.key;
        const d = /^Numpad[0-9]$/.test(code) ? +code.slice(-1) : (/^[0-9]$/.test(key) ? +key : null);
        let handled = true;
        if (key === 'Escape' || key === 'Backspace' || code === 'NumpadDivide') close();
        else if (d !== null && d >= 1 && d <= items.length) items[d - 1].click();
        else if (d === 8 || key === 'ArrowUp') focus(index - 1);
        else if (d === 2 || key === 'ArrowDown') focus(index + 1);
        else if (key === 'Enter' || code === 'NumpadEnter') items[index].click();
        else handled = false;
        if (handled) e.preventDefault();
    }, true);
})();
</script>
<?php
$unlocks = [];
$pending = false;
foreach ($startable as $m) {
    if (M::isPendingPlacement($m)) $pending = true;
    elseif (!M::canStartNow($m, $startWindow)) $unlocks[] = M::startAllowedAt($m, $startWindow);
}
?>
<?php if ($pending): ?>
<script>setTimeout(() => location.reload(), 60 * 1000);</script>
<?php endif; ?>
<?php if ($unlocks): ?>
<script>setTimeout(() => location.reload(), <?= max(5, min($unlocks) - M::nowTs() + 2) ?> * 1000);</script>
<?php endif; ?>
<?= $this->endSection() ?>
