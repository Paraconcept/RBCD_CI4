<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url('admin/dashboard') ?>" class="brand-link">
        <span class="brand-text font-weight-bold">RBC Disonais</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php $memberPhoto = session()->get('member_photo'); ?>
                <?php if ($memberPhoto): ?>
                    <img src="<?= base_url('uploads/members/' . $memberPhoto) ?>"
                         class="img-circle member-photo-thumb"
                         style="width:34px;height:34px;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-2x text-white ml-1" style="line-height:1"></i>
                <?php endif; ?>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= esc(session()->get('member_name')) ?></a>
                <?php foreach (session()->get('admin_roles') ?? [] as $role): ?>
                    <small class="d-block text-white-50"><?= esc($role) ?></small>
                <?php endforeach; ?>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                <li class="nav-item">
                    <a href="<?= base_url('admin/dashboard') ?>" class="nav-link <?= (uri_string() === 'admin/dashboard') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Tableau de bord</p>
                    </a>
                </li>

                <!-- ── ADMINISTRATION DU SITE (section collapsible) ── -->
                <?php
                $isSiteActive = strpos(uri_string(), 'admin/users') === 0
                             || (strpos(uri_string(), 'admin/members') === 0 && strpos(uri_string(), '/payments') === false)
                             || strpos(uri_string(), 'admin/documents') === 0
                             || strpos(uri_string(), 'admin/journal') === 0
                             || strpos(uri_string(), 'admin/news') === 0
                             || strpos(uri_string(), 'admin/galleries') === 0
                             || strpos(uri_string(), 'admin/settings') === 0;
                ?>
                <li class="nav-item has-treeview mt-4 <?= $isSiteActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link nav-section <?= $isSiteActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-globe"></i>
                        <p class="nav-section-long">Administration du site <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/users') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/users') === 0) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Membres du Comité</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/members') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/members') === 0 && strpos(uri_string(), '/payments') === false) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Membres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/documents') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/documents') === 0) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-file-pdf"></i>
                                <p>Documents PDF</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/journal') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/journal') === 0) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-newspaper"></i>
                                <p>Journal "Partie Libre"</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/news') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/news') === 0) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-bullhorn"></i>
                                <p>Actualités <em>(News)</em></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/galleries') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/galleries') === 0) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-images"></i>
                                <p>Galeries photos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/settings') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/settings') === 0) ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Paramètres</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php
                $isTreasuryActive = strpos(uri_string(), 'admin/treasury') === 0
                                 || strpos(uri_string(), 'admin/members/') !== false && strpos(uri_string(), '/payments') !== false;
                $isDsActive = (uri_string() === 'admin/schedule' || strpos(uri_string(), 'admin/schedule/') === 0)
                           || strpos(uri_string(), 'admin/schedule-events') === 0
                           || strpos(uri_string(), 'admin/arbitrage-stats') === 0
                           || strpos(uri_string(), 'admin/sport-results') === 0
                           || strpos(uri_string(), 'admin/cdr') === 0
                           || strpos(uri_string(), 'admin/intm') === 0;
                $isSbActive = strpos(uri_string(), 'admin/scoreboard') === 0;
                $sbUri = uri_string();
                ?>

                <!-- ── DIRECTION SPORTIVE (section collapsible) ── -->
                <li class="nav-item has-treeview mt-4 <?= $isDsActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link nav-section <?= $isDsActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-running"></i>
                        <p>Direction Sportive <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/schedule') ?>"
                               class="nav-link <?= (uri_string() === 'admin/schedule' || strpos(uri_string(), 'admin/schedule/') === 0) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tableau des rencontres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/schedule-events') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/schedule-events') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Événements au tableau</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/arbitrage-stats') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/arbitrage-stats') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stats d'arbitrage</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/sport-results') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/sport-results') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Résultats sportifs</p>
                            </a>
                        </li>
                        <!-- Coupe des Régions — niveau 2 -->
                        <li class="nav-item has-treeview <?= strpos(uri_string(), 'admin/cdr') === 0 ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= strpos(uri_string(), 'admin/cdr') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Coupe des Régions <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('admin/cdr') ?>"
                                       class="nav-link <?= strpos(uri_string(), 'admin/cdr') === 0 ? 'active' : '' ?>">
                                        <i class="far fa-dot-circle nav-icon"></i>
                                        <p>Équipes</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- I.N.T.M. — niveau 2 -->
                        <li class="nav-item has-treeview <?= strpos(uri_string(), 'admin/intm') === 0 ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= strpos(uri_string(), 'admin/intm') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>I.N.T.M. <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= base_url('admin/intm') ?>"
                                       class="nav-link <?= strpos(uri_string(), 'admin/intm') === 0 ? 'active' : '' ?>">
                                        <i class="far fa-dot-circle nav-icon"></i>
                                        <p>Équipes</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- ── MARQUOIRS (section collapsible) ── -->
                <li class="nav-item has-treeview mt-4 <?= $isSbActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link nav-section <?= $isSbActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-table"></i>
                        <p>Marquoirs <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard') ?>"
                               class="nav-link <?= $isSbActive && !preg_match('#^admin/scoreboard/(tables|players|settings|teams|finals)#', $sbUri) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rencontres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard/teams') ?>"
                               class="nav-link <?= strpos($sbUri, 'admin/scoreboard/teams') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rencontres d'équipe</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard/finals') ?>"
                               class="nav-link <?= strpos($sbUri, 'admin/scoreboard/finals') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Finales et poules</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard/tables') ?>"
                               class="nav-link <?= strpos($sbUri, 'admin/scoreboard/tables') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Billards</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard/players') ?>"
                               class="nav-link <?= strpos($sbUri, 'admin/scoreboard/players') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Joueurs externes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard/settings') ?>"
                               class="nav-link <?= strpos($sbUri, 'admin/scoreboard/settings') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Paramètres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/scoreboard/howto') ?>" target="_blank" class="nav-link">
                                <i class="far fa-file-pdf nav-icon"></i>
                                <p>How to</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ── TRÉSORERIE (section collapsible) ── -->
                <li class="nav-item has-treeview mt-4 <?= $isTreasuryActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link nav-section <?= $isTreasuryActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-euro-sign"></i>
                        <p>Trésorerie <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury') ?>"
                               class="nav-link <?= (uri_string() === 'admin/treasury' || (strpos(uri_string(), 'admin/members/') !== false && strpos(uri_string(), '/payments') !== false)) ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cotisations &amp; Forfaits</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury/envelopes') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/treasury/envelopes') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Enveloppes de caisse</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury/expenses') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/treasury/expenses') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dépenses &nbsp; ( <i class="fas fa-minus"></i> )</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury/revenues') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/treasury/revenues') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Recettes &nbsp; ( <i class="fas fa-plus"></i> )</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury/bilan') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/treasury/bilan') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Bilan financier</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury/settings') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/treasury/settings') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Paramètres financiers</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ── CAISSE DU BAR (section collapsible) ── -->
                <?php $isBarActive = strpos(uri_string(), 'admin/caisse') === 0; ?>
                <li class="nav-item has-treeview mt-4 <?= $isBarActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link nav-section <?= $isBarActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cash-register"></i>
                        <p>Caisse du bar <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/services') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/caisse/services') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Services &amp; enveloppes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/catalogue') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/caisse/catalogue') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Catalogue</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/clients') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/caisse/clients') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Clients</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/stocks') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/caisse/stocks') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stocks</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/parametres') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/caisse/parametres') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Paramètres</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('caisse') ?>" target="_blank" class="nav-link">
                                <i class="fas fa-external-link-alt nav-icon"></i>
                                <p>Ouvrir la caisse</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/howto') ?>" target="_blank" class="nav-link">
                                <i class="far fa-file-pdf nav-icon"></i>
                                <p>How to admin</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/caisse/howto-caisse') ?>" target="_blank" class="nav-link">
                                <i class="far fa-file-pdf nav-icon"></i>
                                <p>How to caisse</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header mt-4">GESTION DU CLUB</li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/opening-hours') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/opening-hours') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clock"></i>
                        <p>Heures d'ouverture</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/school') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/school') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>École de billard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/club-keys') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/club-keys') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-key"></i>
                        <p>Clés du club</p>
                    </a>
                </li>

                <li>&nbsp;</li>
                <li>&nbsp;</li>
                <li>&nbsp;</li>

            </ul>
        </nav>
    </div>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.querySelector('.main-sidebar .sidebar');
    var active  = sidebar && sidebar.querySelector('.nav-link.active');
    if (sidebar && active) {
        var top = active.getBoundingClientRect().top - sidebar.getBoundingClientRect().top;
        sidebar.scrollTop = sidebar.scrollTop + top - sidebar.clientHeight / 2;
    }
});
</script>
