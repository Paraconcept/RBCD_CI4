<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url('admin/dashboard') ?>" class="brand-link">
        <span class="brand-text font-weight-bold">RBC Disonais</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php $adminPhoto = session()->get('admin_photo'); ?>
                <?php if ($adminPhoto): ?>
                    <img src="<?= base_url('uploads/members/' . $adminPhoto) ?>"
                         class="img-circle member-photo-thumb"
                         style="width:34px;height:34px;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-2x text-white ml-1" style="line-height:1"></i>
                <?php endif; ?>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= esc(session()->get('admin_name')) ?></a>
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

                <li class="nav-header mt-4">ADMINISTRATION DU SITE</li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/users') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/users') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Membres du Comité</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/members') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/members') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Membres</p>
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

                <?php
                $isTreasuryActive = strpos(uri_string(), 'admin/treasury') === 0;
                $isDsActive = (uri_string() === 'admin/schedule' || strpos(uri_string(), 'admin/schedule/') === 0)
                           || strpos(uri_string(), 'admin/schedule-events') === 0
                           || strpos(uri_string(), 'admin/arbitrage-stats') === 0
                           || strpos(uri_string(), 'admin/sport-results') === 0
                           || strpos(uri_string(), 'admin/cdr') === 0
                           || strpos(uri_string(), 'admin/intm') === 0;
                ?>

                <!-- ── TRÉSORERIE (section collapsible) ── -->
                <li class="nav-item has-treeview mt-4 <?= $isTreasuryActive ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link nav-section <?= $isTreasuryActive ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-euro-sign"></i>
                        <p>Trésorerie <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury') ?>"
                               class="nav-link <?= uri_string() === 'admin/treasury' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>État des paiements</p>
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
                                <p>Dépenses</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('admin/treasury/revenues') ?>"
                               class="nav-link <?= strpos(uri_string(), 'admin/treasury/revenues') === 0 ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Recettes</p>
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
