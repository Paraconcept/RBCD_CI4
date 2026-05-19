<?= $this->extend('public/layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.members-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 300px));
    gap: 8px 24px;
    justify-content: center;
}
@media (max-width: 575px) {
    .members-grid { grid-template-columns: repeat(2, minmax(0, 220px)); }
}
.member-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 8px;
    border-radius: 8px;
    transition: background .2s;
}
a.member-card { text-decoration: none; color: inherit; }
.member-card:hover { background: #f8f8f8; }
.member-photo-wrap {
    flex-shrink: 0;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    transition: border-color .25s;
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.member-card:hover .member-photo-wrap { border-color: #84252B; }
.member-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.member-photo-wrap .member-no-photo {
    font-size: 1.8rem;
    color: #bbb;
    line-height: 1;
}
.member-info { min-width: 0; }
.member-card .member-name {
    font-weight: 700;
    font-size: .9rem;
    color: #333;
    line-height: 1.3;
}
.member-card .member-badges {
    margin-top: 4px;
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
}
.badge-frbb {
    background: #003082;
    color: #fff;
    font-size: .68rem;
    font-weight: 700;
    border-radius: 4px;
    padding: 1px 6px;
    letter-spacing: .3px;
}
.badge-junior   { background: #FFD43C; color: #000; font-size: .68rem; font-weight: 600; border-radius: 4px; padding: 1px 6px; }
.badge-supporter{ background: #6c757d; color: #fff; font-size: .68rem; font-weight: 600; border-radius: 4px; padding: 1px 6px; }
.badge-school   { background: #fd7e14; color: #fff; font-size: .68rem; font-weight: 600; border-radius: 4px; padding: 1px 6px; }
.member-ranking { font-size: .8rem; color: #84252B; font-weight: 600; }
.filter-btn {
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color .2s, opacity .2s;
    opacity: .72;
}
.filter-btn:hover { opacity: 1; }
.filter-btn.active { opacity: 1; border-color: rgba(255,255,255,.7); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container pt-40 pb-60">

  <!-- Compteurs -->
  <div class="text-center mb-4">
    <div class="d-flex justify-content-center gap-3 flex-wrap mb-2">
      <button class="badge fs-6 fw-normal filter-btn active" data-filter="all" style="background:#666666">
        <i class="fas fa-users me-2"></i><?= count($members) ?> membres actifs
      </button>
      <button class="badge fs-6 fw-normal filter-btn" data-filter="federated" style="background:#DA8508">
        <i class="fas fa-id-card me-2"></i><?= $totalFederated ?> fédérés FRBB
      </button>
    </div>
    <small class="text-muted fst-italic">Cliquez sur un nom pour voir sa fiche détaillée</small>
  </div>

  <?php if (empty($members)): ?>
    <div class="alert alert-info">Aucun membre actif pour le moment.</div>
  <?php else: ?>
  <?php
    // Construit un tableau ordonné pour affichage colonne par colonne dans la CSS Grid.
    // Distribue les items équitablement : les premières colonnes reçoivent un item de plus
    // si N n'est pas divisible par $cols (ex. 46 → col1=16, col2=15, col3=15 et non 16/16/14).
    $buildGrid = function(array $items) use (&$cols): array {
        $n      = count($items);
        $extra  = $n % $cols;
        $floor  = (int) floor($n / $cols);
        $starts = [];
        $sizes  = [];
        $off    = 0;
        for ($c = 0; $c < $cols; $c++) {
            $starts[$c] = $off;
            $sizes[$c]  = $floor + ($c < $extra ? 1 : 0);
            $off       += $sizes[$c];
        }
        $result  = [];
        $maxRows = (int) ceil($n / $cols);
        for ($r = 0; $r < $maxRows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if ($r < $sizes[$c]) {
                    $result[] = $items[$starts[$c] + $r];
                }
            }
        }
        return $result;
    };

    $cols    = 3;
    $grid    = $buildGrid($members);
    $fedOnly = array_values(array_filter($members, fn($m) => $m->is_federated));
    $gridFed = $buildGrid($fedOnly);
    $fedPos  = [];
    foreach ($gridFed as $i => $fm) { $fedPos[$fm->id] = $i; }
  ?>
  <div class="members-grid">
    <?php foreach ($grid as $allPos => $m): ?>
    <?php $photo = $m->photo ? base_url('uploads/members/' . $m->photo) : null; ?>
    <a href="<?= base_url('club/membres/' . $m->id) ?>" class="member-card"
       data-federated="<?= $m->is_federated ? '1' : '0' ?>"
       data-all-pos="<?= $allPos ?>"
       data-fed-pos="<?= $fedPos[$m->id] ?? -1 ?>"
       style="order:<?= $allPos ?>">
      <div class="member-photo-wrap">
        <?php if ($m->photo): ?>
          <img src="<?= esc($photo) ?>" alt="<?= esc($m->last_name . ' ' . $m->first_name) ?>">
        <?php else: ?>
          <i class="fas fa-user member-no-photo"></i>
        <?php endif; ?>
      </div>
      <div class="member-info">
        <div class="member-name">
          <?= esc($m->last_name) ?><br>
          <span style="font-weight:400"><?= esc($m->first_name) ?></span>
        </div>
        <div class="member-badges">
          <?php if ($m->is_federated): ?><span class="badge-frbb"><img src="<?= base_url('assets/images/frbb_kbbb_logo_100.png') ?>" alt="FRBB" style="width: 18px; height: 25px;"></span><?php endif; ?>
          <?php if ($m->is_junior): ?><span class="badge-junior"><i class="fas fa-smile fa-lg me-1"></i> Junior</span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

    <!-- Séparateur -->
    <div class="row mt-20 mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.dataset.filter;

        const cards = document.querySelectorAll('.member-card');

        if (filter === 'all') {
            cards.forEach(card => {
                card.style.display = '';
                card.style.order   = card.dataset.allPos;
            });
        } else {
            cards.forEach(card => {
                if (card.dataset.federated === '1') {
                    card.style.display = '';
                    card.style.order   = card.dataset.fedPos;
                } else {
                    card.style.display = 'none';
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
