<?= $this->extend('public/layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card card-outline card-primary mt-4">
            <div class="card-header text-center">
                <h4 class="mb-0"><i class="fas fa-lock me-2"></i><?= esc($page_title) ?></h4>
            </div>
            <div class="card-body">

                <?php if ($msg = session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?= esc($msg) ?>
                    </div>
                <?php endif; ?>

                <p class="text-muted mb-4" style="font-size:.95rem">
                    Bonjour <strong><?= esc($member->first_name . ' ' . $member->last_name) ?></strong>,
                    choisissez un mot de passe d'au moins 8 caractères.
                </p>

                <form method="POST" action="<?= base_url('connexion/reinitialiser/' . esc($token)) ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="••••••••" minlength="8" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                               placeholder="••••••••" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-theme-colored2 text-white btn-block mt-2">
                        <i class="fas fa-save me-2"></i>Enregistrer le mot de passe
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
