<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Accès Administration</h3>
        <div class="ml-auto">
            <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i> Donner un accès
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-bordered table-hover table-striped">
                <thead class="thead-rbcd">
                    <tr>
                        <th>Membre</th>
                        <th>Email</th>
                        <th>Rôles</th>
                        <th>Compte actif</th>
                        <th>Dernière connexion</th>
                        <th class="text-center no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                    <tr>
                        <td>
                            <?php if ($a->photo): ?>
                                <img src="<?= base_url('uploads/members/' . $a->photo) ?>"
                                     class="img-circle mr-2" style="width:28px;height:28px;object-fit:cover">
                            <?php else: ?>
                                <span class="img-circle mr-2 d-inline-flex align-items-center justify-content-center bg-secondary text-white"
                                      style="width:28px;height:28px;font-size:14px;flex-shrink:0">
                                    <i class="fas fa-user"></i>
                                </span>
                            <?php endif; ?>
                            <?= esc($a->last_name . ' ' . $a->first_name) ?>
                        </td>
                        <td><?= esc($a->email) ?></td>
                        <td>
                            <?php foreach (explode(', ', $a->roles_str ?? '') as $role): ?>
                                <?php if ($role): ?>
                                <span class="badge badge-secondary mr-1"><?= esc($role) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($a->is_active): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Mot de passe non défini</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $a->last_login ? date('d/m/Y H:i', strtotime($a->last_login)) : '<span class="text-muted">Jamais</span>' ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/users/' . $a->id . '/edit') ?>"
                               class="btn btn-xs btn-info tt-rbcd"
                               data-toggle="tooltip" data-placement="top" title="Modifier les rôles">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ((int) session()->get('member_id') !== (int) $a->id): ?>
                                <button type="button" class="btn btn-xs btn-danger btn-delete tt-rbcd"
                                    data-id="<?= $a->id ?>"
                                    data-name="<?= esc($a->first_name . ' ' . $a->last_name) ?>"
                                    data-toggle="tooltip" data-placement="top" title="Révoquer l'accès">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php else: ?>
                                <span class="badge badge-light ml-1"><i class="fas fa-user-circle"></i> Vous</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="deleteForm" method="post" action="">
    <?= csrf_field() ?>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    const tooltipOpts = {
        html:     true,
        template: '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
    };
    const table = $('#usersTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: 'no-sort' }]
    });
    $('.tt-rbcd').tooltip(tooltipOpts);
    table.on('draw.dt', function () {
        $('.tt-rbcd').tooltip('dispose').tooltip(tooltipOpts);
    });

    $(document).on('click', '.btn-delete', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Révoquer l\'accès ?',
            html: `Supprimer l'accès admin de <strong>${name}</strong> ?<br><small class="text-muted">La fiche membre reste intacte. Le mot de passe public est conservé.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Révoquer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (!result.isConfirmed) return;
            $('#deleteForm').attr('action', `<?= base_url('admin/users/') ?>${id}/delete`).submit();
        });
    });
});
</script>
<?= $this->endSection() ?>
