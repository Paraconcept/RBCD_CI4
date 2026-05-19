<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Paramètres du site</h3>
  </div>
  <form method="post" action="<?= base_url('admin/settings/save') ?>">
    <?= csrf_field() ?>
    <div class="card-body">

      <h5 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.06em;">
        <i class="fas fa-bullhorn mr-1"></i> Actualités
      </h5>

      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="news_per_page">Nombre de news par page <small class="text-muted">(page d'accueil)</small></label>
            <input type="number" name="news_per_page" id="news_per_page"
                   class="form-control" min="1" max="50"
                   value="<?= (int) $news_per_page ?>">
            <small class="form-text text-muted">Entre 1 et 50. Actuellement : <strong><?= (int) $news_per_page ?></strong></small>
          </div>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-2"></i>Enregistrer
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
