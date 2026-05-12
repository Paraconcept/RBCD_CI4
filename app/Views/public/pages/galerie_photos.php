<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-30">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center"><?= esc($gallery->title) ?></h3>
          <div class="heading-border-line"></div>
          <?php if ($gallery->description): ?>
          <p class="heading-description text-center mt-20"><?= esc($gallery->description) ?></p>
          <?php endif; ?>
          <?php if ($gallery->event_date): ?>
          <p class="text-center text-muted mt-10" style="font-size:.85rem">
            <i class="far fa-calendar-alt me-1"></i>
            <?= date('d/m/Y', strtotime($gallery->event_date)) ?>
            <?= $gallery->season ? ' · Saison ' . esc($gallery->season) : '' ?>
          </p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Retour -->
    <div class="row mb-20">
      <div class="col-12">
        <a href="<?= base_url('galeries') ?>" class="btn-gal-back">
          <i class="fas fa-arrow-left me-2"></i>Toutes les galeries
        </a>
      </div>
    </div>

    <!-- Masonry -->
    <?php if (empty($photos)): ?>
    <div class="row">
      <div class="col-md-8 offset-md-2 text-center text-muted py-40">
        <i class="fas fa-images fa-3x mb-20" style="color:#ccc"></i>
        <p>Cette galerie ne contient pas encore de photos.</p>
      </div>
    </div>

    <?php else: ?>
    <div class="gal-masonry">
      <?php foreach ($photos as $p): ?>
      <?php $url = base_url('uploads/galleries/' . $gallery->id . '/' . $p->filename); ?>
      <div class="gal-masonry-item">
        <a href="<?= $url ?>" class="glightbox" data-gallery="gallery-<?= $gallery->id ?>"
           <?= $p->caption ? 'data-description="' . esc($p->caption) . '"' : '' ?>>
          <img src="<?= $url ?>" alt="<?= esc($p->caption ?? $gallery->title) ?>" loading="lazy">
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/css/glightbox.min.css">
<style>
.gal-masonry {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
@media (max-width: 991px) { .gal-masonry { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 575px)  { .gal-masonry { grid-template-columns: repeat(2, 1fr); } }

.gal-masonry-item {
    overflow: hidden;
    border-radius: 4px;
    aspect-ratio: 4/3;
}
.gal-masonry-item a { display: block; height: 100%; }
.gal-masonry-item img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform .3s, filter .3s;
}
.gal-masonry-item a:hover img {
    transform: scale(1.03);
    filter: brightness(.9);
}

.btn-gal-back {
    display: inline-block;
    border: 1px solid #84252B; color: #84252B;
    border-radius: 4px; padding: 7px 20px;
    font-size: .85rem; font-weight: 600; text-decoration: none;
    transition: background .2s, color .2s;
}
.btn-gal-back:hover { background: #84252B; color: #fff; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.0/dist/js/glightbox.min.js"></script>
<script>
GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
</script>
<?= $this->endSection() ?>
