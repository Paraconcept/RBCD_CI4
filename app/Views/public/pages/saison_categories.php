<?php
$avg = static fn (?int $v): string => $v === null ? '<span class="text-muted">—</span>' : number_format($v / 1000, 3, ',', '');
?>
<?= $this->extend('public/layouts/main') ?>

<?= $this->section('extra_css') ?>
<style>
.cat-switch .btn { border: 2px solid #84252B; color: #84252B; background: #fff; font-weight: 600; padding: 10px 22px; transition: background .18s, color .18s; }
.cat-switch .btn:hover, .cat-switch .btn.active { background: #84252B; color: #fff; }
.cat-switch .btn i { margin-right: 8px; }
@media (max-width: 575px) { .cat-switch .btn { padding: 8px 14px; font-size: .88rem; } .cat-dim { display: block; font-weight: 400; font-size: .8rem; } }
.cat-card { border: 1px solid #e0e0e0; border-top: 4px solid #84252B; border-radius: 6px; overflow: hidden; margin-bottom: 30px; background: #fff; }
.cat-card-title { padding: 12px 18px; margin: 0; font-size: 1.05rem; font-weight: 700; color: #202C45; border-bottom: 1px solid #e0e0e0; }
.cat-card-title i { color: #84252B; margin-right: 8px; font-size: .9em; }
.cat-table { width: 100%; margin: 0; font-size: .9rem; }
.cat-table th, .cat-table td { padding: 6px 10px; text-align: center; vertical-align: middle; }
.cat-table thead th { background: #202C45; color: #fff; font-weight: 600; border: 0; }
.cat-table thead tr.sub th { background: #2c3a5a; color: #dde3ef; font-weight: 400; font-size: .82rem; }
.cat-table thead .sep, .cat-table td.sep { border-left: 2px solid rgba(132, 37, 43, .35); }
.cat-table thead th.sep { border-left-color: rgba(255, 255, 255, .3); }
.cat-table th.cat, .cat-table td.cat { text-align: left; padding-left: 18px; }
.cat-table tbody tr { border-top: 1px solid #eee; }
.cat-table tbody tr:hover { background: #f6eced; }
.cat-table tbody tr.top { background: #f8f9fa; font-weight: 600; }
.cat-table tbody tr.top td.cat { color: #84252B; }
/* Le thème Studypress impose aux tableaux un padding large, des bordures et un texte gris clair */
.cat-card .cat-table td { padding: 5px 10px !important; color: #333; border-width: 0 !important; line-height: 1.4; }
.cat-card .cat-table td.sep { border-left: 2px solid rgba(132, 37, 43, .35) !important; }
.cat-card .cat-table th { padding: 7px 10px !important; }
.cat-card .table-responsive, .cat-card .cat-table { margin-bottom: 0 !important; }
.cat-note { padding: 8px 18px; font-size: .82rem; color: #666; background: #fafafa; border-top: 1px solid #eee; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center">Championnats individuels — <?= SAISON_EN_COURS ?></h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center text-dark mt-20">
            Catégories, points à jouer et moyennes minimum et maximum de chaque catégorie, selon le barème de la FRBB.
          </p>
        </div>
      </div>
    </div>

    <div class="text-center mb-40">
      <div class="btn-group cat-switch" role="group">
        <button type="button" class="btn active" data-target="PF"><i class="fas fa-table"></i>Petit Billard <span class="cat-dim">(2m30 / 2m10)</span></button>
        <button type="button" class="btn" data-target="GF"><i class="fas fa-table"></i>Grand Billard <span class="cat-dim">(2m84)</span></button>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-10 mx-auto">
        <?php foreach ($tables as $format => $modes): ?>
        <div class="cat-panel" id="cat-<?= $format ?>" <?= $format === 'PF' ? '' : 'style="display:none"' ?>>
          <?php foreach ($modes as $t): ?>
          <div class="cat-card">
            <h4 class="cat-card-title"><i class="far fa-dot-circle"></i><?= esc($t['title']) ?></h4>
            <div class="table-responsive">
              <table class="cat-table">
                <thead>
                  <?php if ($format === 'PF'): ?>
                  <tr>
                    <th rowspan="2" class="cat" style="width:22%">Catégorie</th>
                    <th rowspan="2" style="width:10%">Pts</th>
                    <th colspan="2">Moyenne 2m30</th>
                    <th colspan="2" class="sep">Moyenne 2m10</th>
                  </tr>
                  <tr class="sub"><th>Min</th><th>Max</th><th class="sep">Min</th><th>Max</th></tr>
                  <?php else: ?>
                  <tr>
                    <th rowspan="2" class="cat" style="width:22%">Catégorie</th>
                    <th rowspan="2" style="width:10%">Pts</th>
                    <th colspan="2">Moyenne 2m84</th>
                  </tr>
                  <tr class="sub"><th>Min</th><th>Max</th></tr>
                  <?php endif; ?>
                </thead>
                <tbody>
                  <?php foreach ($t['rows'] as $r): ?>
                  <tr class="<?= $r['highlight'] ? 'top' : '' ?>">
                    <td class="cat"><?= esc($r['label']) ?></td>
                    <td><?= $r['points'] ?></td>
                    <td><?= $avg($r['min']) ?></td>
                    <td><?= $avg($r['max']) ?></td>
                    <?php if ($format === 'PF'): ?>
                    <td class="sep"><?= $avg($r['min_210']) ?></td>
                    <td><?= $avg($r['max_210']) ?></td>
                    <?php endif; ?>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php if ($t['note']): ?>
            <div class="cat-note"><i class="fas fa-info-circle me-1"></i><?= esc($t['note']) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  var buttons = document.querySelectorAll('.cat-switch .btn');
  function show(format) {
    buttons.forEach(function (b) { b.classList.toggle('active', b.dataset.target === format); });
    document.querySelectorAll('.cat-panel').forEach(function (p) { p.style.display = p.id === 'cat-' + format ? '' : 'none'; });
  }
  buttons.forEach(function (b) {
    b.addEventListener('click', function () {
      show(b.dataset.target);
      history.replaceState(null, '', b.dataset.target === 'GF' ? '#grand-billard' : location.pathname + location.search);
    });
  });
  if (location.hash === '#grand-billard') show('GF');
})();
</script>
<?= $this->endSection() ?>
