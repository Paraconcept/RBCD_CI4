<?= $this->extend('public/layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.membre-profile-photo {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 4px solid #dee2e6;
    object-fit: cover;
    display: block;
}
.membre-no-photo-circle {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 4px solid #dee2e6;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #bbb;
}
.membre-name {
    font-size: 1.8rem;
    font-weight: 800;
    color: #222;
    line-height: 1.2;
}
.membre-firstname {
    font-size: 1.4rem;
    font-weight: 400;
    color: #555;
}
.membre-badges { gap: 6px; }
.badge-frbb     { background: #003082; color: #fff; font-size: .75rem; font-weight: 700; border-radius: 4px; padding: 3px 10px; }
.badge-junior   { background: #FFD43C; color: #000; font-size: .75rem; font-weight: 600; border-radius: 4px; padding: 3px 10px; }
.badge-supporter{ background: #6c757d; color: #fff; font-size: .75rem; font-weight: 600; border-radius: 4px; padding: 3px 10px; }
.badge-school   { background: #fd7e14; color: #fff; font-size: .75rem; font-weight: 600; border-radius: 4px; padding: 3px 10px; }
.info-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: .95rem;
}
.info-row:last-child { border-bottom: none; }
.info-row .info-icon {
    width: 22px;
    text-align: center;
    color: #84252B;
    flex-shrink: 0;
    margin-top: 2px;
}
.info-row .info-label {
    font-weight: 600;
    color: #555;
    min-width: 100px;
    flex-shrink: 0;
}
.info-row .info-value { color: #222; }
.section-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e8e8e8;
    padding: 24px 28px;
    margin-bottom: 24px;
}
.section-title {
    font-size: .9rem;
    font-weight: 700;
    color: #84252B;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #84252B;
}
/* Colonne coordonnées : séparation verticale sur desktop, horizontale sur mobile */
@media (min-width: 768px) {
    .membre-coords-col {
        border-left: 1px solid #e8e8e8;
        padding-left: 28px;
    }
}
@media (max-width: 767px) {
    .membre-coords-col {
        border-top: 1px solid #e8e8e8;
        padding-top: 20px;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$m          = $member;
$isLoggedIn = (bool) session()->get('member_logged_in');

$canSee = function(string $field, $value) use ($m, $isLoggedIn): bool {
    if (!$value) return false;
    if ($m->{"show_{$field}"})                                      return true;
    if ($isLoggedIn && ($m->{"show_{$field}_members"} ?? 0))        return true;
    return false;
};

$showPhoto = $m->photo && ($m->show_photo || ($isLoggedIn && ($m->show_photo_members ?? 0)));
$photo     = $showPhoto ? base_url('uploads/members/' . $m->photo) : null;

$hasCoords = $canSee('phone',      $m->phone)
           || $canSee('mobile',     $m->mobile)
           || $canSee('email',      $m->email)
           || $canSee('address',    $m->address || $m->city)
           || $canSee('birth_date', $m->birth_date);
?>

<div class="container pt-40 pb-60">

  <div class="section-card">
    <div class="row align-items-start g-4">

      <!-- Photo -->
      <div class="col-auto text-center">
        <?php if ($photo): ?>
          <img src="<?= esc($photo) ?>" alt="<?= esc($m->last_name . ' ' . $m->first_name) ?>"
               class="membre-profile-photo">
        <?php else: ?>
          <div class="membre-no-photo-circle mx-auto">
            <i class="fas fa-user"></i>
          </div>
        <?php endif; ?>
      </div>

      <!-- Identité -->
      <div class="col">
        <div class="membre-name"><?= esc($m->last_name) ?></div>
        <div class="membre-firstname mb-3"><?= esc($m->first_name) ?></div>

        <div class="d-flex flex-wrap membre-badges mb-3">
          <?php if ($m->is_federated): ?>
            <span class="badge-frbb">
              <img src="<?= base_url('assets/images/frbb_kbbb_logo_100.png') ?>" alt="FRBB"
                   style="width:16px;height:22px;vertical-align:middle;margin-right:4px">FRBB
            </span>
          <?php endif; ?>
          <?php if ($m->is_junior): ?><span class="badge-junior"><i class="fas fa-smile fa-lg me-1"></i> Junior</span><?php endif; ?>
        </div>

        <?php if ($m->is_federated && $m->frbb_license): ?>
        <div class="info-row" style="border:none;padding:0">
          <span class="info-icon"><i class="fas fa-id-card"></i></span>
          <span class="info-label">Licence FRBB</span>
          <span class="info-value">: <?= esc($m->frbb_license) ?></span>
        </div>
        <?php endif; ?>

      </div>

      <!-- Coordonnées : col-12 sur mobile (→ ligne propre), col-md-5 sur desktop (→ à droite) -->
      <?php if ($hasCoords): ?>
      <div class="col-12 col-md-5 membre-coords-col">
        <div class="section-title"><i class="fas fa-address-book me-2"></i>Coordonnées</div>

        <?php if ($canSee('phone', $m->phone)): ?>
        <div class="info-row">
          <span class="info-icon"><i class="fas fa-phone"></i></span>
          <span class="info-label">Téléphone</span>
          <span class="info-value">
            <a href="tel:<?= esc(preg_replace('/\s+/', '', $m->phone)) ?>">: <?= esc($m->phone) ?></a>
          </span>
        </div>
        <?php endif; ?>

        <?php if ($canSee('mobile', $m->mobile)): ?>
        <div class="info-row">
          <span class="info-icon"><i class="fas fa-mobile-alt"></i></span>
          <span class="info-label">GSM</span>
          <span class="info-value">
            <a href="tel:<?= esc(preg_replace('/\s+/', '', $m->mobile)) ?>">: <?= esc($m->mobile) ?></a>
          </span>
        </div>
        <?php endif; ?>

        <?php if ($canSee('email', $m->email)): ?>
        <div class="info-row">
          <span class="info-icon"><i class="fas fa-envelope"></i></span>
          <span class="info-label">E-mail</span>
          <span class="info-value">
            <a href="mailto:<?= esc($m->email) ?>">: <?= esc($m->email) ?></a>
          </span>
        </div>
        <?php endif; ?>

        <?php if ($canSee('address', $m->address || $m->city)): ?>
        <div class="info-row">
          <span class="info-icon"><i class="fas fa-map-marker-alt"></i></span>
          <span class="info-label">Adresse</span>
          <span class="info-value">
            : <?= esc($m->address) ?>
            <?php if ($m->address && ($m->postal_code || $m->city)): ?><br>&nbsp;<?php endif; ?>
            <?= esc(trim($m->postal_code . ' ' . $m->city)) ?>
          </span>
        </div>
        <?php endif; ?>

        <?php if ($canSee('birth_date', $m->birth_date)): ?>
        <div class="info-row">
          <span class="info-icon"><i class="fas fa-birthday-cake"></i></span>
          <span class="info-label">Né<?= $m->gender === 'F' ? 'e' : '' ?> le</span>
          <span class="info-value">: <?= date('d / m / Y', strtotime($m->birth_date)) ?></span>
        </div>
        <?php endif; ?>

      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Retour -->
  <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i><?= esc($backLabel) ?>
  </a>

</div>

<?= $this->endSection() ?>
