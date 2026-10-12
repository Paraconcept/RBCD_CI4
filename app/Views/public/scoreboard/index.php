<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('content') ?>
<h1 class="sb-title mb-4">Choisissez votre billard</h1>
<?php if (!$tables): ?>
    <p class="text-secondary">Aucun billard n'est configuré. Le DS peut les ajouter dans l'administration.</p>
<?php endif; ?>
<div class="row g-4">
    <?php foreach ($tables as $t): ?>
    <div class="col-sm-6 col-lg-4">
        <a href="<?= base_url('marquoir/billard/' . $t->id) ?>" class="sb-tile">
            <img src="<?= base_url('assets/images/icon_billard_rouge_150x80.png') ?>" alt="" class="sb-tile-img" width="150" height="80">
            <span class="sb-tile-name"><?= esc($t->name) ?></span>
            <?php if ($t->live): ?>
                <span class="badge bg-danger"><i class="fas fa-circle fa-beat-fade me-1"></i>Partie en cours</span>
            <?php else: ?>
                <span class="badge bg-secondary">Libre</span>
            <?php endif; ?>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<p class="text-secondary small mt-4 mb-0">
    <i class="fas fa-lightbulb me-1"></i>Astuce : ajoutez la page de votre billard aux favoris du Chromebook pour y revenir directement.
</p>
<?= $this->endSection() ?>
