<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = ($news !== null); ?>

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
      <i class="fas fa-newspaper mr-2"></i>
      <?= $isEdit ? 'Modifier l\'actualité' : 'Nouvelle actualité' ?>
    </h3>
  </div>

  <form method="post" enctype="multipart/form-data"
        action="<?= $isEdit ? base_url('admin/news/' . $news->id . '/update') : base_url('admin/news') ?>">
    <?= csrf_field() ?>

    <div class="card-body">

      <!-- Titre + Slug -->
      <div class="row">
        <div class="col-md-7">
          <div class="form-group">
            <label for="title">Titre <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control"
                   value="<?= esc(old('title', $news->title ?? '')) ?>" required>
          </div>
        </div>
        <div class="col-md-5">
          <div class="form-group">
            <label for="slug">
              Slug (URL) <span class="text-danger">*</span>
              <small class="text-muted ml-1">/actualites/<span id="slug-preview"><?= esc(old('slug', $news->slug ?? '')) ?></span></small>
            </label>
            <input type="text" name="slug" id="slug" class="form-control"
                   value="<?= esc(old('slug', $news->slug ?? '')) ?>" required>
          </div>
        </div>
      </div>

      <!-- Extrait -->
      <div class="form-group">
        <label for="excerpt">Extrait <small class="text-muted">(affiché dans la liste — max 500 caractères)</small></label>
        <textarea name="excerpt" id="excerpt" class="form-control" rows="2"
                  maxlength="500"><?= esc(old('excerpt', $news->excerpt ?? '')) ?></textarea>
      </div>

      <!-- Contenu WYSIWYG -->
      <div class="form-group">
        <label for="content">Contenu <span class="text-danger">*</span></label>
        <textarea name="content" id="content" class="form-control"><?= old('content', $news->content ?? '') ?></textarea>
      </div>

      <!-- Image + Date + Statut -->
      <div class="row">

        <div class="col-md-5">
          <div class="form-group">
            <label>Image d'illustration</label>
            <?php if ($isEdit && $news->image): ?>
            <div class="mb-2">
              <img id="img-preview" src="<?= base_url('uploads/news/' . $news->image) ?>"
                   alt="Aperçu" style="max-height:120px;border-radius:4px;border:1px solid #dee2e6;">
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" name="remove_image" id="remove_image" value="1" class="custom-control-input">
              <label class="custom-control-label text-danger" for="remove_image">Supprimer l'image</label>
            </div>
            <?php else: ?>
            <div class="mb-2">
              <img id="img-preview" src="" alt="" style="max-height:120px;border-radius:4px;border:1px solid #dee2e6;display:none;">
            </div>
            <?php endif; ?>
            <div class="input-group">
              <div class="custom-file">
                <input type="file" name="image" id="image" class="custom-file-input"
                       accept="image/jpeg,image/png,image/webp">
                <label class="custom-file-label" for="image">
                  <?= ($isEdit && $news->image) ? 'Remplacer l\'image…' : 'Choisir une image…' ?>
                </label>
              </div>
            </div>
            <small class="form-text text-muted">JPG, PNG ou WebP · max 3 Mo</small>
          </div>
        </div>

        <div class="col-md-3">
          <div class="form-group">
            <label for="published_at">Date de publication</label>
            <input type="date" name="published_at" id="published_at" class="form-control"
                   value="<?= esc(old('published_at', $news->published_at ?? '')) ?>">
            <small class="form-text text-muted">Laisser vide = sans date</small>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label>Statut</label>
            <div class="custom-control custom-switch mt-1">
              <input type="hidden" name="is_published" value="0">
              <input type="checkbox" name="is_published" id="is_published" value="1"
                     class="custom-control-input"
                     <?= old('is_published', $news->is_published ?? 0) ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_published">
                <span class="text-success font-weight-bold">Publié</span>
                <span class="text-muted"> / Brouillon</span>
              </label>
            </div>
            <small class="form-text text-muted">
              Une actualité publiée est visible uniquement si la date est atteinte.
            </small>
          </div>
        </div>

      </div>

    </div><!-- /card-body -->

    <div class="card-footer d-flex justify-content-between">
      <a href="<?= base_url('admin/news') ?>" class="btn btn-default">
        <i class="fas fa-arrow-left mr-1"></i> Retour
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>
        <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'actualité' ?>
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(function () {

  // Summernote
  $('#content').summernote({
    height: 320,
    toolbar: [
      ['style',  ['style']],
      ['font',   ['bold', 'italic', 'underline', 'clear']],
      ['para',   ['ul', 'ol', 'paragraph']],
      ['insert', ['link', 'picture', 'hr']],
      ['view',   ['fullscreen', 'codeview']],
    ],
    lang: 'fr-FR',
  });

  // Auto-slug depuis le titre (création uniquement)
  <?php if (!$isEdit): ?>
  var slugLocked = false;
  $('#title').on('input', function () {
    if (slugLocked) return;
    var slug = $(this).val()
      .toLowerCase()
      .normalize('NFD').replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    $('#slug').val(slug);
    $('#slug-preview').text(slug);
  });
  $('#slug').on('input', function () {
    slugLocked = true;
    $('#slug-preview').text($(this).val());
  });
  <?php else: ?>
  $('#slug').on('input', function () {
    $('#slug-preview').text($(this).val());
  });
  <?php endif; ?>

  // Aperçu image
  $('#image').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      $('#img-preview').attr('src', e.target.result).show();
    };
    reader.readAsDataURL(file);
    $(this).closest('.custom-file').find('.custom-file-label').text(file.name);
  });

});
</script>
<?= $this->endSection() ?>
