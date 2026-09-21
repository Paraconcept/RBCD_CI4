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
      $forfaitBillard      = $treasury ? (float)($treasury->forfait_price ?? 75) : 75;
      $cotisationSemestre  = 30;
      $membreEffectif      = number_format($forfaitBillard + $cotisationSemestre, 2, ',', '');
      $membreSympathisant  = number_format($cotisationSemestre, 2, ',', '');
      $cours               = $treasury ? number_format((float)($treasury->lesson_price ?? 5), 2, ',', '') : '5,00';
    ?>

    <!-- ── Membre effectif / sympathisant ── -->
    <div class="row mb-50">

      <!-- Membre effectif -->
      <div class="col-md-6 mb-30">
        <div class="tarif-card tarif-card-red h-100">
          <div class="tarif-badge">Membre effectif</div>
          <div class="tarif-icon"><i class="fas fa-infinity"></i></div>
          <div class="tarif-amount"><?= $membreEffectif ?> <span class="tarif-unit">€ / semestre</span></div>
          <h5 class="tarif-title">Membre effectif</h5>
          <p class="tarif-desc">
            Cotisation de <?= $membreEffectif ?> € par semestre (janvier-juin / juillet-décembre) permettant un accès aux locaux et un usage illimité des tables de billard.<br>
            Carte nominative semestrielle de membre effectif.
          </p>
        </div>
      </div>

      <!-- Membre sympathisant -->
      <div class="col-md-6 mb-30">
        <div class="tarif-card tarif-card-blue h-100">
          <div class="tarif-badge">Membre sympathisant</div>
          <div class="tarif-icon"><i class="fas fa-door-open"></i></div>
          <div class="tarif-amount"><?= $membreSympathisant ?> <span class="tarif-unit">€ / semestre</span></div>
          <h5 class="tarif-title">Membre sympathisant</h5>
          <p class="tarif-desc">
            Cotisation de <?= $membreSympathisant ?> € par semestre (janvier-juin / juillet-décembre) permettant un accès aux locaux.<br>
            L'utilisation des billards est conditionnée à l'achat d'une carte prépayée de 5 séances d'entraînement (max. 4h consécutives), valable 6 mois.<br>
            Carte nominative semestrielle de membre sympathisant.
          </p>
        </div>
      </div>

    </div>

    <!-- ── École de billard / Mutuelle ── -->
    <div class="row mb-50">

      <!-- École -->
      <div class="col-md-6 mb-30">
        <div class="tarif-card h-100">
          <div class="tarif-icon"><i class="fas fa-graduation-cap"></i></div>
          <div class="tarif-amount"><?= $cours ?> <span class="tarif-unit">€ / séance</span></div>
          <h5 class="tarif-title">École de billard</h5>
          <p class="tarif-desc">
            Carte de membre sympathisant ou effectif, <strong>+ <?= $cours ?> €</strong> par séance de cours.
            <a href="<?= base_url('club/ecole-de-billard') ?>" class="tarif-link">En savoir plus →</a>
          </p>
        </div>
      </div>

      <!-- Mutuelle -->
      <div class="col-md-6 mb-30">
        <div class="tarif-card h-100">
          <div class="tarif-icon"><i class="fas fa-hand-holding-medical"></i></div>
          <h5 class="tarif-title">Intervention mutuelle</h5>
          <p class="tarif-desc">
            Possibilité d'intervention de votre mutuelle.<br>
            Fournissez le document complété à un membre du comité.
            <a href="<?= base_url('documents/remboursements-mutuelle') ?>" class="tarif-link">Votre document mutuelle →</a>
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

    <!-- ── Documents utiles ── -->
    <div class="row">
      <div class="col-lg-10 mx-auto text-center">
        <h4 class="font-weight-700 mb-10">Règlement &amp; documents officiels</h4>
        <p class="text-muted mb-30">
          Le Règlement d'Ordre Intérieur (ROI) détaille l'ensemble des conditions d'accès,
          d'utilisation des installations et de fonctionnement du club.
        </p>
        <div class="docs-links">
          <a href="<?= base_url('documents/roi-reglement-d-ordre-interieur') ?>" class="doc-btn" target="_blank">
            <i class="far fa-file-pdf"></i>
            <span>Règlement d'ordre intérieur</span>
          </a>
          <a href="<?= base_url('documents/reglement-sportif-2026-2027') ?>" class="doc-btn" target="_blank">
            <i class="far fa-file-pdf"></i>
            <span>Règlement Sportif</span>
          </a>
          <a href="<?= base_url('documents/statuts-du-club') ?>" class="doc-btn" target="_blank">
            <i class="far fa-file-pdf"></i>
            <span>Statuts du club</span>
          </a>
          <a href="<?= base_url('documents/r-g-p-d') ?>" class="doc-btn" target="_blank">
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

.tarif-card-red {
    border-top-color: #84252B;
    background: #84252B;
    color: #fff;
}
.tarif-card-red .tarif-desc,
.tarif-card-red .tarif-title { color: #fff; }
.tarif-card-red .tarif-icon  { color: rgba(255,255,255,.8); }
.tarif-card-red .tarif-amount { color: #fff; }
.tarif-card-red .tarif-unit   { color: rgba(255,255,255,.75); }
.tarif-card-red .tarif-link  { color: #ffc107; }

.tarif-card-blue {
    border-top-color: #1B4F72;
    background: #1B4F72;
    color: #fff;
}
.tarif-card-blue .tarif-desc,
.tarif-card-blue .tarif-title { color: #fff; }
.tarif-card-blue .tarif-icon  { color: rgba(255,255,255,.8); }
.tarif-card-blue .tarif-amount { color: #fff; }
.tarif-card-blue .tarif-unit   { color: rgba(255,255,255,.75); }

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
.tarif-card-blue .tarif-link { color: #ffc107; }

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
