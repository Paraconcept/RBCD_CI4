<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center">Galeries photos</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center mt-20">
            Les moments forts du RBC Disonais en images.
          </p>
        </div>
      </div>
    </div>

    <?php if (empty($galleries)): ?>
    <div class="row">
      <div class="col-md-8 offset-md-2 text-center text-muted py-40">
        <i class="fas fa-images fa-3x mb-20" style="color:#ccc"></i>
        <p>Aucune galerie disponible pour le moment.</p>
      </div>
    </div>

    <?php else: ?>
    <div class="row">
      <?php foreach ($galleries as $g): ?>
      <div class="col-sm-6 col-md-4 col-lg-3 mb-30">
        <a href="<?= base_url('galeries/' . $g->slug) ?>" class="gal-card">
          <div class="gal-card-cover">
            <?php if ($g->cover_filename): ?>
              <img src="<?= base_url('uploads/galleries/' . $g->id . '/' . $g->cover_filename) ?>"
                   alt="<?= esc($g->title) ?>">
            <?php else: ?>
              <div class="gal-card-nocover"><i class="fas fa-images"></i></div>
            <?php endif; ?>
            <span class="gal-card-count">
              <i class="fas fa-camera me-1"></i><?= $g->photo_count ?>
            </span>
          </div>
          <div class="gal-card-body">
            <div class="gal-card-title"><?= esc($g->title) ?></div>
            <?php if ($g->event_date): ?>
            <div class="gal-card-date">
              <i class="far fa-calendar-alt me-1"></i>
              <?= date('d/m/Y', strtotime($g->event_date)) ?>
            </div>
            <?php endif; ?>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
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
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.gal-card-date { font-size: .78rem; color: #999; }
</style>
<?= $this->endSection() ?>
