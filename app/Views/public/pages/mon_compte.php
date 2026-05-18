<?= $this->extend('public/layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
/* ── Tabs Mon compte ── */
.mc-tabs-wrap {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e8e8e8;
    overflow: hidden;
    margin-bottom: 32px;
}
.mc-tabs-nav {
    display: flex;
    border-bottom: 2px solid #e8e8e8;
    background: #fafafa;
}
.mc-tabs-nav .nav-link {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 16px 28px;
    font-size: .92rem;
    font-weight: 600;
    color: #555;
    border: none;
    border-bottom: 3px solid transparent;
    border-radius: 0;
    background: transparent;
    text-decoration: none;
    transition: color .2s, border-color .2s;
    cursor: pointer;
    margin-bottom: -2px;
    white-space: nowrap;
}
.mc-tabs-nav .nav-link i {
    font-size: 1.1rem;
    color: #aaa;
    transition: color .2s;
}
.mc-tabs-nav .nav-link.active,
.mc-tabs-nav .nav-link:hover {
    color: #84252B;
    border-bottom-color: #84252B;
}
.mc-tabs-nav .nav-link.active i,
.mc-tabs-nav .nav-link:hover i { color: #84252B; }
.mc-tab-pane { display: none; padding: 32px 36px; }
.mc-tab-pane.active { display: block; }
/* Fieldsets */
.mc-section-title {
    font-size: .8rem;
    font-weight: 700;
    color: #84252B;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 18px;
    padding-bottom: 8px;
    border-bottom: 2px solid #84252B;
}
.mc-form-row { margin-bottom: 18px; }
.mc-form-row label {
    font-size: .87rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
    display: block;
}
.mc-form-row .form-control {
    border-radius: 6px;
    border: 1px solid #d0d0d0;
    font-size: .92rem;
    padding: 9px 13px;
}
.mc-form-row .form-control:focus {
    border-color: #84252B;
    box-shadow: 0 0 0 .18rem rgba(132,37,43,.18);
}
/* No-member notice */
.mc-no-member {
    text-align: center;
    padding: 40px 20px;
    color: #777;
}
.mc-no-member i { font-size: 3rem; color: #ccc; display: block; margin-bottom: 14px; }
/* Privacy — toggle switches */
.privacy-row { border-bottom: 1px solid #f0f0f0; }
.privacy-row:last-child { border-bottom: none; }
.privacy-icon { color: #84252B; font-size: 1.05em; }
.privacy-label { font-size: .95em; color: #333; }
.privacy-sublabel { font-size: .8rem; color: #888; margin-top: 2px; }
.privacy-toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    cursor: pointer;
}
.privacy-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.toggle-slider {
    position: absolute;
    inset: 0;
    background-color: #ccc;
    border-radius: 26px;
    transition: background-color .25s;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    top: 3px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform .25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
.privacy-toggle input:checked + .toggle-slider { background-color: #84252B; }
.privacy-toggle.toggle-members input:checked + .toggle-slider { background-color: #555; }
.privacy-toggle input:checked + .toggle-slider::before { transform: translateX(22px); }
/* Vignette photo cliquable */
.privacy-photo-thumb {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    overflow: visible;
    cursor: pointer;
    flex-shrink: 0;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color .2s;
}
/* Clip uniquement le contenu image, pas les pseudo-éléments */
.privacy-photo-thumb img,
.privacy-photo-thumb .fas.fa-user {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    overflow: hidden;
}
.privacy-photo-thumb .fas.fa-user {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #bbb;
    width: auto;
    height: auto;
}
.privacy-photo-thumb:hover { border-color: #84252B; }
/* Tooltip CSS */
.privacy-photo-thumb::after {
    content: 'Cliquez ici pour\A modifier la photo';
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%) scale(.85);
    background: #84252B;
    color: #fff;
    font-size: .72rem;
    font-weight: 600;
    white-space: pre;
    text-align: center;
    padding: 4px 10px;
    border-radius: 4px;
    pointer-events: none;
    opacity: 0;
    transition: opacity .18s, transform .18s;
}
.privacy-photo-thumb::before {
    content: '';
    position: absolute;
    bottom: calc(100% + 2px);
    left: 50%;
    transform: translateX(-50%) scale(.85);
    border: 5px solid transparent;
    border-top-color: #84252B;
    pointer-events: none;
    opacity: 0;
    transition: opacity .18s, transform .18s;
}
.privacy-photo-thumb:hover::after,
.privacy-photo-thumb:hover::before {
    opacity: 1;
    transform: translateX(-50%) scale(1);
}
.privacy-photo-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.privacy-photo-thumb .fas.fa-user { font-size: 1.2rem; color: #bbb; }
.privacy-photo-overlay {
    position: absolute;
    inset: 0;
    background: rgba(132,37,43,.55);
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .2s;
    color: #fff;
    font-size: .85rem;
}
.privacy-photo-thumb:hover .privacy-photo-overlay { opacity: 1; }
/* Modal preview */
.photo-modal-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.photo-modal-preview img { width:100%;height:100%;object-fit:cover;display:block; }
.photo-modal-preview .fas.fa-user { font-size: 3rem; color: #bbb; }
/* Zone drag&drop upload */
.photo-upload-area {
    border: 2px dashed #d0d0d0;
    border-radius: 8px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.photo-upload-area:hover { border-color: #84252B; background: #fdf5f5; }
/* Cropper.js overrides */
#cropperImg { display: block; max-width: 100%; }
.cropper-crop-box, .cropper-view-box { border-radius: 0; }
.cropper-crop-box { outline: none; }
.cropper-view-box { outline: none; box-shadow: none; border: 2px solid rgba(255,255,255,.7); }
.cropper-face { background: transparent; }
.cropper-modal { background: #000; opacity: .55 !important; }
/* Submit button */
.mc-btn-save {
    background: #84252B;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 30px;
    font-size: .92rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s;
    margin-top: 8px;
}
.mc-btn-save:hover { background: #6a1c21; }
/* Alert */
.mc-alert {
    border-radius: 6px;
    padding: 12px 18px;
    font-size: .9rem;
    margin-bottom: 22px;
}
.mc-alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
.mc-alert-error   { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
/* Mobile : tabs scrollables */
@media (max-width: 600px) {
    .mc-tabs-nav { overflow-x: auto; flex-wrap: nowrap; }
    .mc-tab-pane { padding: 22px 18px; }
    .mc-tabs-nav .nav-link { padding: 14px 18px; }
}
/* ── Onglet Mes statistiques ── */
.ms-stat-card {
    text-align: center;
    background: #f8f9fa;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    padding: 14px 10px 10px;
}
.ms-stat-card .ms-val { font-size: 1.7rem; font-weight: 700; line-height: 1; }
.ms-stat-card .ms-lbl { font-size: .72rem; color: #202020; margin-top: 4px; text-transform: uppercase; letter-spacing: .5px; }
.ms-stat-solde-ok      { color: #2e7d32; }
.ms-stat-solde-deficit { color: #c62828; }
.ms-stat-card-ok      { background: #bbecb1; border-color: #93C37D; }
.ms-stat-card-deficit { background: #f0b0b7; border-color: #D9534F; }
/* Ligne des 7 cartes stats — CSS Grid */
.ms-stats-row {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-bottom: 1.5rem;
}
.ms-stat-item { min-width: 0; }
/* Mobile : disposition 2 / 3 / 2 (grille virtuelle de 6 colonnes) */
@media (max-width: 575.98px) {
  .ms-stats-row { grid-template-columns: repeat(6, 1fr); }
  .ms-stat-item:nth-child(1),
  .ms-stat-item:nth-child(2),
  .ms-stat-item:nth-child(6),
  .ms-stat-item:nth-child(7) { grid-column: span 3; }
  .ms-stat-item:nth-child(3),
  .ms-stat-item:nth-child(4),
  .ms-stat-item:nth-child(5) { grid-column: span 2; }
}
/* Grille calendrier perso — flexbox (pas de <table> pour éviter width:100% du thème) */
.ms-scroll-wrap  { overflow-x: auto; max-width: 100%; }
.ms-cal          { display: flex; flex-direction: column; width: fit-content; gap: 0; }
.ms-cal-row      { display: flex; flex-direction: row; }
.ms-cal-cell     {
    width: 28px; min-width: 28px; max-width: 28px;
    height: 28px; min-height: 28px;
    box-sizing: border-box;
    border: 1px solid #e0e0e0;
    margin: -1px 0 0 -1px;   /* collapse borders */
    flex-shrink: 0;
}
.ms-cal-head     {
    background: #84252B; color: #fff;
    font-size: .7rem; line-height: 1.2;
    font-weight: 600; text-align: center;
    display: flex; align-items: center; justify-content: center;
    text-align: center; padding: 0;
    height: 60px; min-height: 60px;
}
.ms-cell-home     { background: #93C37D; }
.ms-cell-arb      { background: #D9534F; }
.ms-cell-bar      { background: #117DC4; }
.ms-cell-mrq      { background: #FFC109; }
.ms-cell-home-arb { background: linear-gradient(135deg, #93C37D 50%, #D9534F 50%); }
.ms-cell-home-bar { background: linear-gradient(135deg, #93C37D 50%, #117DC4 50%); }
.ms-cell-home-mrq { background: linear-gradient(135deg, #93C37D 50%, #FFC109 50%); }
.ms-cell-empty    { background: #fafafa; }
/* Cellules sommaire (col. de droite) */
.ms-cal-sum {
    width: 44px; min-width: 44px; max-width: 44px;
    height: 28px; min-height: 28px;
    box-sizing: border-box;
    border: 1px solid #adb5bd;
    margin: -1px 0 0 -1px;
    flex-shrink: 0;
    background: #f8f9fa;
    font-size: .72rem; font-weight: 600;
    display: flex; align-items: center; justify-content: center;
}
.ms-cal-sum-head {
    background: #84252B; color: #fff;
    height: 60px; min-height: 60px;
    font-size: .68rem; font-weight: 700; text-align: center; line-height: 1.2;
}
.ms-cal-sum-sep { border-left: 2px solid #adb5bd !important; }
.ms-legend-dot { display:inline-block; width:12px; height:12px; border-radius:2px; vertical-align:middle; margin-right:3px; }
/* Cellule nom sticky gauche */
.ms-cal-name {
    min-width: 110px; width: 110px;
    box-sizing: border-box;
    border: 1px solid #e0e0e0;
    border-right: 2px solid #adb5bd !important;
    margin: -1px 0 0 -1px;
    flex-shrink: 0;
    position: sticky; left: 0; z-index: 2;
    font-size: .72rem; font-weight: 600;
    display: flex; align-items: center;
    padding: 0 6px;
    height: 28px; min-height: 28px;
}
.ms-cal-name-head {
    background: #84252B; color: #fff;
    height: 60px; min-height: 60px;
    font-size: .68rem; font-weight: 700;
}
.ms-cal-name-deficit { background: #f0b0b7; color: #212529; }
.ms-cal-name-ok      { background: #bbecb1; color: #212529; }
.ms-cal-name-none    { background: #fff;    color: #212529; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container pt-40 pb-60">

<?php
  $activeTab  = $activeTab ?? 'coordonnees';
  $tachoColor = null;
  if ($member && $member->is_federated && $memberStats) {
      $s = $memberStats['solde'];
      $tachoColor = $s > 0 ? '#2e7d32' : ($s >= -0.5 ? '#e67e22' : '#c0392b');
  }
?>

<div class="mc-tabs-wrap">

  <!-- Tabs nav -->
  <div class="mc-tabs-nav" role="tablist">
    <a href="?tab=coordonnees" role="tab"
       class="nav-link <?= $activeTab === 'coordonnees' ? 'active' : '' ?>">
      <i class="fas fa-address-card"></i>Mes coordonnées
    </a>
    <a href="?tab=mot-de-passe" role="tab"
       class="nav-link <?= $activeTab === 'mot-de-passe' ? 'active' : '' ?>">
      <i class="fas fa-lock"></i>Mot de passe
    </a>
    <a href="?tab=confidentialite" role="tab"
       class="nav-link <?= $activeTab === 'confidentialite' ? 'active' : '' ?>">
      <i class="fas fa-eye-slash"></i>Confidentialité
    </a>
    <a href="?tab=statistiques" role="tab"
       class="nav-link <?= $activeTab === 'statistiques' ? 'active' : '' ?>">
      <i class="fas fa-chart-bar"></i>Mes statistiques
      <?php if ($tachoColor): ?>
        <i class="fas fa-tachometer-alt ms-1" style="color:<?= $tachoColor ?>;font-size:1.3rem;"></i>
      <?php endif; ?>
    </a>
  </div>

  <!-- ── Tab 1 : Coordonnées ── -->
  <div class="mc-tab-pane <?= $activeTab === 'coordonnees' ? 'active' : '' ?>" id="tab-coordonnees">
    <?php if (!$member): ?>
      <div class="mc-no-member">
        <i class="fas fa-user-slash"></i>
        Votre compte n'est lié à aucun profil membre.<br>
        Contactez un administrateur pour associer votre compte à votre fiche membre.
      </div>
    <?php else: ?>
    <div class="mc-section-title"><i class="fas fa-address-card me-2"></i>Mes coordonnées</div>
    <form method="post" action="<?= base_url('mon-compte/coordonnees') ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-6">
          <div class="mc-form-row">
            <label>Téléphone fixe</label>
            <input type="tel" name="phone" class="form-control"
                   value="<?= esc($member->phone ?? '') ?>" placeholder="Ex. 04 22 33 44 55">
          </div>
          <div class="mc-form-row">
            <label>GSM</label>
            <input type="tel" name="mobile" class="form-control"
                   value="<?= esc($member->mobile ?? '') ?>" placeholder="Ex. 0490 12 34 56">
          </div>
          <div class="mc-form-row">
            <label>E-mail</label>
            <input type="email" name="email" class="form-control"
                   value="<?= esc($member->email ?? '') ?>" placeholder="votre@email.be">
          </div>
        </div>
        <div class="col-md-6">
          <div class="mc-form-row">
            <label>Adresse</label>
            <input type="text" name="address" class="form-control"
                   value="<?= esc($member->address ?? '') ?>" placeholder="Rue, numéro">
          </div>
          <div class="mc-form-row">
            <label>Code postal</label>
            <input type="text" name="postal_code" class="form-control"
                   value="<?= esc($member->postal_code ?? '') ?>" placeholder="Ex. 4820">
          </div>
          <div class="mc-form-row">
            <label>Localité</label>
            <input type="text" name="city" class="form-control"
                   value="<?= esc($member->city ?? '') ?>" placeholder="Ex. DISON">
          </div>
        </div>
      </div>
      <button type="submit" class="mc-btn-save">
        <i class="fas fa-save me-2"></i>Enregistrer
      </button>
    </form>
    <?php endif; ?>
  </div>

  <!-- ── Tab 2 : Mot de passe ── -->
  <div class="mc-tab-pane <?= $activeTab === 'mot-de-passe' ? 'active' : '' ?>" id="tab-mot-de-passe">
    <div class="mc-section-title"><i class="fas fa-lock me-2"></i>Changer mon mot de passe</div>
    <form method="post" action="<?= base_url('mon-compte/mot-de-passe') ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-6">
          <div class="mc-form-row">
            <label>Mot de passe actuel</label>
            <input type="password" name="current_password" class="form-control"
                   autocomplete="current-password" required>
          </div>
          <div class="mc-form-row">
            <label>Nouveau mot de passe <small class="text-muted">(min. 8 caractères)</small></label>
            <input type="password" name="new_password" class="form-control"
                   autocomplete="new-password" required minlength="8">
          </div>
          <div class="mc-form-row">
            <label>Confirmer le nouveau mot de passe</label>
            <input type="password" name="new_password_confirm" class="form-control"
                   autocomplete="new-password" required minlength="8">
          </div>
          <button type="submit" class="mc-btn-save">
            <i class="fas fa-key me-2"></i>Modifier le mot de passe
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- ── Tab 3 : Confidentialité ── -->
  <div class="mc-tab-pane <?= $activeTab === 'confidentialite' ? 'active' : '' ?>" id="tab-confidentialite">
    <?php if (!$member): ?>
      <div class="mc-no-member">
        <i class="fas fa-user-slash"></i>
        Votre compte n'est lié à aucun profil membre.<br>
        Contactez un administrateur pour associer votre compte à votre fiche membre.
      </div>
    <?php else: ?>

    <div class="text-muted mb-3" style="font-size:.88rem;">
      Choisissez les informations visibles sur votre fiche publique (accessible à tout visiteur du site).
    </div>

    <!-- En-tête colonnes -->
    <div class="row align-items-stretch pb-2 mb-1 g-0" style="border-bottom:2px solid #e9ecef;">
      <div class="col-6"></div>
      <div class="col-3 text-center d-flex px-1">
        <span class="badge d-block px-1 py-2 w-100"
              style="font-size:.75rem;background:#84252B;color:#fff;line-height:1.3;">
          <i class="fas fa-globe fa-lg mb-1 d-block"></i>
          Visible<br>pour tous
        </span>
      </div>
      <div class="col-3 text-center d-flex px-1">
        <span class="badge d-block px-1 py-2 w-100"
              style="font-size:.75rem;background:#555;color:#fff;line-height:1.3;">
          <i class="fas fa-user-lock fa-lg mb-1 d-block"></i>
          Visible si<br>connecté
        </span>
      </div>
    </div>

    <form method="post" action="<?= base_url('mon-compte/confidentialite') ?>">
      <?= csrf_field() ?>
      <?php
      $privacyItems = [
          'photo' => [
              'icon'            => 'fa-camera',
              'label'           => 'Photo de profil',
              'value'           => '',
              'checked'         => (bool) ($member->show_photo         ?? 1),
              'checked_members' => (bool) ($member->show_photo_members ?? 0),
          ],
          'phone' => [
              'icon'            => 'fa-phone',
              'label'           => 'Téléphone fixe',
              'value'           => $member->phone ? esc($member->phone) : '<em class="text-muted">Non renseigné</em>',
              'checked'         => (bool) $member->show_phone,
              'checked_members' => (bool) ($member->show_phone_members ?? 0),
          ],
          'mobile' => [
              'icon'            => 'fa-mobile-alt',
              'label'           => 'GSM',
              'value'           => $member->mobile ? esc($member->mobile) : '<em class="text-muted">Non renseigné</em>',
              'checked'         => (bool) $member->show_mobile,
              'checked_members' => (bool) ($member->show_mobile_members ?? 0),
          ],
          'email' => [
              'icon'            => 'fa-envelope',
              'label'           => 'E-mail',
              'value'           => $member->email ? esc($member->email) : '<em class="text-muted">Non renseigné</em>',
              'checked'         => (bool) $member->show_email,
              'checked_members' => (bool) ($member->show_email_members ?? 0),
          ],
          'address' => [
              'icon'            => 'fa-home',
              'label'           => 'Adresse',
              'value'           => (function() use ($member) {
                  $parts = array_filter([$member->address, trim($member->postal_code . ' ' . $member->city)]);
                  return $parts ? esc(implode(', ', $parts)) : '<em class="text-muted">Non renseignée</em>';
              })(),
              'checked'         => (bool) $member->show_address,
              'checked_members' => (bool) ($member->show_address_members ?? 0),
          ],
          'birth_date' => [
              'icon'            => 'fa-birthday-cake',
              'label'           => 'Date de naissance',
              'value'           => $member->birth_date ? date('d/m/Y', strtotime($member->birth_date)) : '<em class="text-muted">Non renseignée</em>',
              'checked'         => (bool) $member->show_birth_date,
              'checked_members' => (bool) ($member->show_birth_date_members ?? 0),
          ],
      ];
      ?>

      <?php
        $memberPhotoUrl = ($member->photo && $member->show_photo)
            ? base_url('uploads/members/' . $member->photo)
            : null;
        // Injecter la vignette cliquable dans la sublabel de la ligne photo
        $privacyItems['photo']['value'] = '<div id="privacy-photo-thumb" class="privacy-photo-thumb"
            data-bs-toggle="modal" data-bs-target="#photoModal">'
            . ($memberPhotoUrl
                ? '<img id="privacy-photo-img" src="' . $memberPhotoUrl . '" alt="Photo de profil">'
                : '<i id="privacy-photo-icon" class="fas fa-user"></i>')
            . '<div class="privacy-photo-overlay"><i class="fas fa-camera"></i></div>'
            . '</div>';
      ?>

      <div class="privacy-list">
      <?php foreach ($privacyItems as $key => $item): ?>
      <div class="privacy-row row align-items-center py-3 g-0">

        <div class="col-6 d-flex align-items-center gap-2">
          <i class="fas <?= $item['icon'] ?> fa-fw privacy-icon"></i>
          <div>
            <span class="privacy-label"><?= $item['label'] ?></span>
            <div class="privacy-sublabel"><?= $item['value'] ?></div>
          </div>
        </div>

        <div class="col-3 text-center d-flex align-items-center justify-content-center">
          <label class="privacy-toggle mb-0" title="Visible pour tous">
            <input type="checkbox" class="privacy-ajax-toggle" name="show_<?= $key ?>" value="1"
                   data-field="show_<?= $key ?>"
                   <?= $item['checked'] ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="col-3 text-center d-flex align-items-center justify-content-center">
          <label class="privacy-toggle toggle-members mb-0" title="Visible si connecté">
            <input type="checkbox" class="privacy-ajax-toggle" name="show_<?= $key ?>_members" value="1"
                   data-field="show_<?= $key ?>_members"
                   <?= $item['checked_members'] ? 'checked' : '' ?>>
            <span class="toggle-slider"></span>
          </label>
        </div>

      </div>
      <?php endforeach; ?>
      </div>

      <div class="d-grid mt-4">
        <button type="submit" class="mc-btn-save" style="width:100%;border-radius:6px;padding:11px;">
          <i class="fas fa-save me-2"></i>Enregistrer mes préférences
        </button>
      </div>
    </form>
    <?php endif; ?>
  </div>

  <!-- ── Tab 4 : Mes statistiques ── -->
  <div class="mc-tab-pane <?= $activeTab === 'statistiques' ? 'active' : '' ?>" id="tab-statistiques">
    <div class="mc-section-title d-flex align-items-center justify-content-between">
      <span><i class="fas fa-chart-bar me-2"></i>Mes statistiques d'arbitrage</span>
    </div>

    <?php if (!$member): ?>
      <div class="mc-no-member">
        <i class="fas fa-user-slash"></i>
        Votre compte n'est lié à aucun profil membre.<br>
        Contactez un administrateur pour associer votre compte à votre fiche membre.
      </div>

    <?php elseif (!$member->is_federated): ?>
      <div class="mc-no-member">
        <i class="fas fa-info-circle"></i>
        Ces statistiques concernent uniquement les joueurs fédérés.
      </div>

    <?php else:
      $ms = $memberStats;
    ?>

      <!-- Explication règle -->
      <div class="mc-alert" style="background: #e8f4fd;border:1px solid #b6d4ea;color:#31708f;font-size:1rem;line-height:1.6;margin-bottom:20px;">
        <div class="d-flex gap-3 align-items-start">
          <i class="fas fa-balance-scale fa-lg mt-1 flex-shrink-0" style="color:#2980b9;"></i>
          <div>
            <p class="mb-2">
              <strong>La règle des 2 pour 3</strong> est appliquée par souci d'<strong>équité entre tous les membres fédérés</strong>.
              Chaque joueur qui bénéficie de l'organisation de rencontres à domicile — salles réservées, arbitrage assuré, bar tenu —
              est invité à contribuer en retour au bon fonctionnement du club.
            </p>
            <p class="mb-4">
              <u>Le principe est simple</u> : <strong>pour 2 jours de jeu à domicile, 3 services sont attendus</strong>
              (arbitrage d'une rencontre ou permanence au bar).<br>
              Ce n'est pas une obligation rigide, mais un engagement collectif qui permet à chacun de profiter
              d'un club qui tourne grâce à l'implication de tous.
            </p>
            <p class="mb-2 text-center"><strong>Plus on est nombreux à contribuer, plus la charge de chacun est légère !</strong></p>
            <p class="mb-0 text-center"><strong>Merci de votre compréhesion et pour votre implication !</strong></p>
          </div>
        </div>
      </div>

      <!-- Saison -->
      <p class="text-dark mb-3" style="font-size:.85rem;">
        Saison <?= $ms['seasonYear'] ?>/<?= $ms['seasonYear'] + 1 ?> &nbsp;·&nbsp;
      </p>

      <!-- Chiffres clés -->
      <div class="ms-stats-row">
        <div class="ms-stat-item">
          <div class="ms-stat-card" style="border-top-color:#93C37D">
            <div class="ms-val"><?= $ms['home_count'] ?></div>
            <div class="ms-lbl">Joué&nbsp;dom.</div>
          </div>
        </div>
        <div class="ms-stat-item">
          <div class="ms-stat-card">
            <div class="ms-val"><?= $ms['required'] == floor($ms['required']) ? (int)$ms['required'] : number_format($ms['required'], 1, '.', '') ?></div>
            <div class="ms-lbl">Requis</div>
          </div>
        </div>
        <div class="ms-stat-item">
          <div class="ms-stat-card" style="border-top-color:#D9534F">
            <div class="ms-val"><?= $ms['arb_count'] ?></div>
            <div class="ms-lbl">Arbitrages</div>
          </div>
        </div>
        <div class="ms-stat-item">
          <div class="ms-stat-card" style="border-top-color:#147DC4">
            <div class="ms-val"><?= $ms['bar_count'] ?></div>
            <div class="ms-lbl">Bar</div>
          </div>
        </div>
        <div class="ms-stat-item">
          <div class="ms-stat-card" style="border-top-color:#FFC109">
            <div class="ms-val"><?= $ms['mrq_count'] ?></div>
            <div class="ms-lbl">Marquages</div>
          </div>
        </div>
        <div class="ms-stat-item">
          <div class="ms-stat-card">
            <div class="ms-val"><?= $ms['done'] ?></div>
            <div class="ms-lbl">Fait</div>
          </div>
        </div>
        <div class="ms-stat-item">
          <?php
            $solde = $ms['solde'];
            $soldeFmt = ($solde == 0) ? '0'
                : ($solde > 0 ? '+' : '') . ($solde == floor($solde) ? (int)$solde : number_format($solde, 1, '.', ''));
            $soldeClass     = $solde < 0 ? 'ms-stat-solde-deficit' : ($solde > 0 ? 'ms-stat-solde-ok' : '');
            $cardSoldeClass = $solde < 0 ? 'ms-stat-card-deficit' : ($solde > 0 ? 'ms-stat-card-ok' : '');
          ?>
          <div class="ms-stat-card <?= $cardSoldeClass ?>">
            <div class="ms-val <?= $soldeClass ?>"><?= $soldeFmt ?></div>
            <div class="ms-lbl"><strong>Mon Solde</strong></div>
          </div>
        </div>
      </div>

      <!-- Statut global -->
      <?php if ($ms['status'] === 'deficit'): ?>
        <div class="mc-alert mc-alert-error mb-4">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Vous êtes redevable de <strong><?= abs($soldeFmt) ?></strong> service(s) — pensez à vous inscrire à l'arbitrage, au bar ou comme marqueur !
        </div>
      <?php elseif ($ms['status'] === 'ok'): ?>
        <div class="mc-alert mc-alert-success mb-4">
          <i class="fas fa-check-circle me-2"></i>
          Vous êtes en ordre pour cette saison — mais rien ne vous empêche de vous inscrire à l'arbitrage, au bar ou comme marqueur et prendre de l'avance !
        </div>
      <?php endif; ?>

      <!-- Grille calendrier -->
      <?php if (empty($ms['dates'])): ?>
        <p class="text-muted" style="font-size:.88rem;">Aucune activité enregistrée cette saison.</p>
      <?php else: ?>
        <!-- Légende -->
        <div class="mb-2 d-flex flex-column" style="gap:.1rem; font-size:.8rem;">
          <span><span class="ms-legend-dot" style="background: #93C37D"></span>Jour de jeu à domicile</span>
          <span><span class="ms-legend-dot" style="background: #D9534F"></span>Arbitrage</span>
          <span><span class="ms-legend-dot" style="background: #117DC4"></span>Bar</span>
          <span><span class="ms-legend-dot" style="background: #FFC109"></span>Marqueur finale</span>
        </div>
        <div class="ms-scroll-wrap">
          <div class="ms-cal">
            <!-- Ligne entêtes dates -->
            <div class="ms-cal-row">
              <div class="ms-cal-name ms-cal-name-head">Joueur</div>
              <?php foreach ($ms['dates'] as $d): ?>
                <div class="ms-cal-cell ms-cal-head">
                  <?= date('d', strtotime($d)) ?><br>
                  <?= date('m', strtotime($d)) ?><br>
                  <?= date('y', strtotime($d)) ?>
                </div>
              <?php endforeach; ?>
              <!-- Entêtes sommaire -->
              <div class="ms-cal-sum ms-cal-sum-head ms-cal-sum-sep" title="Jours joués à domicile">Joué</div>
              <div class="ms-cal-sum ms-cal-sum-head" title="Services requis (règle 2/3)">Requis</div>
              <div class="ms-cal-sum ms-cal-sum-head" title="Arbitrages">Arb.</div>
              <div class="ms-cal-sum ms-cal-sum-head" title="Services bar">Bar</div>
              <div class="ms-cal-sum ms-cal-sum-head" title="Marquages finale">Marq.</div>
              <div class="ms-cal-sum ms-cal-sum-head" title="Total services accomplis">Fait</div>
              <div class="ms-cal-sum ms-cal-sum-head" title="Solde (négatif = redevable)">Solde</div>
            </div>
            <!-- Ligne cellules colorées -->
            <div class="ms-cal-row">
              <?php
                $nameStatusClass = match($ms['status']) {
                    'deficit' => 'ms-cal-name-deficit',
                    'ok'      => 'ms-cal-name-ok',
                    default   => 'ms-cal-name-none',
                };
              ?>
              <div class="ms-cal-name <?= $nameStatusClass ?>">
                <?= esc(mb_strtoupper($member->last_name)) ?> <?= esc(mb_substr($member->first_name, 0, 1)) ?>.
              </div>
              <?php foreach ($ms['dates'] as $d):
                $isHome = isset($ms['home_dates'][$d]);
                $hasArb = isset($ms['arb_dates'][$d]);
                $hasBar = isset($ms['bar_dates'][$d]);
                $hasMrq = isset($ms['mrq_dates'][$d]);

                if ($isHome && $hasArb)      { $c = 'ms-cell-home-arb'; $t = 'Domicile + Arbitrage'; }
                elseif ($isHome && $hasBar)  { $c = 'ms-cell-home-bar'; $t = 'Domicile + Bar'; }
                elseif ($isHome && $hasMrq)  { $c = 'ms-cell-home-mrq'; $t = 'Domicile + Marqueur'; }
                elseif ($isHome)             { $c = 'ms-cell-home';     $t = 'Joue à domicile'; }
                elseif ($hasArb)             { $c = 'ms-cell-arb';      $t = 'Arbitrage'; }
                elseif ($hasBar)             { $c = 'ms-cell-bar';      $t = 'Bar'; }
                elseif ($hasMrq)             { $c = 'ms-cell-mrq';      $t = 'Marqueur finale'; }
                else                        { $c = 'ms-cell-empty';    $t = ''; }
              ?>
                <div class="ms-cal-cell <?= $c ?>" <?= $t ? "title=\"{$t}\"" : '' ?>></div>
              <?php endforeach; ?>
              <!-- Valeurs sommaire -->
              <?php
                $solde    = $ms['solde'];
                $soldeFmt = ($solde == 0) ? '0'
                    : ($solde > 0 ? '+' : '') . ($solde == floor($solde) ? (int)$solde : number_format($solde, 1, '.', ''));
                $soldeColor = $solde < 0 ? '#c62828' : ($solde > 0 ? '#2e7d32' : '');
                $reqFmt = $ms['required'] == floor($ms['required'])
                    ? (int)$ms['required']
                    : number_format($ms['required'], 1, '.', '');
              ?>
              <div class="ms-cal-sum ms-cal-sum-sep"><?= $ms['home_count'] ?></div>
              <div class="ms-cal-sum"><?= $reqFmt ?></div>
              <div class="ms-cal-sum"><?= $ms['arb_count'] ?></div>
              <div class="ms-cal-sum"><?= $ms['bar_count'] ?></div>
              <div class="ms-cal-sum"><?= $ms['mrq_count'] ?: '—' ?></div>
              <div class="ms-cal-sum"><?= $ms['done'] ?></div>
              <div class="ms-cal-sum" style="<?= $soldeColor ? "color:{$soldeColor};font-weight:700;" : '' ?>"><?= $soldeFmt ?></div>
            </div>
          </div><!-- /ms-cal -->
        </div><!-- /ms-scroll-wrap -->
      <?php endif; ?>

    <?php endif; ?>
  </div>

</div><!-- /mc-tabs-wrap -->

<!-- ── Modal : changer la photo de profil ── -->
<?php if ($member): ?>
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom:2px solid #84252B;">
        <h5 class="modal-title" id="photoModalLabel">
          <i class="fas fa-camera me-2" style="color:#84252B"></i>Photo de profil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Zone sélection fichier (visible au départ) -->
        <div id="photo-select-zone">
          <!-- Preview actuelle -->
          <div class="text-center mb-3">
            <div class="photo-modal-preview mx-auto">
              <?php if ($member->photo): ?>
                <img id="photo-preview-img" src="<?= base_url('uploads/members/' . $member->photo) ?>" alt="Aperçu">
              <?php else: ?>
                <i class="fas fa-user"></i>
              <?php endif; ?>
            </div>
          </div>
          <!-- Zone clic pour choisir -->
          <div id="photo-upload-area" class="photo-upload-area" onclick="document.getElementById('photoFileInput').click()">
            <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:#84252B;display:block;margin-bottom:8px"></i>
            <div style="font-size:.9rem;font-weight:600;">Cliquer pour sélectionner une photo</div>
            <div style="font-size:.8rem;color:#888;margin-top:4px">JPG, PNG, WEBP — max 2 Mo</div>
          </div>
          <input type="file" id="photoFileInput" accept="image/*" style="display:none">
        </div>

        <!-- Zone recadrage Cropper.js (masquée au départ) -->
        <div id="photo-cropper-zone" style="display:none;">
          <div style="width:100%;max-width:320px;height:320px;margin:0 auto;overflow:hidden;">
            <img id="cropperImg" src="" alt="" style="display:block;max-width:100%;">
          </div>
          <!-- Contrôles zoom -->
          <div class="d-flex gap-2 justify-content-center align-items-center mt-3 mb-1">
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnZoomOut" title="Zoom arrière">
              <i class="fas fa-search-minus"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnZoomIn" title="Zoom avant">
              <i class="fas fa-search-plus"></i>
            </button>
            <small class="text-muted ms-1">ou molette souris</small>
          </div>
          <div class="text-center mt-2">
            <a href="#" id="btn-reselect" style="font-size:.82rem;color:#84252B;">
              <i class="fas fa-redo me-1"></i>Choisir une autre photo
            </a>
          </div>
        </div>

        <div id="photo-modal-error" class="mc-alert mc-alert-error mt-3" style="display:none"></div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <?php if ($member->photo): ?>
          <button type="button" class="btn btn-sm btn-outline-danger" id="btn-delete-photo">
            <i class="fas fa-trash me-1"></i>Supprimer la photo
          </button>
        <?php else: ?>
          <span></span>
        <?php endif; ?>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="mc-btn-save" id="btn-upload-photo" disabled
                  style="padding:7px 20px;margin-top:0;opacity:.5;">
            <i class="fas fa-save me-1"></i>Enregistrer
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

</div><!-- /container -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
(function () {
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  var baseUrl  = '<?= base_url() ?>';

  // ── Toggle Ajax ──────────────────────────────────────────────────
  function postToggle(field, value, cb) {
    var body = new FormData();
    body.append(csrfName, csrfHash);
    body.append('field', field);
    body.append('value', value);
    fetch(baseUrl + 'mon-compte/toggle-privacy', { method: 'POST', body: body })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) return;
        csrfHash = data.csrf;
        if (field === 'show_photo' || field === 'show_photo_members') updatePhotoThumb(data.photoUrl);
        if (cb) cb(data);
      });
  }

  // ── Lien "pour tous" ↔ "si connecté" ─────────────────────────────
  function lockMembersToggle(membersLabel, lock) {
    membersLabel.style.pointerEvents = lock ? 'none' : '';
    membersLabel.title = lock ? 'Inclus dans "Visible pour tous"' : 'Visible si connecté';
  }

  document.querySelectorAll('.privacy-ajax-toggle:not([data-field$="_members"])').forEach(function (pubCb) {
    var membersField = pubCb.dataset.field + '_members';
    var membersCb    = document.querySelector('[data-field="' + membersField + '"]');
    if (!membersCb) return;
    var membersLabel = membersCb.closest('label');

    // État initial
    if (pubCb.checked) {
      membersCb.checked = true;
      lockMembersToggle(membersLabel, true);
    }

    pubCb.addEventListener('change', function () {
      var val = this.checked ? 1 : 0;
      postToggle(this.dataset.field, val);
      if (this.checked) {
        membersCb.checked = true;
        lockMembersToggle(membersLabel, true);
      } else {
        lockMembersToggle(membersLabel, false);
      }
    });

    membersCb.addEventListener('change', function () {
      var val = this.checked ? 1 : 0;
      postToggle(this.dataset.field, val, function () {
        if (!membersCb.checked && pubCb.checked) {
          pubCb.checked = false;
          postToggle(pubCb.dataset.field, 0);
        }
      });
    });
  });

  var OVERLAY = '<div class="privacy-photo-overlay"><i class="fas fa-camera"></i></div>';

  function updatePhotoThumb(photoUrl) {
    var thumb = document.getElementById('privacy-photo-thumb');
    if (!thumb) return;
    if (photoUrl) {
      thumb.innerHTML = '<img id="privacy-photo-img" src="' + photoUrl + '" alt="">' + OVERLAY;
    } else {
      thumb.innerHTML = '<i id="privacy-photo-icon" class="fas fa-user"></i>' + OVERLAY;
    }
  }

  // ── Modal photo + Cropper.js ─────────────────────────────────────
  var cropper      = null;
  var fileInput    = document.getElementById('photoFileInput');
  var btnUpload    = document.getElementById('btn-upload-photo');
  var btnDelete    = document.getElementById('btn-delete-photo');
  var btnZoomIn    = document.getElementById('btnZoomIn');
  var btnZoomOut   = document.getElementById('btnZoomOut');
  var btnReselect  = document.getElementById('btn-reselect');
  var cropperImg   = document.getElementById('cropperImg');
  var selectZone   = document.getElementById('photo-select-zone');
  var cropperZone  = document.getElementById('photo-cropper-zone');
  var modalError   = document.getElementById('photo-modal-error');

  function showSelectZone() {
    if (cropper) { cropper.destroy(); cropper = null; }
    cropperZone.style.display = 'none';
    selectZone.style.display  = '';
    setBtnState(false);
    if (fileInput) fileInput.value = '';
    if (modalError) modalError.style.display = 'none';
  }

  function showCropperZone(src) {
    selectZone.style.display  = 'none';
    cropperZone.style.display = '';
    cropperImg.src = src;

    if (cropper) cropper.destroy();
    cropper = new Cropper(cropperImg, {
      aspectRatio: 1,
      viewMode: 1,
      dragMode: 'move',
      autoCropArea: 1,
      restore: false,
      guides: false,
      center: true,
      highlight: false,
      background: false,
      cropBoxMovable: false,
      cropBoxResizable: false,
      toggleDragModeOnDblclick: false,
      wheelZoomRatio: 0.08,
      ready: function () {
        var img       = this.cropper.getImageData();
        var container = this.cropper.getContainerData();
        var coverZoom   = Math.max(container.width / img.naturalWidth, container.height / img.naturalHeight);
        var containZoom = Math.min(container.width / img.naturalWidth, container.height / img.naturalHeight);
        cropperImg.dataset.minZoom = containZoom;
        this.cropper.zoomTo(coverZoom);
        setBtnState(true);
      },
      zoom: function (e) {
        var min = parseFloat(cropperImg.dataset.minZoom || 0);
        if (e.detail.ratio < min) { e.preventDefault(); this.cropper.zoomTo(min); }
      },
    });
  }

  function setBtnState(active) {
    if (!btnUpload) return;
    btnUpload.disabled     = !active;
    btnUpload.style.opacity = active ? '1' : '.5';
  }

  if (fileInput) {
    fileInput.addEventListener('change', function () {
      if (!this.files.length) return;
      var file   = this.files[0];
      var reader = new FileReader();
      reader.onload = function (e) { showCropperZone(e.target.result); };
      reader.readAsDataURL(file);
    });
  }

  if (btnReselect) {
    btnReselect.addEventListener('click', function (e) {
      e.preventDefault();
      showSelectZone();
    });
  }

  if (btnZoomIn)  btnZoomIn.addEventListener('click',  function () { if (cropper) cropper.zoom(0.1); });
  if (btnZoomOut) btnZoomOut.addEventListener('click', function () {
    if (!cropper) return;
    var min = parseFloat(cropperImg.dataset.minZoom || 0);
    var cur = cropper.getImageData().ratio;
    cropper.zoomTo(Math.max(cur - 0.1, min));
  });

  if (btnUpload) {
    btnUpload.addEventListener('click', function () {
      if (!cropper) return;
      var canvas    = cropper.getCroppedCanvas({ width: 400, height: 400 });
      var photoData = canvas.toDataURL('image/jpeg', 0.85);
      var body      = new FormData();
      body.append(csrfName, csrfHash);
      body.append('photo_data', photoData);

      btnUpload.disabled = true;
      btnUpload.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Envoi…';

      fetch(baseUrl + 'mon-compte/upload-photo', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          csrfHash = data.csrf;
          if (!data.success) {
            if (modalError) { modalError.textContent = data.error; modalError.style.display = ''; }
            setBtnState(true);
            btnUpload.innerHTML = '<i class="fas fa-save me-1"></i>Enregistrer';
            return;
          }
          bootstrap.Modal.getInstance(document.getElementById('photoModal')).hide();
          updatePhotoThumb(data.photoUrl);
          // Mettre à jour la preview dans la modal pour la prochaine ouverture
          var prevImg = document.getElementById('photo-preview-img');
          if (prevImg) { prevImg.src = data.photoUrl; }
          // Ajouter le bouton supprimer si absent
          if (!btnDelete) {
            var span = document.querySelector('#photoModal .modal-footer span');
            if (span) {
              var del = document.createElement('button');
              del.type = 'button'; del.className = 'btn btn-sm btn-outline-danger'; del.id = 'btn-delete-photo';
              del.innerHTML = '<i class="fas fa-trash me-1"></i>Supprimer la photo';
              span.replaceWith(del);
              del.addEventListener('click', doDeletePhoto);
              btnDelete = del;
            }
          }
        });
    });
  }

  function doDeletePhoto() {
    if (!confirm('Supprimer définitivement votre photo de profil ?')) return;
    var body = new FormData();
    body.append(csrfName, csrfHash);
    fetch(baseUrl + 'mon-compte/delete-photo', { method: 'POST', body: body })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        csrfHash = data.csrf;
        if (!data.success) return;
        bootstrap.Modal.getInstance(document.getElementById('photoModal')).hide();
        updatePhotoThumb(null);
        // Remettre l'avatar dans la preview modale
        var wrap = document.querySelector('#photo-select-zone .photo-modal-preview');
        if (wrap) wrap.innerHTML = '<i class="fas fa-user"></i>';
        if (btnDelete) { btnDelete.remove(); btnDelete = null; }
      });
  }

  if (btnDelete) btnDelete.addEventListener('click', doDeletePhoto);

  // Réinitialiser à chaque ouverture
  document.getElementById('photoModal')?.addEventListener('show.bs.modal', function () {
    showSelectZone();
  });
})();
</script>
<?= $this->endSection() ?>
