<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('styles') ?>
<meta http-equiv="refresh" content="15">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="sb-card text-center py-5">
            <i class="far fa-clock fa-3x sb-accent mb-3"></i>
            <h1 class="sb-title mb-2">La partie n'a pas encore commencé</h1>
            <p class="text-secondary mb-4">
                <?= esc(M::disciplineLabel($match)) ?> · <?= esc(M::distanceLabel($match)) ?>
                <?= ($competition = M::competitionLabel($match)) ? ' · ' . esc($competition) : '' ?>
                <?php if ($match->scheduled_at): ?><br>Prévue à <?= date('H:i', strtotime($match->scheduled_at)) ?><?php endif; ?>
            </p>
            <div class="sb-match-players justify-content-center mb-4">
                <div><strong><?= esc(M::playerLabel($match, 'p1')) ?></strong><small><?= esc($match->p1_club) ?></small></div>
                <span class="sb-vs">vs</span>
                <div><strong><?= esc(M::playerLabel($match, 'p2')) ?></strong><small><?= esc($match->p2_club) ?></small></div>
            </div>
            <p class="text-secondary small mb-4"><i class="fas fa-rotate me-1"></i>Cette page affichera le marquoir dès le début de la partie.</p>
            <a href="<?= base_url('en-direct') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Rencontres du jour</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
