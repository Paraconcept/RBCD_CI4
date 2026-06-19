<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-key mr-2"></i>Statut de connexion des membres</h3>
        <div class="ml-auto">
            <a href="<?= base_url('admin/members') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
            </a>
        </div>
    </div>
    <div class="card-body">

        <?php
        $total       = count($rows);
        $connected   = 0;
        $invited     = 0;
        $noAccount   = 0;
        foreach ($rows as $r) {
            if ($r->password_changed_at)          $connected++;
            elseif ($r->login_active !== null)    $invited++;
            else                                   $noAccount++;
        }
        ?>
        <div class="row mb-3">
            <div class="col-sm-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mot de passe changé</span>
                        <span class="info-box-number"><?= $connected ?> / <?= $total ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Invitation envoyée</span>
                        <span class="info-box-number"><?= $invited ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="info-box bg-secondary">
                    <span class="info-box-icon"><i class="fas fa-user-slash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sans compte</span>
                        <span class="info-box-number"><?= $noAccount ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="loginStatusTable" class="table table-bordered table-hover table-striped">
                <thead class="thead-rbcd">
                    <tr>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th class="text-center">Statut connexion</th>
                        <th class="text-center">Mot de passe changé le</th>
                        <th class="text-center">Dernière connexion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?= esc($r->last_name) ?></strong> <?= esc($r->first_name) ?>
                            <?php if (!$r->member_active): ?>
                                <span class="badge badge-secondary ml-1">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $r->email ? esc($r->email) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center">
                            <?php if ($r->password_changed_at): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Connecté</span>
                            <?php elseif ($r->login_active !== null): ?>
                                <span class="badge badge-warning"><i class="fas fa-envelope mr-1"></i>Invitation envoyée</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><i class="fas fa-minus mr-1"></i>Sans compte</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r->password_changed_at): ?>
                                <?= date('d/m/Y H:i', strtotime($r->password_changed_at)) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r->last_login): ?>
                                <?= date('d/m/Y H:i', strtotime($r->last_login)) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#loginStatusTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [] }
        ]
    });
});
</script>
<?= $this->endSection() ?>
