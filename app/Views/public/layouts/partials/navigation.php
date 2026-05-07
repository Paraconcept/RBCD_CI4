<header id="header" class="header header-layout-type-header-2rows">

  <div class="header-top">
    <div class="container">
      <div class="row">
        <div class="col-xl-auto header-top-left align-self-center text-center text-xl-left">
          <ul class="element contact-info">
            <li class="contact-phone"><i class="fa fa-phone font-icon sm-display-block"></i> 0494 797 353</li>
            <li class="contact-email"><i class="fa fa-envelope font-icon sm-display-block"></i> contact@rbcd.be</li>
            <li class="contact-address"><i class="fa fa-map font-icon sm-display-block"></i> B-4820 Dison</li>
          </ul>
        </div>
        <div class="col-xl-auto ms-xl-auto header-top-right align-self-center text-center text-xl-right">
          <div class="element pt-0 pt-lg-10 pb-0">
            <?php if (session()->get('admin_logged_in')): ?>
              <button type="button" class="btn btn-theme-colored2 btn-sm btn-logout-ajax">
                <i class="fa fa-sign-out-alt me-1"></i>Déconnexion
              </button>
            <?php else: ?>
              <button type="button" class="btn btn-theme-colored2 btn-sm"
                      data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fa fa-sign-in-alt me-1"></i>Me connecter
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="header-nav">
    <div class="header-nav-wrapper navbar-scrolltofixed">
      <div class="menuzord-container header-nav-container">
        <div class="container position-relative">
          <div class="row header-nav-col-row">

            <div class="col-sm-auto align-self-center">
              <a class="menuzord-brand site-brand" href="<?= base_url('/') ?>">
                <img class="logo-default logo-1x" src="<?= base_url('assets/images/logo_rbcd.png') ?>"
                     onerror="this.src='<?= base_url('studypress/images/logo-wide.png') ?>'" alt="RBC Disonais">
              </a>
            </div>

            <div class="col-sm-auto ms-auto pr-0 align-self-center">
              <nav id="top-primary-nav" class="menuzord theme-color1" data-effect="slide" data-animation="none" data-align="right">
                <ul id="main-nav" class="menuzord-menu">

                  <li class="<?= (current_url() === base_url('/') ? 'active' : '') ?> menu-item">
                    <a href="<?= base_url('/') ?>"><i class="fas fa-home"></i></a>
                  </li>

                  <li class="menu-item">
                    <a href="#">Le Club</a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('club/histoire') ?>">Histoire &amp; présentation</a></li>
                      <li><a href="<?= base_url('club/comite') ?>">Le Comité</a></li>
                      <li><a href="<?= base_url('club/membres') ?>">Nos Membres</a></li>
                      <li><a href="<?= base_url('club/ecole-de-billard') ?>">Notre école de billard</a></li>
                      <li><a href="<?= base_url('contact') ?>">Contact</a></li>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="#">Saison <?= ANNEE_1 ?>-<?= ANNEE_2 ?></a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('tableau') ?>">Au Tableau</a></li>
                      <li><a href="<?= base_url('saison/resultats') ?>">Résultats sportifs</a></li>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="#">Archives</a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('tableau') ?>">Journal "Partie Libre"<i class="fas fa-lock"></i></a></li>
                      <li><a href="<?= base_url('saison/resultats') ?>">Résultats sportifs</a></li>
                      <li><a href="<?= base_url('galerie') ?>">Galeries photos<i class="fas fa-images"></i></a></li>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="#">Documents utiles</a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('---') ?>">Documents utiles</a></li>
                      <li><a href="<?= base_url('---') ?>">Statuts du club<i class="far fa-file-pdf"></i></a></li>
                      <li><a href="<?= base_url('---') ?>">Règlement d'ordre intérieur<i class="far fa-file-pdf"></i></a></li>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="<?= base_url('tableau') ?>">Au Tableau</a>
                  </li>

                </ul>
              </nav>
            </div>

            <div class="col-sm-auto align-self-center nav-side-icon-parent">
              <ul class="list-inline nav-side-icon-list">
                <li class="hidden-mobile-mode">
                  <div id="side-panel-trigger" class="side-panel-trigger">
                    <a href="#">
                      <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                      </div>
                    </a>
                  </div>
                </li>
              </ul>
            </div>

          </div>
          <!-- Menu mobile -->
          <div class="row d-block d-xl-none">
            <div class="col-12">
              <nav id="top-primary-nav-clone" class="menuzord d-block d-xl-none default menuzord-color-default menuzord-border-boxed menuzord-responsive" data-effect="slide" data-animation="none" data-align="right">
                <ul id="main-nav-clone" class="menuzord-menu menuzord-right menuzord-indented scrollable"></ul>
              </nav>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</header>
