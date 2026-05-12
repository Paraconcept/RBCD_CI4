<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<!-- En-tête galerie -->
<div class="card card-outline card-primary mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <i class="fas fa-images mr-2"></i><?= esc($gallery->title) ?>
      <small class="text-muted ml-2"><?= $gallery->season ? esc($gallery->season) : '' ?></small>
    </h3>
    <div>
      <a href="<?= base_url('admin/galleries/' . $gallery->id . '/edit') ?>" class="btn btn-sm btn-default mr-1">
        <i class="fas fa-edit mr-1"></i>Modifier les infos
      </a>
      <a href="<?= base_url('admin/galleries') ?>" class="btn btn-sm btn-default">
        <i class="fas fa-arrow-left mr-1"></i>Retour
      </a>
    </div>
  </div>
</div>

<!-- Upload -->
<div class="card card-outline card-success mb-3">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Ajouter des photos</h3>
  </div>
  <form method="post" enctype="multipart/form-data"
        action="<?= base_url('admin/galleries/' . $gallery->id . '/photos/upload') ?>">
    <?= csrf_field() ?>
    <div class="card-body">
      <div class="input-group">
        <div class="custom-file">
          <input type="file" name="photos[]" id="photos" class="custom-file-input"
                 accept="image/jpeg,image/png,image/webp,image/gif" multiple>
          <label class="custom-file-label" for="photos">Choisir des photos…</label>
        </div>
        <div class="input-group-append">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-upload mr-1"></i>Uploader
          </button>
        </div>
      </div>
      <small class="form-text text-muted">JPG, PNG, WebP · plusieurs fichiers acceptés · max 10 Mo / photo</small>
    </div>
  </form>
</div>

<!-- Grille photos -->
<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      Photos <span class="badge badge-secondary ml-2"><?= count($photos) ?></span>
    </h3>
  </div>
  <div class="card-body">

    <?php if (empty($photos)): ?>
    <div class="text-center text-muted py-4">
      <i class="fas fa-images fa-2x mb-2 d-block" style="color:#ccc"></i>
      Aucune photo — utilisez le formulaire ci-dessus.
    </div>

    <?php else: ?>
    <div class="row">
      <?php foreach ($photos as $p): ?>
      <?php $isCover = ((int)$gallery->cover_photo_id === (int)$p->id); ?>
      <div class="col-6 col-md-3 col-lg-2 mb-3">
        <div class="gal-thumb <?= $isCover ? 'gal-thumb--cover' : '' ?>">
          <img src="<?= base_url('uploads/galleries/' . $gallery->id . '/' . $p->filename) ?>"
               alt="<?= esc($p->caption ?? $p->filename) ?>">
          <?php if ($isCover): ?>
            <span class="gal-cover-badge"><i class="fas fa-star mr-1"></i>Couverture</span>
          <?php endif; ?>
          <div class="gal-thumb-actions">
            <?php if (!$isCover): ?>
            <form method="post"
                  action="<?= base_url('admin/galleries/' . $gallery->id . '/photos/' . $p->id . '/cover') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-xs btn-warning" title="Définir comme couverture">
                <i class="fas fa-star"></i>
              </button>
            </form>
            <?php endif; ?>
            <form method="post"
                  action="<?= base_url('admin/galleries/' . $gallery->id . '/photos/' . $p->id . '/delete') ?>"
                  onsubmit="return confirm('Supprimer cette photo ?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
        <small class="d-block text-muted text-truncate mt-1" style="font-size:.7rem"
               title="<?= esc($p->filename) ?>"><?= esc($p->filename) ?></small>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.gal-thumb {
    position: relative;
    border-radius: 4px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    aspect-ratio: 4/3;
    background: #f0f0f0;
}
.gal-thumb--cover { border-color: #ffc107; }
.gal-thumb img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
}
.gal-cover-badge {
    position: absolute; top: 4px; left: 4px;
    background: #ffc107; color: #333;
    font-size: .65rem; font-weight: 700;
    padding: 2px 6px; border-radius: 3px;
}
.gal-thumb-actions {
    position: absolute; bottom: 4px; right: 4px;
    display: flex; gap: 4px;
    opacity: 0; transition: opacity .2s;
}
.gal-thumb:hover .gal-thumb-actions { opacity: 1; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$('#photos').on('change', function () {
    var n = this.files.length;
    $(this).closest('.custom-file').find('.custom-file-label')
           .text(n > 1 ? n + ' fichiers sélectionnés' : (this.files[0] ? this.files[0].name : 'Choisir des photos…'));
});
</script>
<?= $this->endSection() ?>
