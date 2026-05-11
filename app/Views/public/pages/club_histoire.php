<?= $this->extend('public/layouts/main') ?>

<?= $this->section('extra_css') ?>
<link href="<?= base_url('studypress/js/timeline-cp-responsive-vertical/timeline-cp-responsive-vertical.css') ?>" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto text-center mb-10">
        <div class="tm-sc-heading">
          <h3 class="heading-title">Un peu d'histoire</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description mt-20">
            Depuis 1951, le RBC Disonais cultive la passion du billard carambole à Dison.<br>
            Voici les grandes étapes qui ont forgé l'identité de notre club.
          </p>
        </div>
      </div>
    </div>

    <div class="tm-timeline-responsive-vertical-cp">

      <!-- 1951 — Fondation -->
      <div class="timeline__block">
        <div class="timeline__midpoint timeline__midpoint--withtext">
          <div class="timeline__year"><p>1951</p></div>
        </div>
        <div class="timeline__content timeline__content--left">
          <div class="timeline__text timeline__text--left">
            <div class="timeline__year timeline__year--mobile">1951</div>
            <h4 class="timeline-title">Fondation du club</h4>
            <p>
              Grâce au courage et à la volonté de ses membres fondateurs, le club voit le jour et s'affilie
              dès sa première année à la <strong>Fédération Royale Belge de Billard (F.R.B.B.)</strong>.
              Il s'installe au premier étage du cercle social <em>« Le Foyer »</em>, situé au cœur de Dison.
            </p>
          </div>
        </div>
      </div>

      <!-- 1986 — Premier déménagement -->
      <div class="timeline__block">
        <div class="timeline__midpoint timeline__midpoint--withtext">
          <div class="timeline__year"><p>1986</p></div>
        </div>
        <div class="timeline__content timeline__content--right">
          <div class="timeline__text timeline__text--right">
            <div class="timeline__year timeline__year--mobile">1986</div>
            <h4 class="timeline-title">Premier déménagement</h4>
            <p>
              L'essor du club rendant les locaux du cercle <em>« Le Foyer »</em> trop exigus, le BC Disonais déménage dans
              les bâtiments d'une ancienne école primaire mise à disposition par la commune après plusieurs mois de négociations avec le collège échevinal.
              Le club dispose désormais de <strong>quatre tables</strong> : trois de 2m30 et une de 2m84.<br>
              <em>Nous avons vécu comme cela pendant 10 ans et de nouveau un manque évident de billards se faisait ressentir...</em>
            </p>
          </div>
        </div>
      </div>

      <!-- 1993 — École de billard -->
      <div class="timeline__block">
        <div class="timeline__midpoint timeline__midpoint--withtext">
          <div class="timeline__year"><p>1993</p></div>
        </div>
        <div class="timeline__content timeline__content--left">
          <div class="timeline__text timeline__text--left">
            <div class="timeline__year timeline__year--mobile">1993</div>
            <h4 class="timeline-title">École de billard</h4>
            <p>
              Soucieux d'assurer sa relève, le club crée une <strong>école de billard</strong>
              destinée à initier et former de nouveaux joueurs au billard carambole.
              Cette initiative témoigne de l'engagement du BC Disonais dans le développement du sport local.
            </p>
          </div>
        </div>
      </div>

      <!-- 1996 — Extension des locaux -->
      <div class="timeline__block">
        <div class="timeline__midpoint timeline__midpoint--withtext">
          <div class="timeline__year"><p>1996</p></div>
        </div>
        <div class="timeline__content timeline__content--right">
          <div class="timeline__text timeline__text--right">
            <div class="timeline__year timeline__year--mobile">1996</div>
            <h4 class="timeline-title">Extension des locaux</h4>
            <p>
              Nous avons obtenu de la commune un <strong>agrandissement</strong> de notre chalet ; une annexe qui nous permet 
              d'aligner <strong>4 billards de 2m30</strong> avec suffisamment de confort autour des billards pour y disposer 
              un maximum de chaises et de tables sans gêner les joueurs.<br>
              Du côté de "l'ancien chalet" cela nous a permis de placer, avec tout le confort également, <strong>2 billards de 2m84</strong>..
            </p>
          </div>
        </div>
      </div>

      <!-- 2001 — Titre Royal -->
      <div class="timeline__block">
        <div class="timeline__midpoint timeline__midpoint--withtext">
          <div class="timeline__year"><p>2001</p></div>
        </div>
        <div class="timeline__content timeline__content--left">
          <div class="timeline__text timeline__text--left">
            <div class="timeline__year timeline__year--mobile">2001</div>
            <h4 class="timeline-title">Titre Royal <i class="fas fa-crown ml-1" style="color:#c9a84c;font-size:.9em"></i></h4>
            <div class="d-flex align-items-start" style="gap:16px">
              <div>
                <p class="mb-0">
                  Pour célébrer son <strong>50<sup>e</sup> anniversaire</strong>, le club se voit octroyer
                  le prestigieux titre de <strong>« Royal »</strong> par décret royal.
                  Une reconnaissance qui couronne un demi-siècle de passion, de convivialité et de
                  compétition au sein de la communauté disonaise.
                </p>
              </div>
              <img src="<?= base_url('assets/images/DeuxEcussonsRBCD.jpg') ?>"
                   alt="Écussons RBC Disonais"
                   style="width:120px;flex-shrink:0;">
            </div>
          </div>
        </div>
      </div>

      <!-- Aujourd'hui -->
      <div class="timeline__block">
        <div class="timeline__midpoint timeline__midpoint--withtext">
          <div class="timeline__year"><p>AUJ.</p></div>
        </div>
        <div class="timeline__content timeline__content--right">
          <div class="timeline__text timeline__text--right">
            <div class="timeline__year timeline__year--mobile">Aujourd'hui</div>
            <h4 class="timeline-title">Le club aujourd'hui</h4>
            <p>
              Le <strong>Royal Billard Club Disonais</strong> continue d'écrire son histoire avec
              plusieurs équipes engagées en divisions INTM et CDR. Le club reste un lieu de vie
              chaleureux où se mêlent compétition, formation et convivialité, fidèle à l'esprit
              de ses fondateurs.
            </p>
          </div>
        </div>
      </div>

    </div>
    <!-- fin timeline -->

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Couleurs RBCD pour la timeline */
.tm-timeline-responsive-vertical-cp:before {
    background-color: #d4a0a3;
}
.tm-timeline-responsive-vertical-cp .timeline__midpoint {
    background-color: #84252B;
}
.tm-timeline-responsive-vertical-cp .timeline__midpoint:before,
.tm-timeline-responsive-vertical-cp .timeline__midpoint:after {
    border-color: #d4a0a3;
}
.tm-timeline-responsive-vertical-cp .timeline__content .timeline__year {
    color: #84252B;
}
.tm-timeline-responsive-vertical-cp .timeline__midpoint--withtext .timeline__year {
    color: #fff;
}
.tm-timeline-responsive-vertical-cp .timeline__year--mobile {
    font-size: 1rem;
    font-weight: 700;
    color: #84252B;
    margin-bottom: 4px;
}
.tm-timeline-responsive-vertical-cp .timeline__midpoint--withtext {
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    border: 2px solid #333;
}
.tm-timeline-responsive-vertical-cp .timeline__midpoint--withtext .timeline__year p {
    margin: 0;
    line-height: 1.1;
    text-align: center;
}
.timeline-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
}

/* Correction du débordement et chevauchement sur desktop */
@media (min-width: 1200px) {
    .tm-timeline-responsive-vertical-cp .timeline__content {
        width: 44%;
    }
    .tm-timeline-responsive-vertical-cp .timeline__content--left {
        margin-left: 0;
        padding-right: 50px;
    }
    .tm-timeline-responsive-vertical-cp .timeline__content--right {
        float: right;
        width: 44%;
        left: 0;
        padding-left: 50px;
    }
    .tm-timeline-responsive-vertical-cp .timeline__content--left .timeline__year {
        left: 116%;
    }
    .tm-timeline-responsive-vertical-cp .timeline__content--right .timeline__year {
        right: 116%;
        text-align: right;
    }
}
</style>
<?= $this->endSection() ?>
