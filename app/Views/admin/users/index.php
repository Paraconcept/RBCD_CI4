<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Membres du Comité</h3>
        <div class="ml-auto">
            <a href="<?= base_url('admin/users/pick-member') ?>" class="btn btn-primary btn-sm mr-1">
                <i class="fas fa-user-plus mr-1"></i> Membre du comité
            </a>
            <a href="<?= base_url('admin/users/create') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-globe mr-1"></i> Compte externe
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-bordered table-hover table-striped">
                <thead class="thead-rbcd">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôles</th>
                        <th class="text-center">Statut</th>
                        <th>Dernière connexion</th>
                        <th class="text-center no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <?php $uRoles = $rolesMap[$u->id] ?? []; ?>
                    <tr id="row-<?= $u->id ?>">
                        <td><?= esc($u->last_name . ' ' . $u->first_name) ?></td>
                        <td><?= esc($u->email) ?></td>
                        <td>
                            <?php foreach ($uRoles as $role): ?>
                                <span class="badge badge-secondary mr-1"><?= esc($role) ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($uRoles)): ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span id="badge-<?= $u->id ?>" class="badge <?= $u->is_active ? 'badge-success' : 'badge-danger' ?>">
                                <?= $u->is_active ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td><?= $u->last_login ? date('d/m/Y H:i', strtotime($u->last_login)) : '<span class="text-muted">Jamais</span>' ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/users/' . $u->id . '/edit') ?>" class="btn btn-xs btn-info tt-rbcd"
                               data-toggle="tooltip" data-placement="top"
                               title="Modifier<br>le(s) mandat(s) de<br> <?= esc($u->last_name . ' ' . $u->first_name) ?>">
                                <i class="fas fa-edit"></i>
                            </a>

                            <?php if (session()->get('admin_id') != $u->id): ?>
                                <button type="button"
                                    class="btn btn-xs <?= $u->is_active ? 'btn-warning' : 'btn-success' ?> btn-toggle tt-rbcd"
                                    data-id="<?= $u->id ?>"
                                    data-active="<?= $u->is_active ?>"
                                    data-name="<?= esc($u->last_name . ' ' . $u->first_name) ?>"
                                    data-toggle="tooltip" data-placement="top"
                                    title="<?= $u->is_active ? 'Désactiver' : 'Activer' ?> <br> <?= esc($u->last_name . ' ' . $u->first_name) ?> <br>du comité">
                                    <i class="fas fa-power-off"></i>
                                </button>

                                <button type="button" class="btn btn-xs btn-danger btn-delete tt-rbcd"
                                    data-id="<?= $u->id ?>"
                                    data-name="<?= esc($u->first_name . ' ' . $u->last_name) ?>"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Supprimer <br> <?= esc($u->last_name . ' ' . $u->first_name) ?>">
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
    $('#usersTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: 'no-sort' }]
    });

    $('.tt-rbcd').tooltip({
        html:     true,
        template: '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
    });

    $(document).on('click', '.btn-toggle', function() {
        const id     = $(this).data('id');
        const active = $(this).data('active');
        const name   = $(this).data('name');
        Swal.fire({
            title: 'Confirmer',
            html: `Voulez-vous ${active ? 'désactiver' : 'activer'} l'accès comité de<br> <strong>${name}</strong> ?<br><small class="text-muted">La fiche membre reste inchangée.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui',
            cancelButtonText: 'Annuler',
            confirmButtonColor: active ? '#e5a54b' : '#28a745',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('admin/users/') ?>${id}/toggle`, {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }).done(function(res) {
                if (res.success) {
                    const badge = $(`#badge-${id}`);
                    const btn   = $(`[data-id="${id}"].btn-toggle`);
                    if (res.is_active) {
                        badge.removeClass('badge-danger').addClass('badge-success').text('Actif');
                        btn.removeClass('btn-success').addClass('btn-warning').attr('data-original-title','Désactiver ' + name).data('active',1).html('<i class="fas fa-power-off"></i>');
                    } else {
                        badge.removeClass('badge-success').addClass('badge-danger').text('Inactif');
                        btn.removeClass('btn-warning').addClass('btn-success').attr('data-original-title','Activer ' + name).data('active',0).html('<i class="fas fa-power-off"></i>');
                    }
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: res.message });
                }
            });
        });
    });

    $(document).on('click', '.btn-delete', function() {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: 'Supprimer ?',
            html: `Supprimer le compte comité de <strong>${name}</strong> ?<br><small class="text-muted">La fiche membre reste intacte. Cette action est irréversible.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
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
