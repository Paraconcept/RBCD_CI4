<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title mb-4"><i class="<?= esc($icon) ?> me-2 sb-accent"></i><?= esc($title) ?></h1>
<div class="sb-card text-center py-5">
    <i class="fas fa-person-digging fa-3x mb-3 text-warning"></i>
    <h2 class="h3">En construction</h2>
    <p class="text-secondary mb-0">Ce mode de jeu arrive bientôt sur le marquoir.</p>
</div>
<div class="mt-4">
    <a href="<?= base_url('marquoir/billard/' . $table->id) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Retour</a>
</div>
<?= $this->endSection() ?>
