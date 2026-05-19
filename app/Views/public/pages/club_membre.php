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
/* ── Palmarès ── */
.sr-list { list-style: none; margin: 0; padding: 0; }
.sr-item {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}
.sr-item:last-child { border-bottom: none; }
.sr-item-group-end:not(:last-child) { border-bottom: 2px solid #adb5bd; }
.sr-type-col { flex-shrink: 0; width: 28px; text-align: center; }
.sr-icon-coupe       { font-size: 1.4rem; color: #e6a800; }
.sr-icon-championnat { font-size: 1.4rem; color: #84252B; }
.sr-place-num { display: inline-block; font-size: .8rem; font-weight: 700; color: #888; }
.sr-info { flex: 1; min-width: 0; }
.sr-title { font-weight: 700; color: #333; font-size: .92rem; text-decoration: underline; margin-bottom: 2px; }
.sr-winner { font-size: .82rem; color: #555; }
.sr-date-col { flex-shrink: 0; font-size: .8rem; color: #777; min-width: 120px; }
.sr-pdf { flex-shrink: 0; }
.sr-pdf-link { color: #c0392b; font-size: 1.5rem; }
.sr-pdf-link:hover { color: #84252B; }
.sr-pdf-none { color: #ddd; font-size: 1.5rem; }

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

  <!-- Catégories : encart d'attente (fédérés uniquement) -->
  <?php if ($m->is_federated): ?>
  <div class="d-flex align-items-start gap-3 p-3 mb-4 rounded"
       style="background:#f8f9fa;border:1px solid #dee2e6;font-size:.9rem;color:#555">
    <i class="fas fa-info-circle mt-1" style="color:#84252B;flex-shrink:0"></i>
    <div>
      <p class="mb-2">
        <strong style="color:#333">Catégories du joueur</strong><br>
        La FRBB est actuellement en train de revoir le système de classification et de catégories des joueurs.
        Ce projet, dont la mise en œuvre s'avère complexe à l'échelle des différentes régions, n'est pas encore finalisé.
        Dès que les nouvelles catégories seront arrêtées, elles apparaîtront sur chaque fiche joueur.
      </p>
      <p class="mb-0">Merci de votre compréhension.</p>
    </div>
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

  <!-- Palmarès du membre -->
  <?php if (!empty($sportResults)): ?>
  <div class="section-card mb-4">
    <div class="section-title"><i class="fas fa-trophy me-2"></i>Palmarès</div>
    <ul class="sr-list">
      <?php $resArr = array_values($sportResults); $resCount = count($resArr); ?>
      <?php foreach ($resArr as $i => $r): ?>
      <?php
        $place      = (int) ($r->place ?? 1);
        $isGroupEnd = ($i === $resCount - 1) || ($resArr[$i + 1]->title !== $r->title);
        $dateStr    = null;
        if ($r->final_date) {
            $dt     = new DateTime($r->final_date);
            $months = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
            $dateStr = $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
        }
        $pdfPath = $r->pdf_file ? FCPATH . 'uploads/PDF/SportResults/' . $r->pdf_file : null;
        $hasPdf  = $pdfPath && file_exists($pdfPath);
        $placeLabel = match(true) {
            $place === 1 && $r->type === 'coupe'       => 'Vainqueur',
            $place === 1 && $r->type === 'championnat' => 'Champion',
            $place === 1                               => '1<sup>er</sup>',
            default                                    => $place . '<sup>ème</sup> place',
        };
      ?>
      <li class="sr-item<?= $isGroupEnd ? ' sr-item-group-end' : '' ?>">

        <div class="sr-type-col">
          <?php if ($place === 1): ?>
            <?php if ($r->type === 'coupe'): ?>
              <i class="fas fa-trophy sr-icon-coupe"></i>
            <?php elseif ($r->type === 'championnat'): ?>
              <i class="fas fa-medal sr-icon-championnat"></i>
            <?php endif; ?>
          <?php else: ?>
            <span class="sr-place-num">&nbsp;</span>
          <?php endif; ?>
        </div>

        <div class="sr-info">
          <div class="sr-title"><?= esc($r->title) ?></div>
          <div class="sr-winner"><?= $placeLabel ?> · Saison <?= esc($r->season) ?></div>
        </div>

        <div class="sr-date-col">
          <?php if ($dateStr): ?>
            <span class="sr-date-value"><?= $dateStr ?></span>
          <?php endif; ?>
        </div>

        <div class="sr-pdf">
          <?php if ($hasPdf): ?>
            <a href="<?= base_url('uploads/PDF/SportResults/' . $r->pdf_file) ?>"
               target="_blank" rel="noopener" class="sr-pdf-link" title="Télécharger le PDF">
              <i class="fas fa-file-pdf"></i>
            </a>
          <?php else: ?>
            <span class="sr-pdf-none"><i class="fas fa-file-pdf"></i></span>
          <?php endif; ?>
        </div>

      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Retour -->
  <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i><?= esc($backLabel) ?>
  </a>

</div>

<?= $this->endSection() ?>
