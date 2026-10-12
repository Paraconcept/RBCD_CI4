<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="sb-card text-center">
            <i class="fas fa-lock fa-2x mb-3 sb-accent"></i>
            <h1 class="h3 mb-3">Accès au marquoir</h1>
            <?php if (!$pinDefined): ?>
                <p class="text-secondary mb-0">
                    Aucun code PIN n'est encore défini.<br>
                    Le DS doit le configurer dans l'administration
                    (<em>Direction Sportive › Marquoir › Paramètres</em>).
                </p>
            <?php else: ?>
                <p class="text-secondary">Saisissez le code PIN du club.<br>Il ne sera plus demandé sur cet appareil.</p>
                <form method="post" action="<?= base_url('marquoir/acces') ?>">
                    <input type="password" name="pin" inputmode="numeric" autocomplete="off" autofocus required
                           class="form-control form-control-lg text-center mb-3 sb-pin">
                    <button class="btn btn-sb btn-lg w-100"><i class="fas fa-unlock me-2"></i>Valider</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
