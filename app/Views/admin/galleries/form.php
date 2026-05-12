<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = ($gallery !== null); ?>

<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
      <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
        <li><?= esc($e) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-images mr-2"></i>
      <?= $isEdit ? 'Modifier la galerie' : 'Nouvelle galerie' ?>
    </h3>
  </div>

  <form method="post"
        action="<?= $isEdit ? base_url('admin/galleries/' . $gallery->id . '/update') : base_url('admin/galleries') ?>">
    <?= csrf_field() ?>

    <div class="card-body">

      <div class="form-group">
        <label for="title">Titre <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title" class="form-control" required
               value="<?= esc(old('title', $gallery->title ?? '')) ?>"
               placeholder="ex : Tournoi de Noël 2025">
      </div>

      <div class="form-group">
        <label for="slug">Slug <small class="text-muted">(auto-généré si vide)</small></label>
        <input type="text" name="slug" id="slug" class="form-control"
               value="<?= esc(old('slug', $gallery->slug ?? '')) ?>"
               placeholder="ex : tournoi-de-noel-2025">
        <small class="form-text text-muted">Utilisé dans l'URL publique.</small>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control" rows="3"
                  placeholder="Quelques mots sur cette galerie…"><?= esc(old('description', $gallery->description ?? '')) ?></textarea>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="event_date">Date de l'événement</label>
            <input type="date" name="event_date" id="event_date" class="form-control"
                   value="<?= esc(old('event_date', $gallery->event_date ?? '')) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="season">Saison</label>
            <select name="season" id="season" class="form-control">
              <option value="">— Aucune —</option>
              <?php
                $currentYear = (int) ANNEE_1;
                $selected    = old('season', $gallery->season ?? '');
                for ($y = $currentYear; $y >= 2018; $y--):
                  $s = $y . '-' . ($y + 1);
              ?>
              <option value="<?= $s ?>" <?= $selected === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" name="is_published" id="is_published" value="1"
                     class="custom-control-input"
                     <?= old('is_published', $gallery->is_published ?? 1) ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_published">Publier la galerie</label>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="<?= base_url('admin/galleries') ?>" class="btn btn-default">
        <i class="fas fa-arrow-left mr-1"></i>Retour
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>
        <?= $isEdit ? 'Enregistrer' : 'Créer la galerie' ?>
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-génère le slug depuis le titre (si le champ slug est vide)
$('#title').on('input', function () {
  if ($('#slug').val() !== '') return;
  var slug = $(this).val()
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/[\s-]+/g, '-');
  $('#slug').attr('placeholder', slug);
});
</script>
<?= $this->endSection() ?>
