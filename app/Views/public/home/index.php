<?= $this->extend('public/layouts/main') ?>

<?= $this->section('extra_css') ?>
<link rel="stylesheet" type="text/css" href="<?= base_url('studypress/js/revolution-slider/css/rs6.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('studypress/js/revolution-slider/extra-rev-slider1.css') ?>">
<style>
.news-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: 4px solid #84252B;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow .2s;
}
.news-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }
.news-card-img-wrap { display: block; overflow: hidden; }
.news-card-img { width: 100%; height: 190px; object-fit: cover; display: block; transition: transform .3s; }
.news-card-img-wrap:hover .news-card-img { transform: scale(1.03); }
.news-card-img-placeholder { height: 190px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 2.5rem; }
.news-card-body { padding: 20px 22px 22px; display: flex; flex-direction: column; flex: 1; }
.news-card-date { font-size: .78rem; color: #84252B; font-weight: 600; margin-bottom: 8px; }
.news-card-title { font-size: 1rem; font-weight: 700; margin: 0 0 10px; line-height: 1.4; }
.news-card-title a { color: #222; text-decoration: none; }
.news-card-title a:hover { color: #84252B; }
.news-card-excerpt { font-size: .87rem; color: #555; line-height: 1.55; flex: 1; margin-bottom: 16px; }
.news-card-link { font-size: .85rem; font-weight: 600; color: #84252B; text-decoration: none; align-self: flex-start; }
.news-card-link:hover { text-decoration: underline; }
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

          <div class="row">
          <?php foreach ($news as $n): ?>
          <div class="col-sm-6 mb-30">
            <article class="news-card h-100">
              <a href="<?= base_url('actualites/' . $n->slug) ?>" class="news-card-img-wrap">
                <?php if ($n->image): ?>
                  <img src="<?= base_url('uploads/news/' . $n->image) ?>"
                       alt="<?= esc($n->title) ?>"
                       class="news-card-img">
                <?php else: ?>
                  <div class="news-card-img-placeholder"><i class="fas fa-newspaper"></i></div>
                <?php endif; ?>
              </a>
              <div class="news-card-body">
                <?php if ($n->published_at): ?>
                  <div class="news-card-date"><i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($n->published_at)) ?></div>
                <?php endif; ?>
                <h5 class="news-card-title">
                  <a href="<?= base_url('actualites/' . $n->slug) ?>"><?= esc($n->title) ?></a>
                </h5>
                <?php
                  $_t  = strip_tags($n->content ?? '');
                  $_ex = $n->excerpt ?: (mb_strlen($_t) > 120 ? mb_substr($_t, 0, 120) . '…' : $_t);
                ?>
                <?php if ($_ex): ?>
                  <p class="news-card-excerpt"><?= esc($_ex) ?></p>
                <?php endif; ?>
                <a href="<?= base_url('actualites/' . $n->slug) ?>" class="news-card-link">Lire la suite →</a>
              </div>
            </article>
          </div>
          <?php endforeach; ?>
          </div>

          <?= $pager->links('default', 'home_pager') ?>
          
        </div>
      </div>

      <!-- Sidebar droite -->
      <div class="col-md-3" style="position:sticky;top:90px;align-self:flex-start;">
        <div class="sidebar sidebar-right mt-sm-30 mb-4">

          <!-- Widget : Prochains matchs -->
          <div class="widget text-center">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Prochains matchs</h4>
            <a href="<?= base_url('tableau') ?>">
              <img src="<?= base_url('assets/images/Chalkboard.png') ?>"
                    alt="tableau"
                    class="img-responsive img-fullwidth" style="max-height:219px;object-fit:contain;">
            </a>
            <a href="<?= base_url('tableau') ?>" class="btn btn-theme-colored2 btn-sm btn-block mt-10">
              Voir le tableau complet
            </a>
          </div>

          <!-- Widget : Anniversaires de la semaine -->
          <div class="widget" style="border-bottom:2px solid #84252B;">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">
              <i class="fas fa-birthday-cake me-5 text-theme-colored1"></i>Anniversaires
            </h4>
            <?php if (empty($birthdays)): ?>
            <p class="font-size-16 text-dark text-center">Pas d'anniversaires cette semaine !</p>
            <?php else: ?>
            <ul class="list-unstyled">
              <?php foreach ($birthdays as $b): ?>
              <li class="d-flex align-items-center gap-2 mb-10">
                <?php if ($b['photo']): ?>
                <img src="<?= base_url('uploads/members/' . $b['photo']) ?>" alt=""
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #84252B;flex-shrink:0;">
                <?php else: ?>
                <div style="width:34px;height:34px;border-radius:50%;background:#f0f0f0;border:2px solid #84252B;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="fas fa-user" style="font-size:.75rem;color:#333333;"></i>
                </div>
                <?php endif; ?>
                <a href="<?= base_url('club/membres/' . $b['id']) ?>" class="font-size-13 flex-grow-1 text-dark" style="text-decoration:none;font-weight:600;"><?= esc($b['first_name'] . ' ' . mb_strtoupper($b['last_name'])) ?></a>
                <span class="text-dark text-right" style="font-size:.78rem;line-height:1.3;white-space:nowrap;">
                  <strong><?= (int)$b['age'] ?> ans</strong><br>
                  <span class="text-muted">ce <?= esc($b['birthday_day_month']) ?></span>
                </span>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <img src="<?= base_url('assets/images/JoyeuxAnniversaire.png') ?>"
                 alt="Joyeux Anniversaire" class="img-responsive mb-2" style="width:80%;display:block;margin:10px auto 0;">
          </div>

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

<?= $this->section('scripts') ?>
<script>
window.addEventListener('load', function () {
    if (window.location.hash === '#actualites') {
        setTimeout(function () {
            var el = document.getElementById('actualites');
            if (el) {
                var top = el.getBoundingClientRect().top + window.pageYOffset - 80;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        }, 400);
    }
});

</script>
<?= $this->endSection() ?>
