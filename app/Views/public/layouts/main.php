<!DOCTYPE html>
<html dir="ltr" lang="fr">
<head>

<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<title><?= esc($title ?? 'RBC Disonais') ?></title>

<link href="<?= base_url('studypress/images/favicon.png') ?>" rel="shortcut icon" type="image/png">

<!-- Studypress CSS -->
<link href="<?= base_url('studypress/css/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('studypress/css/animate.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('studypress/css/javascript-plugins-bundle.css') ?>" rel="stylesheet">
<link href="<?= base_url('studypress/js/menuzord/css/menuzord.css') ?>" rel="stylesheet">
<link href="<?= base_url('studypress/css/style-main.css') ?>" rel="stylesheet">
<link id="menuzord-menu-skins" href="<?= base_url('studypress/css/menuzord-skins/menuzord-rounded-boxed.css') ?>" rel="stylesheet">
<link href="<?= base_url('studypress/css/responsive.css') ?>" rel="stylesheet">
<link href="<?= base_url('studypress/css/colors/theme-skin-color-set1.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/rbcd-theme.css') ?>" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<?= $this->renderSection('extra_css') ?>
<?= $this->renderSection('styles') ?>

<!-- Studypress JS (head) -->
<script src="<?= base_url('studypress/js/jquery.js') ?>"></script>
<script src="<?= base_url('studypress/js/popper.min.js') ?>"></script>
<script src="<?= base_url('studypress/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('studypress/js/javascript-plugins-bundle.js') ?>"></script>
<script src="<?= base_url('studypress/js/menuzord/js/menuzord.js') ?>"></script>

<?= $this->renderSection('extra_head_js') ?>

</head>
<body class="tm-container-1300px has-side-panel side-panel-right <?= $body_class ?? '' ?>">

<!-- Side panel droit -->
<div class="side-panel-body-overlay"></div>
<div id="side-panel-container" class="dark" data-tm-bg-img="<?= base_url('studypress/images/side-push-bg.jpg') ?>">
  <div class="side-panel-wrap">
    <div id="side-panel-trigger-close" class="side-panel-trigger">
      <a href="#"><i class="fa fa-times side-panel-trigger-icon"></i></a>
    </div>
    <img class="logo mb-50" src="<?= base_url('assets/images/logo-rbcd-white.png') ?>" onerror="this.src='<?= base_url('studypress/images/logo-wide.png') ?>'" alt="RBC Disonais">
    <div class="widget">
      <h5 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Contact</h5>
      <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
        <ul>
          <li class="contact-phone">
            <div class="icon"><i class="flaticon-contact-042-phone-1"></i></div>
            <div class="text"><a href="tel:+32497000000">+32 497 00 00 00</a></div>
          </li>
          <li class="contact-email">
            <div class="icon"><i class="flaticon-contact-043-email-1"></i></div>
            <div class="text"><a href="mailto:info@rbcd.be">info@rbcd.be</a></div>
          </li>
          <li class="contact-address">
            <div class="icon"><i class="flaticon-contact-047-location"></i></div>
            <div class="text">Dison, Belgique</div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div id="wrapper" class="clearfix">

  <!-- Header -->
  <header id="header" class="header header-layout-type-header-2rows">
    <div class="header-top">
      <div class="container">
        <div class="row">
          <div class="col-xl-auto header-top-left align-self-center text-center text-xl-left">
            <ul class="element contact-info">
              <li class="contact-phone"><i class="fa fa-phone font-icon sm-display-block"></i> +32 87 77 55 28</li>
              <li class="contact-email"><i class="fa fa-envelope font-icon sm-display-block"></i> info@rbcd.be</li>
              <li class="contact-address"><i class="fa fa-map font-icon sm-display-block"></i> Dison, Belgique</li>
            </ul>
          </div>
          <div class="col-xl-auto ms-xl-auto header-top-right align-self-center text-center text-xl-right">
            <div class="element pt-0 pb-0">
              <ul class="styled-icons icon-dark icon-theme-colored1 icon-circled clearfix">
                <li><a class="social-link" href="https://www.facebook.com/rbcdisonais" target="_blank"><i class="fab fa-facebook"></i></a></li>
              </ul>
            </div>
            <div class="element pt-0 pt-lg-10 pb-0">
              <?php if (session()->get('public_logged_in')): ?>
                <a href="<?= base_url('deconnexion') ?>" class="btn btn-theme-colored2 btn-sm">
                  <i class="fa fa-sign-out-alt me-1"></i>Déconnexion
                </a>
              <?php else: ?>
                <a href="<?= base_url('connexion') ?>" class="btn btn-theme-colored2 btn-sm">
                  <i class="fa fa-sign-in-alt me-1"></i>Espace membres
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="header-nav tm-enable-navbar-hide-on-scroll">
      <div class="header-nav-wrapper navbar-scrolltofixed">
        <div class="menuzord-container header-nav-container">
          <div class="container position-relative">
            <div class="row header-nav-col-row">
              <div class="col-sm-auto align-self-center">
                <a class="menuzord-brand site-brand" href="<?= base_url('/') ?>">
                  <img class="logo-default logo-1x" src="<?= base_url('assets/images/logo-rbcd.png') ?>" onerror="this.src='<?= base_url('studypress/images/logo-wide.png') ?>'" alt="RBC Disonais">
                </a>
              </div>
              <div class="col-sm-auto ms-auto pr-0 align-self-center">
                <nav id="top-primary-nav" class="menuzord theme-color1" data-effect="slide" data-animation="none" data-align="right">
                  <ul id="main-nav" class="menuzord-menu">
                    <li class="<?= (current_url() === base_url('/') ? 'active' : '') ?> menu-item">
                      <a href="<?= base_url('/') ?>">Accueil</a>
                    </li>
                    <li class="menu-item">
                      <a href="#">Le Club</a>
                      <ul class="dropdown">
                        <li><a href="<?= base_url('club/histoire') ?>">Histoire &amp; présentation</a></li>
                        <li><a href="<?= base_url('club/comite') ?>">Le Comité</a></li>
                        <li><a href="<?= base_url('club/membres') ?>">Les Membres</a></li>
                        <li><a href="<?= base_url('club/cotisations') ?>">Cotisations</a></li>
                        <li><a href="<?= base_url('contact') ?>">Contact</a></li>
                      </ul>
                    </li>
                    <li class="menu-item">
                      <a href="#">Saison</a>
                      <ul class="dropdown">
                        <li><a href="<?= base_url('tableau') ?>">Au Tableau</a></li>
                        <li><a href="<?= base_url('saison/classements') ?>">Classements</a></li>
                        <li><a href="<?= base_url('saison/resultats') ?>">Résultats</a></li>
                        <li><a href="<?= base_url('saison/calendrier') ?>">Calendrier</a></li>
                      </ul>
                    </li>
                    <li class="menu-item">
                      <a href="<?= base_url('actualites') ?>">Actualités</a>
                    </li>
                    <li class="menu-item">
                      <a href="<?= base_url('galerie') ?>">Galerie</a>
                    </li>
                    <li class="menu-item">
                      <a href="#">Archives</a>
                      <ul class="dropdown">
                        <li><a href="<?= base_url('archives') ?>">Résultats passés</a></li>
                        <li><a href="<?= base_url('archives/galeries') ?>">Galeries passées</a></li>
                        <li><a href="<?= base_url('documents') ?>">Documents utiles</a></li>
                      </ul>
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

  <!-- Contenu principal -->
  <div class="main-content-area">

    <?php if (!empty($page_title)): ?>
    <section class="page-title layer-overlay overlay-dark-9 section-typo-light bg-img-center"
             data-tm-bg-img="<?= base_url('studypress/images/bg/bg1.jpg') ?>">
      <div class="container pt-50 pb-50">
        <div class="section-content">
          <div class="row">
            <div class="col-md-12 text-center">
              <h2 class="title"><?= esc($page_title) ?></h2>
              <?php if (!empty($breadcrumbs)): ?>
              <nav class="breadcrumbs" role="navigation" aria-label="Fil d'Ariane">
                <div class="breadcrumbs">
                  <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?><span><i class="fa fa-angle-right"></i></span><?php endif; ?>
                    <?php if (!empty($crumb['url'])): ?>
                      <span><a href="<?= esc($crumb['url']) ?>"><?= esc($crumb['label']) ?></a></span>
                    <?php else: ?>
                      <span class="active"><?= esc($crumb['label']) ?></span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </nav>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($flash = session()->getFlashdata('success')): ?>
      <div class="container mt-20">
        <div class="alert alert-success alert-dismissible fade show">
          <?= esc($flash) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($flash = session()->getFlashdata('error')): ?>
      <div class="container mt-20">
        <div class="alert alert-danger alert-dismissible fade show">
          <?= esc($flash) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>

  </div>
  <!-- fin main-content-area -->

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="footer-widget-area">
      <div class="container pt-90 pb-60">
        <div class="row">
          <div class="col-md-6 col-lg-6 col-xl-3 mb-md-40">
            <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
              <div class="thumb mb-20">
                <img alt="RBC Disonais" src="<?= base_url('assets/images/logo-rbcd-white.png') ?>" onerror="this.src='<?= base_url('studypress/images/logo-wide-white.png') ?>'" style="max-height:60px;">
              </div>
              <div class="description text-gray">Club de billard carambole fondé à Dison, Belgique.</div>
            </div>
            <ul class="styled-icons icon-dark icon-theme-colored1 icon-rounded clearfix mt-20">
              <li><a class="social-link" href="https://www.facebook.com/rbcdisonais" target="_blank"><i class="fab fa-facebook"></i></a></li>
            </ul>
          </div>
          <div class="col-md-6 col-lg-6 col-xl-3 mb-md-40">
            <div class="widget">
              <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Liens rapides</h4>
              <div class="widget widget_nav_menu split-nav-menu clearfix">
                <ul>
                  <li><a href="<?= base_url('/') ?>">Accueil</a></li>
                  <li><a href="<?= base_url('club/histoire') ?>">Le Club</a></li>
                  <li><a href="<?= base_url('tableau') ?>">Au Tableau</a></li>
                  <li><a href="<?= base_url('actualites') ?>">Actualités</a></li>
                  <li><a href="<?= base_url('galerie') ?>">Galerie</a></li>
                  <li><a href="<?= base_url('contact') ?>">Contact</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-6 col-xl-3 mb-md-40">
            <div class="widget">
              <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Contact</h4>
              <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
                <ul>
                  <li class="contact-phone">
                    <div class="icon"><i class="flaticon-contact-042-phone-1"></i></div>
                    <div class="text"><a href="tel:+3287775528">+32 87 77 55 28</a></div>
                  </li>
                  <li class="contact-email">
                    <div class="icon"><i class="flaticon-contact-043-email-1"></i></div>
                    <div class="text"><a href="mailto:info@rbcd.be">info@rbcd.be</a></div>
                  </li>
                  <li class="contact-address">
                    <div class="icon"><i class="flaticon-contact-047-location"></i></div>
                    <div class="text">Dison, Belgique</div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="widget">
              <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Heures d'ouverture</h4>
              <div class="opening-hours border-dark">
                <ul>
                  <li class="clearfix"><span>Lundi — Vendredi :</span><div class="value">19h00 — 23h00</div></li>
                  <li class="clearfix"><span>Samedi :</span><div class="value">14h00 — 23h00</div></li>
                  <li class="clearfix"><span>Dimanche :</span><div class="value">Fermé</div></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="footer-bottom" data-tm-bg-color="#84252B">
        <div class="container">
          <div class="row pt-20 pb-20">
            <div class="col-sm-6">
              <div class="footer-paragraph">
                &copy; <?= date('Y') ?> RBC Disonais. Tous droits réservés.
              </div>
            </div>
            <div class="col-sm-6 text-end">
              <div class="footer-paragraph">
                <a href="<?= base_url('admin') ?>" class="text-gray">Administration</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <a class="scrollToTop" href="#"><i class="fa fa-angle-up"></i></a>
</div>
<!-- fin wrapper -->

<!-- Scripts de bas de page -->
<script src="<?= base_url('studypress/js/custom.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
