<?= $this->extend('public/layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card card-outline card-primary mt-4">
            <div class="card-header text-center">
                <h4 class="mb-0"><i class="fas fa-lock mr-2"></i>Espace membres</h4>
            </div>
            <div class="card-body">

                <?php if ($errors = session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <div><?= esc($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error = session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= esc($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('connexion') ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Adresse e-mail</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= esc(old('email')) ?>" autofocus required>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt mr-1"></i>Se connecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
