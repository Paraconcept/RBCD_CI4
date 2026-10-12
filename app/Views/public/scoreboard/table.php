<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="row g-4">
    <div class="col-lg-8">
        <h2 class="sb-title mb-3"><i class="fas fa-calendar-check me-2 sb-accent"></i>Rencontres prévues</h2>
        <?php if (!$startable): ?>
            <div class="sb-card text-secondary">Aucune rencontre prévue pour aujourd'hui.</div>
        <?php endif; ?>
        <?php foreach ($startable as $m): ?>
        <div class="sb-card sb-match mb-3">
            <div class="sb-match-meta">
                <span class="badge <?= $m->type === 'official' ? 'bg-sb' : 'bg-secondary' ?>"><?= $m->type === 'official' ? 'Officielle' : 'Amicale' ?></span>
                <?php if ($m->scheduled_at): ?><span><i class="far fa-clock me-1"></i><?= date('d/m H:i', strtotime($m->scheduled_at)) ?></span><?php endif; ?>
                <span><?= esc(M::disciplineLabel($m)) ?> · <?= esc(M::distanceLabel($m)) ?><?= $m->innings_limit ? ' · ' . (int) $m->innings_limit . ' rep.' : '' ?></span>
                <?php if ($m->competition): ?><span><?= esc($m->competition) ?></span><?php endif; ?>
            </div>
            <div class="sb-match-players">
                <div><strong><?= esc(M::playerLabel($m, 'p1')) ?></strong><small><?= esc($m->p1_club) ?></small></div>
                <span class="sb-vs">vs</span>
                <div><strong><?= esc(M::playerLabel($m, 'p2')) ?></strong><small><?= esc($m->p2_club) ?></small></div>
            </div>
            <a href="<?= base_url('marquoir/billard/' . $table->id . '/demarrer/' . $m->id) ?>" class="btn btn-sb">
                <i class="fas fa-play me-2"></i>Démarrer
            </a>
        </div>
        <?php endforeach; ?>
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
                    <span><?= esc($m->p1_last_name) ?> <b><?= (int) $m->p1_points ?></b></span>
                    <span class="text-secondary">–</span>
                    <span><b><?= (int) $m->p2_points ?></b> <?= esc($m->p2_last_name) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
