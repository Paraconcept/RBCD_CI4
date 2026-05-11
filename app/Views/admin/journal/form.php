<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $issue !== null; ?>

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
      <?= $isEdit ? 'Modifier le numéro' : 'Nouveau numéro de "Partie Libre"' ?>
    </h3>
  </div>
  <form method="post"
        action="<?= $isEdit ? base_url('admin/journal/' . $issue->id . '/update') : base_url('admin/journal') ?>"
        enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card-body">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="title">Titre <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control"
                   placeholder="Ex : Novembre 2024"
                   value="<?= esc(old('title', $issue->title ?? '')) ?>" required>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="published_date">Date de parution</label>
            <input type="date" name="published_date" id="published_date" class="form-control"
                   value="<?= esc(old('published_date', $issue->published_date ?? '')) ?>">
          </div>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <div class="form-group">
            <div class="custom-control custom-switch mt-2">
              <input type="checkbox" class="custom-control-input" id="is_published" name="is_published" value="1"
                     <?= old('is_published', $issue->is_published ?? 1) ? 'checked' : '' ?>>
              <label class="custom-control-label" for="is_published">Publié</label>
            </div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="description">Résumé <small class="text-muted">(optionnel)</small></label>
        <textarea name="description" id="description" class="form-control" rows="2"
                  placeholder="Brève présentation du contenu…"><?= esc(old('description', $issue->description ?? '')) ?></textarea>
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
                <?= ($issue && $issue->file_path === $fname) ? 'selected' : '' ?>>
                <?= esc($fname) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Fichiers dans uploads/PDF/PartieLibre/</small>
          </div>
        </div>
        <!-- Nouvel upload -->
        <div class="col-md-6">
          <div class="form-group">
            <label for="pdf_file">
              Ou uploader un nouveau PDF
              <?php if ($isEdit && $issue->file_path): ?>
                <small class="text-muted ml-1">
                  — actuel : <a href="<?= base_url('uploads/PDF/PartieLibre/' . $issue->file_path) ?>" target="_blank">
                    <i class="fas fa-file-pdf text-danger"></i> voir
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
            <small class="form-text text-muted">L'upload est prioritaire sur la sélection ci-contre.</small>
          </div>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Enregistrer' : 'Ajouter ce numéro' ?>
      </button>
      <a href="<?= base_url('admin/journal') ?>" class="btn btn-default ml-2">Annuler</a>
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
</script>
<?= $this->endSection() ?>
