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

      <!-- Galerie associée -->
      <div class="form-group">
        <label for="gallery_id">
          <i class="fas fa-images mr-1"></i> Galerie photos associée
          <small class="text-muted ml-1">(lien affiché en bas de l'article)</small>
        </label>
        <select name="gallery_id" id="gallery_id" class="form-control">
          <option value="">— Aucune galerie associée —</option>
          <?php foreach ($galleries as $g): ?>
          <option value="<?= $g->id ?>"
            <?= old('gallery_id', $news->gallery_id ?? '') == $g->id ? 'selected' : '' ?>>
            <?= esc($g->title) ?>
            <?php if ($g->event_date): ?>(<?= date('d/m/Y', strtotime($g->event_date)) ?>)<?php endif; ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Galerie photos uploadées -->
      <?php if ($isEdit): ?>
      <hr>
      <div class="form-group mb-0">
        <label><i class="fas fa-images mr-1"></i> Galerie photos <small class="text-muted">(affichées sous l'article)</small></label>

        <?php if (!empty($galleryImages)): ?>
        <div class="d-flex flex-wrap mt-2 mb-3" id="gallery-thumbs">
          <?php foreach ($galleryImages as $gi): ?>
          <div class="position-relative mr-2 mb-2" style="width:100px;">
            <img src="<?= base_url('uploads/news/' . $gi->filename) ?>"
                 alt="" style="width:100px;height:75px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
            <button type="submit"
                    form="del-gi-<?= $gi->id ?>"
                    class="btn btn-danger btn-xs p-0 position-absolute"
                    style="top:2px;right:2px;width:18px;height:18px;line-height:16px;font-size:10px;border-radius:50%;"
                    title="Supprimer"
                    onclick="return confirm('Supprimer cette photo ?');">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="input-group">
          <div class="custom-file">
            <input type="file" name="gallery[]" id="gallery" class="custom-file-input"
                   accept="image/jpeg,image/png,image/webp" multiple>
            <label class="custom-file-label" for="gallery">Ajouter des photos…</label>
          </div>
        </div>
        <small class="form-text text-muted">JPG, PNG ou WebP · max 3 Mo par photo · sélection multiple possible</small>
      </div>
      <?php else: ?>
      <hr>
      <p class="text-muted small mb-0">
        <i class="fas fa-info-circle mr-1"></i>
        Enregistrez d'abord l'actualité pour pouvoir ajouter des photos à la galerie.
      </p>
      <?php endif; ?>

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

  <?php if ($isEdit && !empty($galleryImages)): ?>
  <?php foreach ($galleryImages as $gi): ?>
  <form id="del-gi-<?= $gi->id ?>" method="post"
        action="<?= base_url('admin/news/' . $news->id . '/gallery/' . $gi->id . '/delete') ?>">
    <?= csrf_field() ?>
  </form>
  <?php endforeach; ?>
  <?php endif; ?>

</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/js-beautify@1.15.1/js/lib/beautify-html.min.js"></script>
<script>
$(function () {

  // ── Summernote ────────────────────────────────────────────────────
  var specialChars = [
    '©','®','™','°','±','×','÷',
    '€','£','¥','¢','§','¶',
    '…','«','»','–','—',
    '≤','≥','≠','≈','∞','√',
    'α','β','γ','δ','μ','π','Ω',
    'æ','œ','ç','ñ','ü',
  ];

  // Panel caractères spéciaux attaché au body
  var $scPanel = $('<div id="sn-special-chars">').css({
    position: 'absolute', background: '#fff',
    border: '1px solid #ced4da', borderRadius: '4px',
    padding: '6px', zIndex: 10000, width: '222px',
    boxShadow: '0 2px 8px rgba(0,0,0,.15)',
  }).hide().appendTo('body');

  specialChars.forEach(function (ch) {
    $('<button type="button">').css({
      width: '30px', height: '30px', fontSize: '14px',
      margin: '1px', cursor: 'pointer',
      border: '1px solid #dee2e6', background: '#fff', borderRadius: '3px',
    })
    .text(ch).attr('title', ch)
    .on('mousedown', function (e) {
      e.preventDefault();
      $('#content').summernote('insertText', ch);
      $scPanel.hide();
    })
    .appendTo($scPanel);
  });

  $(document).on('mousedown', function (e) {
    if (!$(e.target).closest('#sn-special-chars, #btn-special-chars').length) {
      $scPanel.hide();
    }
  });

  $('#content').summernote({
    height: 320,
    toolbar: [
        ['style',   ['style']],
        ['font',    ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
        ['color',   ['color']],
        ['para',    ['ul', 'ol', 'paragraph']],
        ['table',   ['table']],
        ['insert',  ['link', 'picture', 'hr']],
        ['view',    ['fullscreen', 'codeview', 'help']],
    ],
    styleTags: ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
    lang: 'fr-FR',
    callbacks: {
      onInit: function () {
        var $toolbar = $('#content').next('.note-editor').find('.note-toolbar');

        // Boutons Indenter / Désindenter
        var $indentGrp = $('<div class="note-btn-group btn-group">');
        $('<button type="button" class="note-btn btn btn-default btn-sm" title="Indenter">')
          .html('<i class="fas fa-indent fa-fw"></i>')
          .on('mousedown', function (e) { e.preventDefault(); $('#content').summernote('indent'); })
          .appendTo($indentGrp);
        $('<button type="button" class="note-btn btn btn-default btn-sm" title="Désindenter">')
          .html('<i class="fas fa-outdent fa-fw"></i>')
          .on('mousedown', function (e) { e.preventDefault(); $('#content').summernote('outdent'); })
          .appendTo($indentGrp);

        // Bouton Formater le HTML
        var $fmtGrp = $('<div class="note-btn-group btn-group">');
        $('<button type="button" class="note-btn btn btn-default btn-sm" title="Formater / indenter le HTML">')
          .html('<i class="fas fa-code fa-fw"></i>')
          .on('mousedown', function (e) {
            e.preventDefault();
            var raw       = $('#content').summernote('code');
            var formatted = html_beautify(raw, {
              indent_size: 2,
              wrap_line_length: 0,
              preserve_newlines: false,
              end_with_newline: false,
            });
            $('#content').summernote('code', formatted);
          })
          .appendTo($fmtGrp);

        // Bouton Caractères spéciaux
        var $scGrp = $('<div class="note-btn-group btn-group">');
        $('<button id="btn-special-chars" type="button" class="note-btn btn btn-default btn-sm" title="Caractères spéciaux">')
          .html('<b style="font-family:serif;font-size:14px;">Ω</b>')
          .on('click', function (e) {
            var $btn = $(this);
            var off  = $btn.offset();
            $scPanel.css({ top: off.top + $btn.outerHeight() + 2, left: off.left });
            $scPanel.toggle();
          })
          .appendTo($scGrp);

        // Insertion dans la toolbar après le groupe 'para'
        var $paraGrp = $toolbar.find('.note-btn-group').eq(3); // para group (index 3)
        $indentGrp.insertAfter($paraGrp);
        $fmtGrp.appendTo($toolbar);
        $scGrp.appendTo($toolbar);
      }
    }
  });

  // Auto-slug
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
  var slugLocked = false;
  $('#title').on('blur', function () {
    if (slugLocked) return;
    var slug = titleToSlug($(this).val());
    if (slug) { $('#slug').val(slug); $('#slug-preview').text(slug); }
  });
  $('#slug').on('input', function () {
    slugLocked = true;
    $('#slug-preview').text($(this).val());
  });
  <?php endif; ?>

  // Label multi-upload galerie
  $('#gallery').on('change', function () {
    var count = this.files.length;
    $(this).closest('.custom-file').find('.custom-file-label')
      .text(count === 1 ? this.files[0].name : count + ' photos sélectionnées');
  });

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
