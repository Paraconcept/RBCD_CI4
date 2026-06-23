<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">

        <!-- Titre -->
        <div class="news-detail-header mb-30">
          <?php if ($news->published_at): ?>
          <p class="news-detail-date">
            <i class="far fa-calendar-alt me-1"></i>
            <?= date('d/m/Y', strtotime($news->published_at)) ?>
          </p>
          <?php endif; ?>
          <h2 class="news-detail-title"><?= esc($news->title) ?></h2>
          <?php if ($news->excerpt): ?>
          <p class="news-detail-excerpt"><?= esc($news->excerpt) ?></p>
          <?php endif; ?>
        </div>

        <!-- Image -->
        <?php if ($news->image): ?>
        <div class="news-detail-img-wrap mb-30">
          <img src="<?= base_url('uploads/news/' . $news->image) ?>"
               alt="<?= esc($news->title) ?>" class="news-detail-img">
        </div>
        <?php endif; ?>

        <!-- Contenu -->
        <div class="news-detail-content">
          <?= $news->content ?>
        </div>

        <!-- Galerie photos -->
        <?php if (!empty($galleryImages)): ?>
        <div class="separator text-center my-4">
          <img src="https://rbcd.be/assets/images/billiard-chalk.png"
               alt="Séparateur Craie de billard"
               style="width:20px;opacity:0.7;margin: 0 10px;">
        </div>
        <div class="news-gallery mb-10">
          <h3 class="news-gallery-title heading-title mb-4">
            <i class="fas fa-images me-2"></i>Photos associées
          </h3>
          <div class="row g-2">
            <?php foreach ($galleryImages as $gi): ?>
            <div class="col-6 col-sm-4 col-md-3">
              <a href="<?= base_url('uploads/news/' . $gi->filename) ?>"
                 class="glightbox news-gallery-item"
                 data-gallery="news-gallery"
                 data-type="image">
                <img src="<?= base_url('uploads/news/' . $gi->filename) ?>"
                     alt="" class="news-gallery-thumb">
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Carte galerie associée -->
        <?php if (!empty($linkedGallery)): ?>
        <div class="separator text-center my-4">
          <img src="https://rbcd.be/assets/images/billiard-chalk.png"
               alt="Séparateur" style="width:20px;opacity:0.7;margin: 0 10px;">
        </div>
        <div class="row justify-content-center mb-20">
          <div class="col-sm-8 col-md-6 col-lg-5">
            <a href="<?= base_url('galeries/' . $linkedGallery->slug) ?>" class="gal-card">
              <div class="gal-card-cover">
                <?php if ($linkedGallery->cover_filename): ?>
                  <img src="<?= base_url('uploads/galleries/' . $linkedGallery->id . '/' . $linkedGallery->cover_filename) ?>"
                       alt="<?= esc($linkedGallery->title) ?>">
                <?php else: ?>
                  <div class="gal-card-nocover"><i class="fas fa-images"></i></div>
                <?php endif; ?>
                <span class="gal-card-count">
                  <i class="fas fa-camera me-1"></i><?= $linkedGallery->photo_count ?>
                </span>
              </div>
              <div class="gal-card-body">
                <div class="gal-card-title"><?= esc($linkedGallery->title) ?></div>
                <?php if ($linkedGallery->event_date): ?>
                <div class="gal-card-date">
                  <i class="far fa-calendar-alt me-1"></i>
                  <?= date('d/m/Y', strtotime($linkedGallery->event_date)) ?>
                </div>
                <?php endif; ?>
              </div>
            </a>
          </div>
        </div>
        <?php endif; ?>

        <!-- Retour -->
        <div class="mt-40 pt-20" style="border-top:1px solid #eee">
          <a href="<?= base_url() ?>#actualites" class="btn btn-theme-colored1 btn-sm btn-round">
            <i class="fas fa-arrow-left me-1"></i> Retour aux actualités
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<style>
.news-detail-date {
    font-size: .8rem;
    color: #84252B;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0 0 10px;
}
.news-detail-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #222;
    line-height: 1.3;
    margin: 0 0 14px;
}
.news-detail-excerpt {
    font-size: 1.05rem;
    color: #555;
    font-style: italic;
    border-left: 3px solid #84252B;
    padding-left: 14px;
    margin: 0 0 20px;
}
.news-detail-img-wrap {
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0,0,0,.10);
}
.news-detail-img {
    width: 100%;
    display: block;
    border-radius: 6px;
}
.news-detail-content {
    font-size: 1rem;
    line-height: 1.8;
    color: #333;
}
.news-detail-content img { max-width: 100%; height: auto; border-radius: 4px; }
.news-detail-content h2, .news-detail-content h3 { color: #222; margin-top: 1.5rem; }
.news-detail-content a { color: #84252B; }
.news-detail-content a:hover { color: #6a1d22; }
.btn-news-back {
    font-size: .9rem;
    font-weight: 600;
    color: #84252B;
    text-decoration: none;
}
.btn-news-back:hover { color: #6a1d22; }
.news-gallery-title {
    font-weight: 700;
    color: #444;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 12px;
}
.news-gallery-item {
    display: block;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    transition: transform .2s, box-shadow .2s;
}
.news-gallery-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,.18);
}
.news-gallery-thumb {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
    display: block;
}
.gal-card {
    display: block;
    text-decoration: none;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    transition: transform .2s, box-shadow .2s;
    color: inherit;
}
.gal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0,0,0,.15);
    color: inherit;
    text-decoration: none;
}
.gal-card-cover {
    position: relative;
    aspect-ratio: 4/3;
    background: #f0f0f0;
    overflow: hidden;
}
.gal-card-cover img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform .3s;
}
.gal-card:hover .gal-card-cover img { transform: scale(1.05); }
.gal-card-nocover {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: #ccc; font-size: 3rem;
}
.gal-card-count {
    position: absolute; bottom: 8px; right: 8px;
    background: rgba(0,0,0,.55); color: #fff;
    font-size: .72rem; font-weight: 600;
    padding: 3px 8px; border-radius: 20px;
}
.gal-card-body {
    padding: 10px 14px 12px;
    background: #fff;
    border-top: 3px solid #84252B;
}
.gal-card-title {
    font-weight: 700; font-size: .9rem; color: #333;
    margin-bottom: 4px;
}
.gal-card-date { font-size: .78rem; color: #999; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
</script>
<?= $this->endSection() ?>
