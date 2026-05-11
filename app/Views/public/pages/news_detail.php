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

        <!-- Retour -->
        <div class="mt-40 pt-20" style="border-top:1px solid #eee">
          <a href="<?= base_url('/') ?>" class="btn-news-back">
            <i class="fas fa-arrow-left me-1"></i> Retour à l'accueil
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
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
</style>
<?= $this->endSection() ?>
