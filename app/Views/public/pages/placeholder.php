<?= $this->extend('public/layouts/main') ?>
<?= $this->section('content') ?>

<div class="container pt-40 pb-60">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 text-center">
      <div class="p-5" style="border:2px dashed #dee2e6; border-radius:.75rem;">
        <i class="fas fa-hard-hat fa-3x mb-3" style="color:#84252B;"></i>
        <h3 class="mb-2"><?= esc($page_title) ?></h3>
        <p class="text-muted mb-4">Cette page est en cours de construction.<br>Elle sera disponible prochainement.</p>
        <a href="<?= base_url('/') ?>" class="btn btn-theme-colored2 text-white">
          <i class="fas fa-home me-2"></i>Retour à l'accueil
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
