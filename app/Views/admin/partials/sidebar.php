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
                <small class="text-white-50"><?= esc(implode(', ', session()->get('admin_roles') ?? [])) ?></small>
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

                <li class="nav-header">GESTION</li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/members') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/members') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Membres</p>
                    </a>
                </li>

                <li class="nav-header">ADMINISTRATION</li>

                <li class="nav-item">
                    <a href="<?= base_url('admin/users') ?>" class="nav-link <?= (strpos(uri_string(), 'admin/users') === 0) ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Utilisateurs admin</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
