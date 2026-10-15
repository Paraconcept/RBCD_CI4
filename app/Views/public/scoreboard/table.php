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
                <span><?= esc(M::disciplineLabel($m)) ?> · <?= esc(M::distanceLabel($m)) ?><?= $m->innings_limit ? ' · ' . (int) $m->innings_limit . ' rep.' : '' ?></span>
                <?php if ($competition = M::competitionLabel($m)): ?><span><?= esc($competition) ?></span><?php endif; ?>
            </div>
            <div class="sb-match-players">
                <?php foreach (M::playerKeys($m) as $i => $p): ?>
                <?php if ($i > 0): ?><span class="sb-vs">vs</span><?php endif; ?>
                <div><strong><?= esc(M::playerLabel($m, $p)) ?></strong><small><?= esc($m->{$p . '_club'}) ?></small></div>
                <?php endforeach; ?>
            </div>
            <?php if ($today && M::canStartNow($m, $startWindow)): ?>
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
        <a href="<?= base_url('marquoir/billard/' . $table->id . '/amicale') ?>" class="sb-tile sb-tile-cta mb-4">
            <i class="fas fa-handshake"></i>
            <span class="sb-tile-name">Partie amicale</span>
            <small class="text-secondary">Lancer une nouvelle partie</small>
        </a>
        <a href="<?= base_url('marquoir/billard/' . $table->id . '/amicale-a-3') ?>" class="sb-tile sb-tile-cta mb-4">
            <i class="fas fa-users"></i>
            <span class="sb-tile-name">Partie amicale à 3</span>
            <small class="text-secondary">Trois joueurs, à tour de rôle</small>
        </a>
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
                    <span class="badge sb-badge-official ms-auto">Officielle</span>
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
$unlocks = [];
foreach ($startable as $m) {
    if (!M::canStartNow($m, $startWindow)) $unlocks[] = M::startAllowedAt($m, $startWindow);
}
?>
<?php if ($unlocks): ?>
<script>setTimeout(() => location.reload(), <?= max(5, min($unlocks) - M::nowTs() + 2) ?> * 1000);</script>
<?php endif; ?>
<?= $this->endSection() ?>
