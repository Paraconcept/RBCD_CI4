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
    text-align: center;
    margin-bottom: 28px;
}
.member-photo-wrap {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    transition: border-color .25s;
    margin: 0 auto 12px;
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
    font-size: 3rem;
    color: #bbb;
    line-height: 1;
}
.member-card .member-name {
    font-weight: 700;
    font-size: .95rem;
    color: #333;
    line-height: 1.3;
}
.member-card .member-badges {
    margin-top: 5px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px;
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
  <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <span class="badge bg-secondary fs-6 fw-normal">
      <i class="fas fa-users me-1"></i><?= count($members) ?> membres actifs
    </span>
    <span class="badge fs-6 fw-normal" style="background:#003082">
      <i class="fas fa-id-card me-1"></i><?= $totalFederated ?> fédérés FRBB
    </span>
    <small class="text-muted ms-auto fst-italic">Cliquez sur un nom pour la fiche détaillée</small>
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
      <div class="member-name">
        <?= esc($m->last_name) ?><br>
        <span style="font-weight:400"><?= esc($m->first_name) ?></span>
      </div>
      <?php if ($m->ranking): ?>
        <div class="member-ranking">Cl. <?= (int) $m->ranking ?></div>
      <?php endif; ?>
      <div class="member-badges">
        <?php if ($m->is_federated): ?><span class="badge-frbb">FRBB</span><?php endif; ?>
        <?php if ($m->is_junior): ?><span class="badge-junior">Junior</span><?php endif; ?>
        <?php if ($m->is_school): ?><span class="badge-school">École</span><?php endif; ?>
        <?php if ($m->is_supporter): ?><span class="badge-supporter">Supporter</span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?= $this->endSection() ?>
