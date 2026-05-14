<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto text-center mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title">Tarifs &amp; Fonctionnement</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description mt-20">
            Tout ce qu'il faut savoir pour jouer au RBC Disonais : nos formules d'adhésion,<br>
            nos tarifs et le fonctionnement du club au quotidien.
          </p>
        </div>
      </div>
    </div>

    <?php
      $cotisation  = $treasury ? number_format((float)$treasury->annual_cotisation, 0, ',', '') : '50';
      $forfait     = $treasury ? number_format((float)($treasury->forfait_price  ?? 75),  2, ',', '') : '75,00';
      $horaire     = $treasury ? number_format((float)($treasury->hourly_price   ?? 2.5), 2, ',', '') : '2,50';
      $cours       = $treasury ? number_format((float)($treasury->lesson_price   ?? 5),   2, ',', '') : '5,00';
    ?>

    <!-- ── Tarifs ── -->
    <div class="row mb-50">

      <!-- Cotisation annuelle -->
      <div class="col-md-6 col-lg-3 mb-30">
        <div class="tarif-card h-100">
          <div class="tarif-icon"><i class="fas fa-id-card"></i></div>
          <div class="tarif-amount"><?= $cotisation ?> <span class="tarif-unit">€ / an</span></div>
          <h5 class="tarif-title">Cotisation RBCD</h5>
          <p class="tarif-desc">
            Affiliez-vous au club pour la saison en cours (janvier → décembre).<br>
            Donne accès au club et à toutes ses activités.
          </p>
        </div>
      </div>

      <!-- École -->
      <div class="col-md-6 col-lg-3 mb-30">
        <div class="tarif-card h-100">
          <div class="tarif-icon"><i class="fas fa-graduation-cap"></i></div>
          <div class="tarif-amount"><?= $cours ?> <span class="tarif-unit">€ / séance</span></div>
          <h5 class="tarif-title">École de billard</h5>
          <p class="tarif-desc">
            Même cotisation annuelle que le club, <strong>+ <?= $cours ?> €</strong> par séance de cours.
            <a href="<?= base_url('club/ecole-de-billard') ?>" class="tarif-link">En savoir plus →</a>
          </p>
        </div>
      </div>

      <!-- Tarif horaire -->
      <div class="col-md-6 col-lg-3 mb-30">
        <div class="tarif-card h-100">
          <div class="tarif-icon"><i class="fas fa-clock"></i></div>
          <div class="tarif-amount"><?= $horaire ?> <span class="tarif-unit">€ / h</span></div>
          <h5 class="tarif-title">Billard à l'heure</h5>
          <p class="tarif-desc">
            Pour les membres sans forfait, l'utilisation des tables est facturée à l'heure.
          </p>
        </div>
      </div>

      <!-- Forfait -->
      <div class="col-md-6 col-lg-3 mb-30">
        <div class="tarif-card tarif-card-featured h-100">
          <div class="tarif-badge">Recommandé</div>
          <div class="tarif-icon"><i class="fas fa-infinity"></i></div>
          <div class="tarif-amount"><?= $forfait ?> <span class="tarif-unit">€ / semestre</span></div>
          <h5 class="tarif-title">Forfait billard</h5>
          <p class="tarif-desc">
            Accès illimité aux tables pour un semestre entier.<br>
            Deux périodes : <strong>H1</strong> (jan–jun) et <strong>H2</strong> (jul–déc).
          </p>
        </div>
      </div>

    </div>

    <!-- Séparateur -->
    <div class="row mt-10 mb-40">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur"
             style="width:20px;opacity:0.7;margin:0 10px;">
      </div>
    </div>

    <!-- ── Forfait : comment ça marche ── -->
    <div class="row mb-50">
      <div class="col-lg-10 mx-auto">
        <h4 class="font-weight-700 mb-20 text-center">Comment fonctionne le forfait billard ?</h4>
        <div class="row">
          <div class="col-md-6 mb-20">
            <div class="forfait-block forfait-h1">
              <div class="forfait-label">H1 — 1<sup style="text-transform:lowercase">er</sup> semestre</div>
              <div class="forfait-dates"><i class="fas fa-calendar-alt me-2"></i>1<sup>er</sup> janvier — 30 juin</div>
              <div class="forfait-price"><?= $forfait ?> €</div>
              <p class="forfait-note">Valable pour toute la période, sans limite d'heures.</p>
            </div>
          </div>
          <div class="col-md-6 mb-20">
            <div class="forfait-block forfait-h2">
              <div class="forfait-label">H2 — 2<sup style="text-transform:lowercase">ème</sup> semestre</div>
              <div class="forfait-dates"><i class="fas fa-calendar-alt me-2"></i>1<sup>er</sup> juillet — 31 décembre</div>
              <div class="forfait-price"><?= $forfait ?> €</div>
              <p class="forfait-note">Valable pour toute la période, sans limite d'heures.</p>
            </div>
          </div>
        </div>
        <div class="alert-tarif mt-10">
          <i class="fas fa-info-circle fa-2x me-2" style="color:#2980b9;flex-shrink:0;margin-top:2px;"></i>
          <div>
            Le forfait s'applique à l'utilisation libre des tables en dehors des heures de compétition.<br>
            Il est souscrit par semestre civil et n'est pas proratisé en cas d'inscription en cours de période.
          </div>
        </div>
      </div>
    </div>

    <!-- Séparateur -->
    <div class="row mt-10 mb-40">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur"
             style="width:20px;opacity:0.7;margin:0 10px;">
      </div>
    </div>

    <!-- ── Documents utiles ── -->
    <div class="row">
      <div class="col-lg-10 mx-auto text-center">
        <h4 class="font-weight-700 mb-10">Règlement &amp; documents officiels</h4>
        <p class="text-muted mb-30">
          Le Règlement d'Ordre Intérieur (ROI) détaille l'ensemble des conditions d'accès,
          d'utilisation des installations et de fonctionnement du club.
        </p>
        <div class="docs-links">
          <a href="<?= base_url('documents/roi') ?>" class="doc-btn" target="_blank">
            <i class="far fa-file-pdf"></i>
            <span>Règlement d'ordre intérieur</span>
          </a>
          <a href="<?= base_url('documents/statuts') ?>" class="doc-btn" target="_blank">
            <i class="far fa-file-pdf"></i>
            <span>Statuts du club</span>
          </a>
          <a href="<?= base_url('documents/rgpd') ?>" class="doc-btn" target="_blank">
            <i class="far fa-file-pdf"></i>
            <span>R.G.P.D.</span>
          </a>
        </div>
      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>

/* ── Cartes tarif ── */
.tarif-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: 4px solid #84252B;
    border-radius: 6px;
    padding: 28px 22px 24px;
    text-align: center;
    position: relative;
    transition: box-shadow .2s;
}
.tarif-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }

.tarif-card-featured {
    border-top-color: #84252B;
    background: #84252B;
    color: #fff;
}
.tarif-card-featured .tarif-desc,
.tarif-card-featured .tarif-title { color: #fff; }
.tarif-card-featured .tarif-icon  { color: rgba(255,255,255,.8); }
.tarif-card-featured .tarif-amount { color: #fff; }
.tarif-card-featured .tarif-unit   { color: rgba(255,255,255,.75); }

.tarif-badge {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: #ffc107;
    color: #000;
    font-size: .72rem;
    font-weight: 700;
    padding: 2px 12px;
    border-radius: 20px;
    white-space: nowrap;
}

.tarif-icon {
    font-size: 2rem;
    color: #84252B;
    margin-bottom: 12px;
}
.tarif-amount {
    font-size: 2rem;
    font-weight: 800;
    color: #84252B;
    line-height: 1.1;
    margin-bottom: 6px;
}
.tarif-unit { font-size: 1rem; font-weight: 500; }
.tarif-title {
    font-size: .95rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #333;
    margin: 10px 0 10px;
}
.tarif-desc { font-size: .88rem; color: #555; line-height: 1.55; margin: 0; }
.tarif-link { display: block; margin-top: 8px; font-size: .83rem; color: #84252B; font-weight: 600; }
.tarif-link:hover { text-decoration: underline; }
.tarif-card-featured .tarif-link { color: #ffc107; }

/* ── Blocs forfait ── */
.forfait-block {
    border-radius: 6px;
    padding: 22px 24px;
    text-align: center;
}
.forfait-h1 { background: #fdf3e8; border: 1px solid #eaceb6; }
/* .forfait-h1 { background: #e8f4fd; border: 1px solid #b6d4ea; } */
.forfait-h2 { background: #e8f5e9; border: 1px solid #93C37D; }
.forfait-label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #555;
    margin-bottom: 6px;
}
.forfait-dates { font-size: .88rem; color: #333; margin-bottom: 10px; }
.forfait-price { font-size: 2rem; font-weight: 800; color: #84252B; margin-bottom: 8px; }
.forfait-note  { font-size: .83rem; color: #333; margin: 0; }

/* ── Alerte info ── */
.alert-tarif {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #e8f4fd;
    border: 1px solid #b6d4ea;
    border-radius: 6px;
    padding: 14px 18px;
    font-size: .88rem;
    color: #31708f;
    line-height: 1.6;
}

/* ── Boutons documents ── */
.docs-links {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 16px;
}
.doc-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 2px solid #84252B;
    color: #84252B;
    border-radius: 6px;
    padding: 12px 22px;
    font-size: .9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .18s, color .18s;
}
.doc-btn i { font-size: 1.2rem; }
.doc-btn:hover {
    background: #84252B;
    color: #fff;
    text-decoration: none;
}
</style>
<?= $this->endSection() ?>
