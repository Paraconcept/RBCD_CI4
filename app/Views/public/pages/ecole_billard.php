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

        <h4 class="font-weight-700 mt-0 mb-10">Un apprentissage adapté à chacun</h4>
        <p class="mb-30">
          Notre instructeur, <strong>Max Aussems</strong>, dispense des cours entièrement personnalisés,
          adaptés au niveau et au mode de jeu préféré de chaque élève — que vous souhaitiez pratiquer
          la <strong>libre</strong>, le <strong>cadre</strong> ou le <strong>3 bandes</strong>.
        </p>

        <div class="row">

          <div class="col-sm-6 mb-30">
            <div class="tm-sc-icon-box icon-box icon-left tm-iconbox-icontype-font-icon text-left iconbox-centered-in-responsive iconbox-theme-colored1 icon-position-icon-left">
              <div class="icon-box-wrapper">
                <a class="icon icon-type-font-icon icon-circled icon-sm icon-dark icon-theme-colored1 mt-10">
                  <i class="fas fa-calendar-alt"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5">4 séances par mois</h5>
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
                  <h5 class="icon-box-title text-uppercase mb-5">Samedi, 10h — 12h</h5>
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
                  <h5 class="icon-box-title text-uppercase mb-5">50 € par an</h5>
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
                  <i class="fas fa-chalkboard-teacher"></i>
                </a>
                <div class="icon-text">
                  <h5 class="icon-box-title text-uppercase mb-5">Max Aussems</h5>
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

          <div class="ecole-contact-line mb-15">
            <i class="fas fa-user"></i>
            <div>
              <strong>Nathalie Cavelier</strong><br>
              <small>Responsable inscriptions</small>
            </div>
          </div>
          <div class="ecole-contact-line mb-15">
            <i class="fas fa-phone"></i>
            <div><a href="tel:+32494360306">0494 / 36 03 06</a></div>
          </div>
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

    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.icon-box-title {
    font-size: .92rem;
    font-weight: 700;
    color: #333;
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
</style>
<?= $this->endSection() ?>
