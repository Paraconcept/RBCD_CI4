<?= $this->extend('public/layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card card-outline card-primary mt-4 mb-4">
            <div class="card-header text-center">
                <h4 class="mb-0"><i class="fas fa-user-lock me-2"></i>Contenu réservé aux membres</h4>
            </div>
            <div class="card-body">

                <?php if ($errors = session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e): ?>
                            <div><?= esc($e) ?></div>
                        <?php endforeach; ?>
                    </div>
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

                    <button type="submit" class="btn btn-theme-colored2 text-white btn-block mt-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="<?= base_url('connexion/mot-de-passe-oublie') ?>" class="text-muted" style="font-size:.9rem">
                        <i class="fas fa-key me-1"></i>Première connexion / Mot de passe oublié ?
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
