<?php use App\Models\NewsModel; ?>
<?= $this->extend('public/layouts/main') ?>

<?= $this->section('extra_css') ?>
<?= view('public/partials/news_card_css') ?>
<style>
.news-season { font-size: 1.05rem; font-weight: 700; color: #202C45; border-bottom: 2px solid #84252B; padding-bottom: 6px; margin: 10px 0 25px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<section id="actualites">
  <div class="container mt-30 mb-30 pt-30 pb-30">

    <div class="mb-40">
      <div class="tm-sc tm-sc-section-title section-title">
        <div class="title-wrapper">
          <h2 class="text-uppercase line-bottom line-bottom-theme-colored1">
            <i class="fas fa-archive me-3 text-theme-colored1"></i>
            Anciennes <span class="text-theme-colored1">News</span>
          </h2>
        </div>
      </div>
      <p class="text-muted mb-0">Les actualités des saisons précédentes. Celles de la saison <?= SAISON_EN_COURS ?> sont sur la <a href="<?= base_url('/') ?>">page d'accueil</a>.</p>
    </div>

    <div class="blog-posts">
      <?php if (empty($news)): ?>
      <div class="alert alert-info">Aucune ancienne actualité.</div>
      <?php else: ?>

      <?php $season = null; ?>
      <?php foreach ($news as $n): ?>
        <?php $s = NewsModel::seasonOf($n->published_at); ?>
        <?php if ($s !== $season): ?>
          <?php if ($season !== null): ?></div><?php endif; ?>
          <h3 class="news-season"><i class="fas fa-calendar-alt me-2 text-theme-colored1"></i>Saison <?= $s ?></h3>
          <div class="row">
          <?php $season = $s; ?>
        <?php endif; ?>
        <div class="col-sm-6 col-lg-4 mb-30">
          <?= view('public/partials/news_card', ['n' => $n]) ?>
        </div>
      <?php endforeach; ?>
      </div>

      <?= !empty($news) ? $pager->links('default', 'home_pager') : '' ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<?= $this->endSection() ?>
