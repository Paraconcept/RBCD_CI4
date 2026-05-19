<header id="header" class="header header-layout-type-header-2rows">

  <div class="header-top">
    <div class="container">
      <div class="row">
        <div class="col-xl-auto header-top-left align-self-center text-center text-xl-left d-none d-xl-block">
          <ul class="element contact-info">
            <li class="contact-phone"><i class="fa fa-phone font-icon sm-display-block"></i> <a href="tel:+32494797353">0494 797 353</a></li>
            <li class="contact-email"><i class="fa fa-envelope font-icon sm-display-block"></i> <a href="mailto:contact@rbcd.be">contact@rbcd.be</a></li>
            <li class="contact-address"><i class="fa fa-map font-icon sm-display-block"></i> B-4820 Dison</li>
          </ul>
        </div>
        <div class="col-xl-auto ms-xl-auto header-top-right align-self-center text-center text-xl-right">
          <div>
            <?php if (session()->get('member_logged_in')): ?>
              <div class="dropdown nav-user-dropdown">
                <button class="btn btn-theme-colored2 btn-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-user-circle me-2"></i><?= esc(session()->get('member_name')) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="<?= base_url('mon-compte') ?>">
                      <i class="fas fa-id-card me-2 text-muted"></i>Mon compte
                    </a>
                  </li>
                  <?php if (session()->get('admin_logged_in')): ?>
                  <li>
                    <a class="dropdown-item" href="<?= base_url('admin') ?>">
                      <i class="fas fa-cog me-2 text-muted"></i>Administration
                    </a>
                  </li>
                  <?php endif; ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button type="button" class="dropdown-item text-danger btn-logout-ajax">
                      <i class="fa fa-sign-out-alt me-2"></i>Déconnexion
                    </button>
                  </li>
                </ul>
              </div>
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
                      <li><a href="<?= base_url('club/tarifs') ?>">Tarifs &amp; Fonctionnement</a></li>
                      <li><a href="<?= base_url('contact') ?>">Contact</a></li>
                    </ul>
                  </li>

                  <?php
                    $db = \Config\Database::connect();
                    $cdrTeams  = $db->query('SELECT * FROM cdr_teams  WHERE season = ? ORDER BY CAST(TRIM(SUBSTRING_INDEX(name, " ", -1)) AS UNSIGNED) ASC', [SAISON_EN_COURS])->getResultObject();
                    $intmTeams = $db->query('SELECT * FROM intm_teams WHERE season = ? ORDER BY CAST(TRIM(SUBSTRING_INDEX(name, " ", -1)) AS UNSIGNED) ASC', [SAISON_EN_COURS])->getResultObject();
                  ?>
                  <li class="menu-item">
                    <a href="#">Saison <?= ANNEE_1 ?>-<?= ANNEE_2 ?></a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('saison/resultats') ?>">Résultats sportifs</a></li>
                      <?php if ($cdrTeams): ?>
                      <li class="has-sub">
                        <a href="#">Coupe des Régions</a>
                        <ul class="dropdown">
                          <?php foreach ($cdrTeams as $ct): ?>
                          <li><a href="<?= base_url('saison/coupe-des-regions/' . $ct->id) ?>"><?= esc($ct->name) ?></a></li>
                          <?php endforeach; ?>
                        </ul>
                      </li>
                      <?php endif; ?>
                      <?php if ($intmTeams): ?>
                      <li class="has-sub">
                        <a href="#">I.N.T.M.</a>
                        <ul class="dropdown">
                          <?php foreach ($intmTeams as $it): ?>
                          <li><a href="<?= base_url('saison/intm/' . $it->id) ?>"><?= esc($it->name) ?></a></li>
                          <?php endforeach; ?>
                        </ul>
                      </li>
                      <?php endif; ?>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="#">Archives</a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('archives/journal') ?>">Journal "Partie Libre"<i class="fas fa-lock"></i></a></li>
                      <li><a href="<?= base_url('archives/resultats') ?>">Résultats sportifs</a></li>
                      <li><a href="<?= base_url('galeries') ?>">Galeries photos<i class="fas fa-images"></i></a></li>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="<?= base_url('documents') ?>">Documents utiles</a>
                    <ul class="dropdown">
                      <li><a href="<?= base_url('documents/statuts') ?>">Statuts du club<i class="far fa-file-pdf"></i></a></li>
                      <li><a href="<?= base_url('documents/roi') ?>">Règlement d'ordre intérieur<i class="far fa-file-pdf"></i></a></li>
                      <li><a href="<?= base_url('documents/rgpd') ?>">R.G.P.D.<i class="far fa-file-pdf"></i></a></li>
                      <li><a href="<?= base_url('documents/reglement-sportif') ?>">Règlement sportif<i class="far fa-file-pdf"></i></a></li>
                    </ul>
                  </li>

                  <li class="menu-item">
                    <a href="<?= base_url('tableau') ?>">Au Tableau</a>
                  </li>

                </ul>
              </nav>
            </div>
            
            <!--
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
            -->

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
