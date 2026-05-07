<?= $this->extend('public/layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.members-grid {
    column-count: 3;
    column-gap: 24px;
}
@media (max-width: 575px) {
    .members-grid { column-count: 2; }
}
.member-card {
    break-inside: avoid;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
    padding: 8px;
    border-radius: 8px;
    transition: background .2s;
}
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
.badge-junior   { background: #198754; color: #fff; font-size: .68rem; font-weight: 600; border-radius: 4px; padding: 1px 6px; }
.badge-supporter{ background: #6c757d; color: #fff; font-size: .68rem; font-weight: 600; border-radius: 4px; padding: 1px 6px; }
.badge-school   { background: #fd7e14; color: #fff; font-size: .68rem; font-weight: 600; border-radius: 4px; padding: 1px 6px; }
.member-ranking { font-size: .8rem; color: #84252B; font-weight: 600; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container pt-40 pb-60">

  <!-- Compteurs -->
  <div class="text-center mb-4">
    <div class="d-flex justify-content-center gap-3 flex-wrap mb-2">
      <span class="badge fs-6 fw-normal" style="background:#666666">
        <i class="fas fa-users me-2"></i><?= count($members) ?> membres actifs
      </span>
      <span class="badge fs-6 fw-normal" style="background:#DA8508">
        <i class="fas fa-id-card me-2"></i><?= $totalFederated ?> fédérés FRBB
      </span>
    </div>
    <small class="text-muted fst-italic">Cliquez sur un nom pour voir sa fiche détaillée</small>
  </div>

  <?php if (empty($members)): ?>
    <div class="alert alert-info">Aucun membre actif pour le moment.</div>
  <?php else: ?>
  <div class="members-grid">
    <?php foreach ($members as $m): ?>
    <?php $photo = $m->photo ? base_url('uploads/members/' . $m->photo) : null; ?>
    <div class="member-card">
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
          <?php if ($m->is_junior): ?><span class="badge-junior">Junior</span><?php endif; ?>
          <?php if ($m->is_school): ?><span class="badge-school">École</span><?php endif; ?>
          <?php if ($m->is_supporter): ?><span class="badge-supporter">Supporter</span><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?= $this->endSection() ?>
