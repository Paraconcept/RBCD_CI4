<?= $this->extend('public/layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card card-outline card-primary mt-4 mb-4">
            <div class="card-header text-center">
                <h4 class="mb-0"><i class="fas fa-key me-2"></i><?= esc($page_title) ?></h4>
            </div>
            <div class="card-body">

                <p class="text-muted mb-4" style="font-size:.95rem">
                    Entrez l'adresse e-mail liée à votre profil membre. Vous recevrez un lien pour créer ou réinitialiser votre mot de passe.
                </p>

                <form method="POST" action="<?= base_url('connexion/mot-de-passe-oublie') ?>">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" name="email" id="email" class="form-control"
                               placeholder="votre@email.com" autofocus required>
                    </div>

                    <button type="submit" class="btn btn-theme-colored2 text-white btn-block mt-2">
                        <i class="fas fa-paper-plane me-2"></i>Envoyer le lien
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= base_url('connexion') ?>" class="text-muted" style="font-size:.9rem">
                        <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
