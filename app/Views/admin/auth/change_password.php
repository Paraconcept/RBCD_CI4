<?= $this->extend('admin/layouts/auth') ?>
<?= $this->section('content') ?>

<div class="login-box">
    <div class="login-logo">
        <b>RBC</b> Disonais
        <br><small class="text-muted" style="font-size:.75rem;font-weight:400;">Administration</small>
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <div class="alert alert-warning">
                <i class="fas fa-key mr-1"></i>
                Vous utilisez encore le mot de passe par défaut.<br>
                <strong>Choisissez un mot de passe personnel avant de continuer.</strong>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <?php $errors = session()->getFlashdata('errors') ?? []; ?>

            <form action="<?= base_url('admin/change-password') ?>" method="post" autocomplete="off">
                <?= csrf_field() ?>

                <div class="input-group mb-1">
                    <input type="password" name="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                           placeholder="Nouveau mot de passe" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
                <small class="text-muted d-block mb-3">Minimum 8 caractères.</small>

                <div class="input-group mb-3">
                    <input type="password" name="password_confirm"
                           class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                           placeholder="Confirmer le mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fas fa-lock"></i></div>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-1"></i> Enregistrer mon mot de passe
                </button>

            </form>

            <div class="mt-3 text-center">
                <a href="<?= base_url('admin/logout') ?>" class="text-muted small">
                    <i class="fas fa-sign-out-alt mr-1"></i> Se déconnecter
                </a>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
