<?= $this->extend('admin/layouts/auth') ?>
<?= $this->section('content') ?>

<div class="login-box">
    <div class="login-logo">
        <b>RBC</b> Disonais
        <br><small class="text-muted" style="font-size:.75rem;font-weight:400;">Administration</small>
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control <?= (isset($errors['email'])) ? 'is-invalid' : '' ?>"
                           placeholder="Email" value="<?= old('email') ?>" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control <?= (isset($errors['password'])) ? 'is-invalid' : '' ?>"
                           placeholder="Mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-1"></i> Connexion
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
