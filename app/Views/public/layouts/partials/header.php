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
<style>
@media (hover: hover) {
  .nav-user-dropdown:hover > .dropdown-menu { display: block; }
}
.nav-user-dropdown .dropdown-menu {
    margin-top: 0;
    padding: 0;
    border: none;
    border-top: 2px solid #333;
    border-radius: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
    min-width: 200px;
    background: #fff;
}
.nav-user-dropdown .dropdown-menu .dropdown-item {
    padding: 10px 25px 10px 22px;
    color: #878787;
    font-size: 14px;
    font-weight: 400;
    border-radius: 0;
    border-left: 0px solid transparent;
    transition: background .15s, border-color .15s, color .15s;
}
.nav-user-dropdown .dropdown-menu .dropdown-item:hover,
.nav-user-dropdown .dropdown-menu .dropdown-item:focus {
    background: #EEEEEE;
    color: #333;
    border-left: 3px solid #333;
}
.nav-user-dropdown .dropdown-menu .dropdown-divider {
    margin: 0;
    border-top: 1px solid #f0f0f0;
}
</style>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<?= $extra_css ?? '' ?>
<?= $styles ?? '' ?>

<!-- Studypress JS (head) -->
<script src="<?= base_url('studypress/js/jquery.js') ?>"></script>
<script src="<?= base_url('studypress/js/popper.min.js') ?>"></script>
<script src="<?= base_url('studypress/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('studypress/js/javascript-plugins-bundle.js') ?>"></script>
<script src="<?= base_url('studypress/js/menuzord/js/menuzord.js') ?>"></script>

<?= $extra_head_js ?? '' ?>

</head>
<body class="tm-container-1300px has-side-panel side-panel-right <?= $body_class ?? '' ?>">

<!-- Side panel droit -->
<div class="side-panel-body-overlay"></div>
<div id="side-panel-container" class="dark" data-tm-bg-img="<?= base_url('studypress/images/side-push-bg.jpg') ?>">
  <div class="side-panel-wrap">
    <div id="side-panel-trigger-close" class="side-panel-trigger">
      <a href="#"><i class="fa fa-times side-panel-trigger-icon"></i></a>
    </div>
    <img class="logo mb-50" src="<?= base_url('assets/images/logo_rbcd.png') ?>" alt="RBC Disonais">
    <div class="widget">
      <h5 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Contact</h5>
      <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
        <ul>
          <li class="contact-phone">
            <div class="icon"><i class="flaticon-contact-042-phone-1"></i></div>
            <div class="text"><a href="tel:+32494797353">0494 / 797 353</a></div>
          </li>
          <li class="contact-email">
            <div class="icon"><i class="flaticon-contact-043-email-1"></i></div>
            <div class="text"><a href="mailto:contact@rbcd.be">contact@rbcd.be</a></div>
          </li>
          <li class="contact-address">
            <div class="icon"><i class="flaticon-contact-047-location"></i></div>
            <div class="text">B-4820 Dison</div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
