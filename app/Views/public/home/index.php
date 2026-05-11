<?= $this->extend('public/layouts/main') ?>

<?= $this->section('extra_css') ?>
<link rel="stylesheet" type="text/css" href="<?= base_url('studypress/js/revolution-slider/css/rs6.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('studypress/js/revolution-slider/extra-rev-slider1.css') ?>">
<style>
/* Écusson slide 1 — taille forcée car RS6 ignore data-wh sur layer manuel */
                            #slider-10-slide-47-layer-img img { width: 240px !important; height: auto !important; }
@media (max-width:1199px) { #slider-10-slide-47-layer-img img { width: 180px !important; } }
@media (max-width:991px)  { #slider-10-slide-47-layer-img img { width: 110px !important; } }
@media (max-width:767px)  { #slider-10-slide-47-layer-img { visibility: hidden !important; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('extra_head_js') ?>
<script src="<?= base_url('studypress/js/revolution-slider/js/revolution.tools.min.js') ?>"></script>
<script src="<?= base_url('studypress/js/revolution-slider/js/rs6.min.js') ?>"></script>
<script src="<?= base_url('studypress/js/revolution-slider/extra-rev-slider1.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ========== SECTION : SLIDER ========== -->
<section id="home" class="">
  <div class="container-fluid p-0">
    <div class="row">
      <div class="col">
        <!-- Revolution Slider -->
        <p class="rs-p-wp-fix"></p>
        <rs-module-wrap id="rev_slider_1_1_wrapper" data-alias="studypress-html-revslider-1" data-source="gallery" style="background:transparent;padding:0;margin:0px auto;margin-top:0;margin-bottom:0;">
          <rs-module id="rev_slider_1_1" style="display:none;" data-version="6.1.7">
            <rs-slides>

              <!-- Slide 1 : Le Club -->
              <rs-slide data-key="rs-47" data-title="Slide 1" data-thumb="<?= base_url('assets/images/bg-slider/bg-rbcd-GF.jpg') ?>" data-anim="ei:d;eo:d;s:d;r:0;t:slotslide-horizontal;sl:d;">
                <img src="<?= base_url('assets/images/bg-slider/bg-rbcd-GF.jpg') ?>" title="bg3" width="1920" height="1280" data-parallax="off" class="rev-slidebg" data-no-retina>
                <rs-layer id="slider-10-slide-47-layer-2" data-type="text" data-rsp_ch="on"
                  data-xy="x:l,l,l,c;xo:48px,45px,50px,0;yo:226px,212px,179px,153px;"
                  data-text="w:normal;s:115,100,95,78;l:115,89,90,71;ls:1px,0px,0px,0px;fw:700;a:left,left,left,center;"
                  data-frame_1="st:300;sp:1000;sR:310;"
                  data-frame_999="o:0;st:w;sR:7690;"
                  style="z-index:11;text-transform:uppercase;">RBC Disonais
                </rs-layer>
                <rs-layer id="slider-10-slide-47-layer-3" data-type="text" data-rsp_ch="on"
                  data-xy="x:l,l,l,c;xo:59px,50px,55px,0;yo:364px,321px,291px,245px;"
                  data-text="w:normal;s:36,32,28,24;l:25,21,25,30;fw:600;a:left,left,left,center;"
                  data-padding="t:15,15,11,7;r:20,20,15,15;b:15,15,11,7;l:20,20,15,15;"
                  data-border="bos:solid;boc:#84252B;bow:0,0px,0,6px;"
                  data-frame_1="st:600;sp:1000;sR:500;"
                  data-frame_999="o:0;st:w;sR:7500;"
                  style="z-index:10;background-color:#84252B;text-transform:uppercase;">Club de Billard Carambole
                </rs-layer>
                <rs-layer id="slider-10-slide-47-layer-1" data-type="text" data-rsp_ch="on"
                  data-xy="x:l,l,l,c;xo:55px,50px,55px,0;yo:447px,399px,364px,312px;"
                  data-text="w:normal;s:20,18,18,16;l:25,24,28,24;fw:300;a:left,left,left,center;"
                  data-padding="t:10,10,9,7;r:16,16,15,15;b:10,10,9,7;l:20,20,15,15;"
                  data-border="bos:solid;boc:#84252B;bow:0,0px,0,6px;"
                  data-frame_1="st:900;sp:1000;"
                  data-frame_999="o:0;st:w;sR:8700;"
                  style="z-index:9;background-color:#84252B;">Fondé en 1951.
                </rs-layer>
                <rs-layer id="slider-10-slide-47-layer-4" data-type="text" data-rsp_ch="on"
                  data-xy="x:l,l,l,c;xo:55px,50px,55px,0;yo:539px,481px,448px,390px;"
                  data-text="w:normal;s:18,14,18,20;l:25,19,18,22;a:left,left,left,center;"
                  data-border="bos:solid;boc:#84252B;bow:0,0,0,6px;"
                  data-frame_1="st:1200;sp:1000;sR:1500;"
                  data-frame_999="o:0;st:w;sR:6500;"
                  style="z-index:8;"><a href="<?= base_url('club/histoire') ?>" class="btn btn-flat btn-theme-colored2 text-white">Découvrir le club</a>
                </rs-layer>
                <!-- Écusson du club — droite, responsive, caché sur mobile -->
                <rs-layer id="slider-10-slide-47-layer-img" data-type="image" data-rsp_ch="on"
                  data-xy="x:r,r,r,r;xo:100px,60px,40px,15px;yo:160px,160px,160px,160px;"
                  data-frame_1="st:500;sp:1500;sR:500;"
                  data-frame_999="o:0;st:w;sR:7000;"
                  style="z-index:13;">
                  <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" alt="Ecusson RBC Disonais" data-no-retina>
                </rs-layer>
              </rs-slide>
<?php /* ?>
              <!-- Slide 2 : Compétitions -->
              <rs-slide data-key="rs-48" data-title="Slide 2" data-thumb="<?= base_url('studypress/images/bg/bg2.jpg') ?>" data-anim="ei:d;eo:d;s:d;r:0;t:slotslide-horizontal;sl:d;">
                <img src="<?= base_url('studypress/images/bg/bg2.jpg') ?>" title="bg2" width="1920" height="1280" data-parallax="off" class="rev-slidebg" data-no-retina>
                <rs-layer id="slider-10-slide-48-layer-2" data-type="text" data-rsp_ch="on"
                  data-xy="x:c;yo:226px,212px,179px,153px;"
                  data-text="w:normal;s:115,100,95,78;l:115,89,90,71;ls:1px,0px,0px,0px;fw:700;a:center;"
                  data-frame_1="st:300;sp:1000;sR:310;"
                  data-frame_999="o:0;st:w;sR:7690;"
                  style="z-index:11;text-transform:uppercase;">Compétitions
                </rs-layer>
                <rs-layer id="slider-10-slide-48-layer-3" data-type="text" data-rsp_ch="on"
                  data-xy="x:c;yo:364px,323px,291px,245px;"
                  data-text="w:normal;s:36,32,28,24;l:25,21,25,30;fw:600;a:center;"
                  data-padding="t:15,15,11,7;r:30,20,15,15;b:15,15,11,7;l:30,20,15,15;"
                  data-border="bos:solid;boc:#84252B;bow:0,6px,0,6px;bor:50px,50px,50px,50px;"
                  data-frame_1="st:600;sp:1000;sR:500;"
                  data-frame_999="o:0;st:w;sR:7500;"
                  style="z-index:10;background-color:#84252B;text-transform:uppercase;">Libre &amp; 3 Bandes
                </rs-layer>
                <rs-layer id="slider-10-slide-48-layer-1" data-type="text" data-rsp_ch="on"
                  data-xy="x:c;yo:447px,400px,364px,312px;"
                  data-text="w:normal;s:20,18,18,16;l:25,28,28,26;fw:300;a:center;"
                  data-frame_1="st:900;sp:1000;"
                  data-frame_999="o:0;st:w;sR:8700;"
                  style="z-index:9;">Intercommunales et Coupes de la Province de Liège.
                </rs-layer>
                <rs-layer id="slider-10-slide-48-layer-4" data-type="text" data-rsp_ch="on"
                  data-xy="x:c;yo:539px,481px,448px,390px;"
                  data-text="w:normal;s:18,14,18,20;l:25,19,18,22;a:center;"
                  data-border="bos:solid;boc:#84252B;bow:0,0,0,6px;"
                  data-frame_1="st:1200;sp:1000;sR:1500;"
                  data-frame_999="o:0;st:w;sR:6500;"
                  style="z-index:8;"><a href="<?= base_url('saison/classements') ?>" class="btn btn-flat btn-theme-colored2 text-white">Voir les classements</a>
                </rs-layer>
              </rs-slide>

              <!-- Slide 3 : Au Tableau -->
              <rs-slide data-key="rs-49" data-title="Slide 3" data-thumb="<?= base_url('studypress/images/bg/bg1.jpg') ?>" data-anim="ei:d;eo:d;s:d;r:0;t:slotslide-horizontal;sl:d;">
                <img src="<?= base_url('studypress/images/bg/bg1.jpg') ?>" title="bg1" width="1920" height="1280" data-parallax="off" class="rev-slidebg" data-no-retina>
                <rs-layer id="slider-10-slide-49-layer-2" data-type="text" data-rsp_ch="on"
                  data-xy="x:r,r,r,c;xo:50px,45px,50px,0;yo:226px,212px,179px,153px;"
                  data-text="w:normal;s:115,100,95,78;l:115,89,90,71;ls:1px,0px,0px,0px;fw:700;a:right,right,right,center;"
                  data-frame_1="st:300;sp:1000;sR:310;"
                  data-frame_999="o:0;st:w;sR:7690;"
                  style="z-index:11;text-transform:uppercase;">Au Tableau
                </rs-layer>
                <rs-layer id="slider-10-slide-49-layer-3" data-type="text" data-rsp_ch="on"
                  data-xy="x:r,r,r,c;xo:55px,50px,55px,0;yo:364px,321px,291px,245px;"
                  data-text="w:normal;s:36,32,28,24;l:25,21,25,30;fw:600;a:right,right,right,center;"
                  data-padding="t:15,15,11,7;r:20,20,15,15;b:15,15,11,7;l:20,20,15,15;"
                  data-border="bos:solid;boc:#84252B;bow:0,6px,0,0px;"
                  data-frame_1="st:600;sp:1000;sR:500;"
                  data-frame_999="o:0;st:w;sR:7500;"
                  style="z-index:10;background-color:#84252B;text-transform:uppercase;">Matchs &amp; arbitrages
                </rs-layer>
                <rs-layer id="slider-10-slide-49-layer-1" data-type="text" data-rsp_ch="on"
                  data-xy="x:r,r,r,c;xo:55px,50px,55px,0;yo:447px,399px,364px,312px;"
                  data-text="w:normal;s:20,18,18,16;l:25,24,28,24;fw:300;a:right,right,right,center;"
                  data-frame_1="st:900;sp:1000;"
                  data-frame_999="o:0;st:w;sR:8700;"
                  style="z-index:9;">Consultez le planning hebdomadaire des rencontres.
                </rs-layer>
                <rs-layer id="slider-10-slide-49-layer-4" data-type="text" data-rsp_ch="on"
                  data-xy="x:r,r,r,c;xo:55px,50px,55px,0;yo:539px,481px,448px,390px;"
                  data-text="w:normal;s:18,14,18,20;l:25,19,18,22;a:right,right,right,center;"
                  data-border="bos:solid;boc:#84252B;bow:0,6px,0,0px;"
                  data-frame_1="st:1200;sp:1000;sR:1500;"
                  data-frame_999="o:0;st:w;sR:6500;"
                  style="z-index:8;"><a href="<?= base_url('tableau') ?>" class="btn btn-flat btn-theme-colored2 text-white">Voir le tableau</a>
                </rs-layer>
              </rs-slide>
<?php */ ?>
            </rs-slides>
            <rs-static-layers></rs-static-layers>
            <rs-progress class="rs-bottom" style="height: 5px; background: rgba(199,199,199,0.5);"></rs-progress>
          </rs-module>
          <script type="text/javascript">
            if(typeof revslider_showDoubleJqueryError === "undefined") {
              function revslider_showDoubleJqueryError(sliderID) {
                var err = "<div class='rs_error_message_box'><div class='rs_error_message_oops'>Oops...</div><div class='rs_error_message_content'>jQuery chargé après le slider. Corriger l'ordre des scripts.</div></div>";
                jQuery(sliderID).show().html(err);
              }
            }
          </script>
        </rs-module-wrap>
        <!-- FIN REVOLUTION SLIDER -->
      </div>
    </div>
  </div>
</section>

<!-- ========== SECTION : ACTUALITÉS + SIDEBAR ========== -->
<section id="actualites">
  <div class="container mt-30 mb-30 pt-30 pb-30">
    <div class="row">

      <!-- Colonne principale : Actualités -->
      <div class="col-md-9 sm-pull-none">

        <div class="mb-40">
          <div class="tm-sc tm-sc-section-title section-title">
            <div class="title-wrapper">
              <h2 class="text-uppercase line-bottom line-bottom-theme-colored1">
                Dernières <span class="text-theme-colored1">Actualités</span>
              </h2>
            </div>
          </div>
        </div>

        <div class="blog-posts">

          <?php if (empty($news)): ?>
          <div class="alert alert-info">Aucune actualité pour le moment.</div>
          <?php endif; ?>

          <?php foreach ($news as $n): ?>
          <article class="post clearfix mb-30 border-1px">
            <div class="row g-0">
              <div class="col-md-4">
                <div class="post-thumb thumb">
                  <?php if ($n->image): ?>
                  <img src="<?= base_url('uploads/news/' . $n->image) ?>"
                       alt="<?= esc($n->title) ?>"
                       class="img-responsive img-fullwidth" style="height:200px;object-fit:contain;background:#f4f4f4;">
                  <?php else: ?>
                  <div style="height:200px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-newspaper fa-3x text-muted"></i>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-8">
                <div class="entry-content p-15">
                  <h4 class="entry-title mb-5">
                    <a href="<?= base_url('actualites/' . $n->slug) ?>"><?= esc($n->title) ?></a>
                  </h4>
                  <?php if ($n->published_at): ?>
                  <div class="entry-meta mb-10">
                    <span class="text-gray-darkgray font-size-13">
                      <i class="far fa-calendar-alt me-5 text-theme-colored1"></i>
                      <?= date('d/m/Y', strtotime($n->published_at)) ?>
                    </span>
                  </div>
                  <?php endif; ?>
                  <?php
                    $_t  = strip_tags($n->content ?? '');
                    $_ex = $n->excerpt ?: (mb_strlen($_t) > 100 ? mb_substr($_t, 0, 100) . '…' : $_t);
                  ?>
                  <?php if ($_ex): ?>
                  <p class="mt-5 mb-10"><?= esc($_ex) ?></p>
                  <?php endif; ?>
                  <a href="<?= base_url('actualites/' . $n->slug) ?>"
                     class="btn btn-plain-text-with-arrow">Lire la suite</a>
                </div>
              </div>
            </div>
          </article>
          <?php endforeach; ?>

          <nav>
            <ul class="pagination">
              <li class="page-item">
                <a class="page-link" href="<?= base_url('actualites') ?>">Toutes les actualités »</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <!-- Sidebar droite -->
      <div class="col-md-3">
        <div class="sidebar sidebar-right mt-sm-30">

          <!-- Widget : Prochains matchs -->
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Prochains matchs</h4>
            <ul class="list-unstyled">
              <?php foreach ($upcoming_matches as $match): ?>
              <li class="mb-10 pb-10 border-bottom">
                <small class="text-theme-colored1 d-block">
                  <i class="far fa-calendar-alt me-5"></i><?= esc($match['date']) ?>
                </small>
                <strong><?= esc($match['home']) ?></strong> vs <strong><?= esc($match['away']) ?></strong>
              </li>
              <?php endforeach; ?>
              <?php if (empty($upcoming_matches)): ?>
              <li class="text-gray-darkgray font-size-13">Aucun match planifié.</li>
              <?php endif; ?>
            </ul>
            <a href="<?= base_url('tableau') ?>" class="btn btn-theme-colored2 btn-sm btn-block mt-10">
              Voir le tableau complet
            </a>
          </div>

          <!-- Widget : Anniversaires du mois -->
          <?php if (!empty($birthdays)): ?>
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">
              <i class="fas fa-birthday-cake me-5 text-theme-colored1"></i>Anniversaires
            </h4>
            <ul class="list-unstyled">
              <?php foreach ($birthdays as $b): ?>
              <li class="mb-5 font-size-13">
                <i class="fa fa-user me-5 text-theme-colored2"></i>
                <?= esc($b['first_name'] . ' ' . $b['last_name']) ?>
                <span class="text-gray-darkgray float-end"><?= esc($b['birthday_day_month']) ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <!-- Widget : Liens utiles -->
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Liens utiles</h4>
            <ul>
              <li><a href="<?= base_url('documents') ?>"><i class="fa fa-file-pdf me-5 text-theme-colored1"></i>Documents utiles</a></li>
              <li><a href="<?= base_url('archives') ?>"><i class="fa fa-archive me-5 text-theme-colored1"></i>Archives</a></li>
              <li><a href="<?= base_url('galerie') ?>"><i class="fa fa-images me-5 text-theme-colored1"></i>Galerie photos</a></li>
              <li><a href="<?= base_url('contact') ?>"><i class="fa fa-envelope me-5 text-theme-colored1"></i>Nous contacter</a></li>
            </ul>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<?= $this->endSection() ?>
