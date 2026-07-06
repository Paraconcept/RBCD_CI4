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
/* ── Accordéon palmarès ── */
.accordion-theme-colored1 .accordion-button:not(.collapsed) {
    background-color: #84252B; color: #fff;
}
.accordion-theme-colored1 .accordion-button:not(.collapsed)::after { filter: brightness(10); }
.accordion-theme-colored1 .accordion-button:focus { box-shadow: 0 0 0 .2rem rgba(132,37,43,.25); }
.accordion-item { border: 1px solid rgba(0,0,0,.1); margin-bottom: 6px; border-radius: 4px !important; overflow: hidden; }

/* ── Liste des résultats ── */
.sr-list { list-style: none; margin: 0; padding: 0; }
.sr-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background .15s;
}
.sr-item:last-child { border-bottom: none; }
.sr-item-group-end:not(:last-child) { border-bottom: 2px solid #adb5bd; }
.sr-item:hover { background: #fafafa; }
/* Photo */
.sr-photo {
    flex-shrink: 0;
    width: 56px; height: 56px;
    border-radius: 50%;
    border: 1px solid #84252B;
    overflow: hidden;
    background: #f0f0f0;
    display: flex; align-items: center; justify-content: center;
    color: #bbb; font-size: 1.4rem;
}
.sr-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
/* Icône type / numéro de place */
.sr-type-col { flex-shrink: 0; width: 30px; text-align: center; }
.sr-icon-coupe       { font-size: 1.5rem; color: #e6a800; }
.sr-icon-championnat { font-size: 1.5rem; color: #84252B; }
.sr-place-num { display: inline-block; font-size: .8rem; font-weight: 700; color: #888; line-height: 1; }
/* Infos */
.sr-info { flex: 1; min-width: 0; }
.sr-title { font-weight: 700; color: #333; font-size: .95rem; text-decoration: underline; margin-bottom: 4px; }
.sr-winner { font-size: .85rem; color: #555; }
/* Date */
.sr-date-col { flex-shrink: 0; min-width: 170px; font-size: .8rem; color: #555; }
.sr-date-label { display: block; font-weight: 600; margin-bottom: 2px; }
.sr-date-value { display: block; }
/* PDF */
.sr-pdf { flex-shrink: 0; text-align: center; }
.sr-pdf-link { color: #c0392b; text-decoration: none; transition: transform .15s, color .15s; display: block; }
.sr-pdf-link i { font-size: 1.8rem; }
.sr-pdf-link:hover { color: #84252B; transform: scale(1.1); }
.sr-pdf-none { color: #ccc; cursor: default; display: block; }
.sr-pdf-none i { font-size: 1.8rem; }
.sr-count { font-size: .8rem; font-weight: 400; opacity: .75; }

/* ── Tableaux catégories du joueur ── */
.cat-table { width:100%; border-collapse:collapse; font-size:.85rem; color:#333; border:1px solid #e5e5e5; }
.cat-table thead th {
    background:#84252B; color:#fff; font-weight:700;
    padding:7px 10px; text-align:left; border:none;
}
.cat-table thead th.cat-th-title { letter-spacing:.5px; text-transform:uppercase; }
.cat-table tbody td {
    padding:6px 10px; border-bottom:1px solid #ececec;
    vertical-align:middle; color:#333; font-weight:600;
}
.cat-table tbody td:first-child { width:30%; }
.cat-table tbody td:last-child { width:30%; }
.cat-table tbody tr:nth-child(odd)  td { background:#fafafa; }
.cat-table tbody tr:nth-child(even) td { background:#fff; }
.cat-table tbody tr:hover td { background:#fdf3f3 !important; }
.cat-none { color:#ccc; }
.cat-statut-cell { display:flex; justify-content:space-between; align-items:center; gap:6px; }
.cat-statut { font-size:.82rem; white-space:nowrap; }
.cat-col { width:100%; padding-right:.75rem; padding-left:.75rem; }
@media (min-width:768px) {
    .cat-col { width:50%; }
}
@media (max-width:767px) {
    .cat-table tbody td:first-child { width:35%; }
    .cat-table tbody td:last-child { width:35%; }
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

  <!-- Catégories du joueur (fédérés uniquement) -->
  <?php if ($m->is_federated): ?>
  <div class="section-card">
    <div class="section-title"><i class="fas fa-layer-group me-2"></i>Catégories du joueur</div>

    <div class="row">
      <div class="cat-col mb-20">
        <table class="cat-table">
          <thead>
            <tr>
              <th colspan="3" class="cat-th-title">
                Petit Billard <span style="font-size:.75rem;font-weight:400;opacity:.75;margin-left:4px">(2m30)</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align:right">Partie Libre :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Bande :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Cadre 38/2 :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Cadre 57/2 :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">3 Bandes :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="cat-col mb-20">
        <table class="cat-table">
          <thead>
            <tr>
              <th colspan="3" class="cat-th-title">
                Grand Billard <span style="font-size:.75rem;font-weight:400;opacity:.75;margin-left:4px">(2m84)</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align:right">Partie Libre :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Bande :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Cadre 47/2 :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Cadre 47/1 :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">Cadre 71/2 :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
            <tr>
              <td style="text-align:right">3 Bandes :</td>
              <td><div class="cat-statut-cell"><span><span class="cat-none">—</span></span></div></td>
              <td><span class="cat-none">—</span></td>
            </tr>
          </tbody>
        </table>
      </div>
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

  <!-- Palmarès du membre (saison en cours) -->
  <?php if (!empty($sportResults)): ?>
  <div class="accordion tm-accordion accordion-classic accordion-theme-colored1 mb-4" id="accordionPalmares">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingPalmares">
        <button class="accordion-button" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapsePalmares"
                aria-expanded="true" aria-controls="collapsePalmares">
          <i class="fas fa-trophy me-3" style="opacity:.7"></i>
          <strong>Mon Palmarès — Saison <?= esc($currentSeason) ?></strong>
          <span class="sr-count ms-5">
            <?= count($sportResults) ?>&nbsp;
            résultat<?= count($sportResults) > 1 ? 's' : '' ?>&nbsp;
            encodé<?= count($sportResults) > 1 ? 's' : '' ?>
          </span>
        </button>
      </h2>
      <div id="collapsePalmares" class="accordion-collapse collapse show"
           aria-labelledby="headingPalmares" data-bs-parent="#accordionPalmares">
        <div class="accordion-body py-0 px-0">
          <ul class="sr-list">
            <?php $resArr = array_values($sportResults); $resCount = count($resArr); ?>
            <?php foreach ($resArr as $i => $r): ?>
            <?php
              $place      = (int) ($r->place ?? 1);
              $isGroupEnd = ($i === $resCount - 1) || ($resArr[$i + 1]->title !== $r->title);
              $dateStr    = null;
              if ($r->final_date) {
                  $dt     = new DateTime($r->final_date);
                  $days   = ['','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
                  $months = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
                  $dateStr = $days[(int)$dt->format('N')] . ' ' . $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
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

              <!-- Photo (cliquable si résultat lié à une équipe) -->
              <?php
                $teamUrl = null;
                if (!empty($r->cdr_team_id))  $teamUrl = base_url('saison/coupe-des-regions/' . $r->cdr_team_id);
                elseif (!empty($r->intm_team_id)) $teamUrl = base_url('saison/intm/' . $r->intm_team_id);
              ?>
              <div class="sr-photo">
                <?php if ($teamUrl): ?>
                  <a href="<?= $teamUrl ?>" title="Voir la page de l'équipe">
                    <?php if ($photo): ?>
                      <img src="<?= esc($photo) ?>" alt="<?= esc($m->last_name . ' ' . $m->first_name) ?>">
                    <?php else: ?>
                      <i class="fas fa-user"></i>
                    <?php endif; ?>
                  </a>
                <?php elseif ($photo): ?>
                  <img src="<?= esc($photo) ?>" alt="<?= esc($m->last_name . ' ' . $m->first_name) ?>">
                <?php else: ?>
                  <i class="fas fa-user"></i>
                <?php endif; ?>
              </div>

              <!-- Icône type (1er uniquement) -->
              <div class="sr-type-col">
                <?php if ($place === 1): ?>
                  <?php if ($r->type === 'coupe'): ?>
                    <i class="fas fa-trophy sr-icon-coupe" title="Coupe"></i>
                  <?php elseif ($r->type === 'championnat'): ?>
                    <i class="fas fa-medal sr-icon-championnat" title="Championnat"></i>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="sr-place-num">&nbsp;</span>
                <?php endif; ?>
              </div>

              <!-- Titre + place -->
              <div class="sr-info">
                <div class="sr-title"><?= esc($r->title) ?></div>
                <div class="sr-winner"><?= $placeLabel ?></div>
              </div>

              <!-- Date -->
              <div class="sr-date-col">
                <?php if ($dateStr): ?>
                  <span class="sr-date-label"><i class="far fa-calendar-alt me-1"></i>Finale disputée le :</span>
                  <span class="sr-date-value"><?= $dateStr ?></span>
                <?php endif; ?>
              </div>

              <!-- PDF -->
              <div class="sr-pdf">
                <?php if ($hasPdf): ?>
                  <a href="<?= base_url('uploads/PDF/SportResults/' . $r->pdf_file) ?>"
                     target="_blank" rel="noopener" class="sr-pdf-link" title="Télécharger les résultats (PDF)">
                    <i class="fas fa-file-pdf"></i>
                  </a>
                <?php else: ?>
                  <span class="sr-pdf-none" title="Pas de fichier PDF disponible">
                    <i class="fas fa-file-pdf"></i>
                  </span>
                <?php endif; ?>
              </div>

            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Retour -->
  <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary">
    <i class="fas fa-arrow-left me-2"></i><?= esc($backLabel) ?>
  </a>

</div>

<?= $this->endSection() ?>
