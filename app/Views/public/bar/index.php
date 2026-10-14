<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span id="barOffline" class="badge bg-danger ms-auto" hidden><i class="fas fa-wifi me-1"></i>Connexion perdue</span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="bar-grid" id="barGrid"></div>
<p class="text-secondary small mt-4 mb-0 text-center">
    <i class="fas fa-hand-pointer me-1"></i>Touchez un billard allumé une fois le service terminé.
</p>

<div class="bar-unlock" id="barUnlock">
    <div>
        <i class="fas fa-volume-high fa-3x mb-3"></i>
        <div class="fs-3 fw-bold">Touchez l'écran pour démarrer</div>
        <div class="text-secondary">La tablette pourra alors sonner à chaque appel.</div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>window.BAR_CONFIG = <?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= base_url('assets/scoreboard/bar.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/bar.js') ?>"></script>
<?= $this->endSection() ?>
