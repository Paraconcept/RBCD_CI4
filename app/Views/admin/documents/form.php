<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $document !== null; ?>

<?php if ($errors = session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <?= $isEdit ? 'Modifier le document' : 'Nouveau document PDF' ?>
    </h3>
  </div>
  <form method="post"
        action="<?= $isEdit ? base_url('admin/documents/' . $document->id . '/update') : base_url('admin/documents') ?>"
        enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card-body">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="title">Titre <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control"
                   placeholder="Ex : Statuts du club — version 2024"
                   value="<?= esc(old('title', $document->title ?? '')) ?>" required>
            <small class="form-text text-muted">Description visible dans les listes et menus.</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="slug">
              Slug <span class="text-danger">*</span>
              <small class="text-muted ml-1">/documents/<span id="slug-preview"><?= esc(old('slug', $document->slug ?? '')) ?></span></small>
            </label>
            <input type="text" name="slug" id="slug" class="form-control"
                   placeholder="ex : statuts-du-club"
                   value="<?= esc(old('slug', $document->slug ?? '')) ?>" required>
            <small class="form-text text-muted">Segment d'URL (auto-généré depuis le titre).</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="uploaded_at">Date d'upload</label>
            <input type="date" name="uploaded_at" id="uploaded_at" class="form-control"
                   value="<?= esc(old('uploaded_at', $document->uploaded_at ?? date('Y-m-d'))) ?>">
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Fichier existant -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="existing_file">Sélectionner un PDF existant</label>
            <select name="existing_file" id="existing_file" class="form-control">
              <option value="">— Aucun / garder l'actuel —</option>
              <?php foreach ($existingFiles as $fname): ?>
              <option value="<?= esc($fname) ?>"
                <?= ($document && $document->filename === $fname) ? 'selected' : '' ?>>
                <?= esc($fname) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Fichiers dans uploads/PDF/Documents/</small>
          </div>
        </div>
        <!-- Nouvel upload -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="pdf_file">
              Ou uploader un nouveau PDF
              <?php if ($isEdit && $document->filename): ?>
                <small class="text-muted ml-1">
                  — actuel : <a href="<?= base_url('uploads/PDF/Documents/' . $document->filename) ?>" target="_blank">
                    <i class="fas fa-file-pdf text-danger"></i> <?= esc($document->filename) ?>
                  </a>
                </small>
              <?php endif; ?>
            </label>
            <div class="input-group">
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="pdf_file" name="pdf_file" accept=".pdf">
                <label class="custom-file-label" for="pdf_file">Choisir un PDF…</label>
              </div>
            </div>
            <small class="form-text text-muted">L'upload est prioritaire sur la sélection ci-contre. Le nom du fichier est conservé tel quel.</small>
          </div>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Enregistrer' : 'Ajouter ce document' ?>
      </button>
      <a href="<?= base_url('admin/documents') ?>" class="btn btn-default ml-2">Annuler</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$('#pdf_file').on('change', function () {
  var name = $(this).val().split('\\').pop();
  $(this).next('.custom-file-label').text(name || 'Choisir un PDF…');
});

function titleToSlug(val) {
  return val.toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}
<?php if (!$isEdit): ?>
var slugLocked = false;
$('#title').on('input', function () {
  if (slugLocked) return;
  var slug = titleToSlug($(this).val());
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
</script>
<?= $this->endSection() ?>
