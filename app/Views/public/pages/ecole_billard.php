<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto text-center mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title">Notre École de Billard</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description mt-20">
            Depuis 1993, le RBC Disonais propose une école de billard carambole ouverte à tous —
            débutants ou joueurs souhaitant progresser, bienvenue !
          </p>
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="row align-items-start">

      <!-- Gauche : présentation + icon boxes -->
      <div class="col-lg-8">

        <?php
          $teacherName = $teacher
              ? esc($teacher->first_name . ' ' . $teacher->last_name)
              : 'notre instructeur';
          $freq        = $school->frequency_per_month ?? 4;
          $schedule    = $school->schedule ?? 'Samedi, 10h00 — 12h00';
          $price       = $treasury ? number_format((float)$treasury->annual_cotisation, 0, ',', '') : '50';
        ?>
        <h4 class="font-weight-700 mt-0 mb-10">Un apprentissage adapté à chacun</h4>
        <p class="mb-30">
          Notre instructeur, <strong><?= $teacherName ?></strong>, dispense des cours entièrement personnalisés,
          adaptés au niveau et au mode de jeu préféré de chaque élève — que vous souhaitiez pratiquer
          la <strong>libre</strong>, le <strong>cadre</strong> ou le <strong>3 bandes</strong>.
        </p>

        <div class="ecole-illustration mb-30">
          <img src="<?= base_url('assets/images/DessinEcoleBillard.png') ?>"
               alt="Cours de billard — illustration"
               class="img-fluid">
        </div>

        <div class="row">

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-calendar-alt"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5"><?= $freq ?> séance<?= $freq > 1 ? 's' : '' ?> par mois</h5>
                  <p class="mb-0">Des cours réguliers tout au long de l'année pour progresser à votre rythme.</p>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-clock"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5"><?= esc($schedule) ?></h5>
                  <p class="mb-0">Un créneau fixe le samedi matin, idéal pour bien démarrer le week-end.</p>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-coins"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5"><?= $price ?> € par an</h5>
                  <p class="mb-0">Cotisation annuelle tout compris : cours du samedi <em>et</em> accès libre au club.</p>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-key"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5">Accès illimité</h5>
                  <p class="mb-0">En dehors des cours, profitez librement du club et de toutes ses tables.</p>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-user-tie"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5"><?= $teacherName ?></h5>
                  <p class="mb-0">Instructeur expérimenté, il adapte chaque cours au niveau et au style de jeu de l'élève.</p>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-users"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5">Places limitées</h5>
                  <p class="mb-0">Les inscriptions sont soumises à disponibilité pour garantir la qualité de l'enseignement.</p>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Droite : carte inscription -->
      <div class="col-lg-4 mt-sm-30">
        <div class="ecole-card p-30">
          <h4 class="mt-0 mb-20">
            <i class="fas fa-user-plus me-2"></i>S'inscrire
          </h4>
          <p class="mb-20">
            Pour rejoindre l'école de billard, contactez directement notre responsable des inscriptions :
          </p>

          <?php
            $contactName   = $contact ? esc($contact->first_name . ' ' . $contact->last_name) : '';
            $contactMobile = $contact->mobile ?? '';
            $contactHref   = $contactMobile
                ? 'tel:+32' . ltrim(preg_replace('/\D/', '', $contactMobile), '0')
                : '#';
          ?>
          <div class="ecole-contact-line mb-15">
            <i class="fas fa-user"></i>
            <div>
              <strong><?= $contactName ?></strong><br>
              <small>Responsable inscriptions</small>
            </div>
          </div>
          <?php if ($contactMobile): ?>
          <div class="ecole-contact-line mb-15">
            <i class="fas fa-phone"></i>
            <div><a href="<?= $contactHref ?>"><?= esc($contactMobile) ?></a></div>
          </div>
          <?php endif; ?>
          <div class="ecole-contact-line mb-20">
            <i class="fas fa-envelope"></i>
            <div><a href="mailto:contact@rbcd.be">contact@rbcd.be</a></div>
          </div>

          <div class="ecole-card-note">
            <i class="fas fa-info-circle me-1"></i>
            Inscription accordée sous réserve de disponibilité des places.
          </div>
        </div>
      </div>

    </div><!-- /.row align-items-start -->

    <!-- Séparateur -->
    <div class="row mt-20 mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

    <!-- Mutuelles -->
    <div class="row">
      <div class="col-12 text-center">
        <h4 class="font-weight-700 mt-0 mb-10">Votre mutuelle vous rembourse pour l'affiliation à un club sportif :</h4>
        <p class="mb-25 text-muted">Cliquez sur le logo de votre mutuelle pour télécharger le document à remplir :</p>
        <div class="mutuelles-logos">
          <a href="<?= base_url('uploads/mutuelles/MC-ClubSportif.pdf') ?>" title="Mutuelle Chrétienne" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Chretienne.jpg') ?>" alt="Mutuelle Chrétienne">
          </a>
          <a href="<?= base_url('uploads/mutuelles/SOLIDARIS-ClubSportif.pdf') ?>" title="Solidaris — Mutuelle Socialiste" class="mutuelle-link">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Socialiste.jpg') ?>" alt="Solidaris">
          </a>
          <a href="<?= base_url('uploads/mutuelles/MUTUALIA-ClubSportif.pdf') ?>" title="Mutuelle Neutre" class="mutuelle-link" target="_blank">
            <img src="<?= base_url('assets/images/mutuelles/Mut_Neutre.jpg') ?>" alt="Mutuelle Neutre">
          </a>
        </div>
      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.ecole-illustration {
    text-align: center;
}
.ecole-illustration img {
    max-width: 100%;
    border-radius: 6px;
    opacity: .92;
}
.icon-box-title {
    font-size: .92rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px !important;
}
/* Icône blanche dans le rond bleu (icon-dark) */
.iconbox-theme-colored1 .icon-theme-colored1 i {
    color: #fff !important;
}
/* Carte inscription (droite) */
.ecole-card {
    background: #84252B;
    border-radius: 4px;
    color: #fff;
}
.ecole-card h4,
.ecole-card p,
.ecole-card small {
    color: #fff;
}
.ecole-card a {
    color: rgba(255,255,255,.9);
    text-decoration: none;
}
.ecole-card a:hover {
    color: #fff;
    text-decoration: underline;
}
.ecole-contact-line {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.ecole-contact-line i {
    color: rgba(255,255,255,.7);
    font-size: 1rem;
    margin-top: 3px;
    flex-shrink: 0;
    width: 16px;
    text-align: center;
}
.ecole-card-note {
    background: rgba(0,0,0,.15);
    border-radius: 4px;
    padding: 10px 14px;
    font-size: .88rem;
    color: rgba(255,255,255,.85);
}
/* Logos mutuelles */
.mutuelles-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 30px;
}
.mutuelle-link img {
    max-height: 80px;
    max-width: 180px;
    object-fit: contain;
    filter: grayscale(20%);
    opacity: .85;
    transition: opacity .2s, filter .2s, transform .2s;
}
.mutuelle-link:hover img {
    opacity: 1;
    filter: grayscale(0%);
    transform: scale(1.05);
}
</style>
<?= $this->endSection() ?>
