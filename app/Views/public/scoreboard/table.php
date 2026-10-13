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
                <div><strong><?= esc(M::playerLabel($m, 'p1')) ?></strong><small><?= esc($m->p1_club) ?></small></div>
                <span class="sb-vs">vs</span>
                <div><strong><?= esc(M::playerLabel($m, 'p2')) ?></strong><small><?= esc($m->p2_club) ?></small></div>
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
        <?php if ($lastFinished): ?>
        <h3 class="h6 text-secondary text-uppercase mb-2">Dernières parties</h3>
        <ul class="list-unstyled sb-last">
            <?php foreach ($lastFinished as $m): ?>
            <li>
                <a href="<?= base_url('marquoir/match/' . $m->id) ?>">
                    <span><?= esc($m->p1_last_name) ?> <b><?= M::score($m, 'p1') ?></b></span>
                    <span class="text-secondary">–</span>
                    <span><b><?= M::score($m, 'p2') ?></b> <?= esc($m->p2_last_name) ?></span>
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
