<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('styles') ?>
<meta http-equiv="refresh" content="30">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title mb-1"><i class="fas fa-tower-broadcast me-2 sb-accent"></i>Rencontres du jour</h1>
<p class="text-secondary mb-4"><?= M::now('d/m/Y') ?> · cliquez sur « Visionner » pour suivre une partie en direct.</p>

<?php if (!$matches): ?>
    <div class="sb-card text-secondary">Aucune rencontre officielle aujourd'hui au RBC Disonais.</div>
<?php endif; ?>

<?php foreach ($matches as $m): ?>
<div class="sb-card sb-match mb-3">
    <div class="sb-match-meta">
        <?php if ($m->status === 'live'): ?>
            <span class="badge bg-danger"><i class="fas fa-circle fa-beat-fade me-1"></i>En direct</span>
        <?php elseif ($m->status === 'finished'): ?>
            <span class="badge bg-success">Terminée</span>
        <?php else: ?>
            <span class="badge bg-secondary"><i class="far fa-clock me-1"></i><?= $m->scheduled_at ? 'À ' . date('H:i', strtotime($m->scheduled_at)) : 'À venir' ?></span>
        <?php endif; ?>
        <span class="badge <?= $m->type === 'official' ? 'bg-sb' : 'bg-secondary' ?>"><?= $m->type === 'official' ? 'Officielle' : 'Amicale' ?></span>
        <span><?= esc(M::disciplineLabel($m)) ?> · <?= esc(M::distanceLabel($m)) ?></span>
        <?php if ($competition = M::competitionLabel($m)): ?><span><?= esc($competition) ?></span><?php endif; ?>
    </div>
    <div class="sb-match-players">
        <div><strong><?= esc(M::playerLabel($m, 'p1')) ?></strong><small><?= esc($m->p1_club) ?></small></div>
        <?php if ($m->status === 'scheduled'): ?>
            <span class="sb-vs">vs</span>
        <?php else: ?>
            <span class="sb-live-score"><?= M::score($m, 'p1') ?> – <?= M::score($m, 'p2') ?><?= M::isSets($m) ? ' <small>sets</small>' : '' ?></span>
        <?php endif; ?>
        <div><strong><?= esc(M::playerLabel($m, 'p2')) ?></strong><small><?= esc($m->p2_club) ?></small></div>
    </div>
    <a href="<?= base_url('en-direct/' . $m->id) ?>" class="btn <?= $m->status === 'live' ? 'btn-sb' : 'btn-outline-light' ?>">
        <i class="fas fa-eye me-2"></i>Visionner
    </a>
</div>
<?php endforeach; ?>

<p class="text-secondary small mt-4 mb-0"><i class="fas fa-rotate me-1"></i>La liste se met à jour automatiquement.</p>
<?= $this->endSection() ?>
