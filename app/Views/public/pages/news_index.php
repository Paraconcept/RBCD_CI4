<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-50">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center">Actualités</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center mt-10">Les dernières nouvelles du RBC Disonais</p>
        </div>
      </div>
    </div>

    <?php if (empty($news)): ?>
    <div class="row">
      <div class="col-12 text-center text-muted py-40">
        <i class="fas fa-newspaper fa-3x mb-20" style="opacity:.3"></i>
        <p>Aucune actualité pour le moment.</p>
      </div>
    </div>
    <?php else: ?>
    <div class="row">
      <?php foreach ($news as $n): ?>
      <div class="col-sm-6 col-lg-4 mb-40">
        <div class="news-card">
          <a href="<?= base_url('actualites/' . $n->slug) ?>" class="news-card-img-link">
            <?php if ($n->image): ?>
              <img src="<?= base_url('uploads/news/' . $n->image) ?>"
                   alt="<?= esc($n->title) ?>" class="news-card-img">
            <?php else: ?>
              <div class="news-card-img-placeholder">
                <i class="fas fa-newspaper"></i>
              </div>
            <?php endif; ?>
          </a>
          <div class="news-card-body">
            <?php if ($n->published_at): ?>
            <p class="news-card-date">
              <i class="far fa-calendar-alt me-1"></i>
              <?= date('d/m/Y', strtotime($n->published_at)) ?>
            </p>
            <?php endif; ?>
            <h4 class="news-card-title">
              <a href="<?= base_url('actualites/' . $n->slug) ?>"><?= esc($n->title) ?></a>
            </h4>
            <?php if ($n->excerpt): ?>
            <p class="news-card-excerpt"><?= esc($n->excerpt) ?></p>
            <?php endif; ?>
            <a href="<?= base_url('actualites/' . $n->slug) ?>" class="btn-news-more">
              Lire la suite <i class="fas fa-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
    <div class="row mt-20">
      <div class="col-12 d-flex justify-content-center">
        <?= $pager->links('default', 'news_pager') ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.news-card {
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: box-shadow .25s;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.news-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.news-card-img-link { display: block; flex-shrink: 0; }
.news-card-img {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    display: block;
    transition: transform .35s;
}
.news-card:hover .news-card-img { transform: scale(1.03); }
.news-card-img-placeholder {
    width: 100%;
    aspect-ratio: 16/9;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #ccc;
}
.news-card-body {
    padding: 20px 18px 22px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.news-card-date {
    font-size: .8rem;
    color: #84252B;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0 0 8px;
}
.news-card-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 10px;
    line-height: 1.4;
}
.news-card-title a { color: #222; text-decoration: none; }
.news-card-title a:hover { color: #84252B; }
.news-card-excerpt {
    font-size: .9rem;
    color: #666;
    flex: 1;
    margin: 0 0 14px;
}
.btn-news-more {
    font-size: .85rem;
    font-weight: 600;
    color: #84252B;
    text-decoration: none;
    border-top: 1px solid #eee;
    padding-top: 12px;
    margin-top: auto;
    display: inline-block;
}
.btn-news-more:hover { color: #6a1d22; }
</style>
<?= $this->endSection() ?>
