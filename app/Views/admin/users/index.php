<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Utilisateurs admin</h3>
        <div class="ml-auto">
            <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Nouvel utilisateur
            </a>
        </div>
    </div>
    <div class="card-body">
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
                        <a href="<?= base_url('admin/users/' . $u->id . '/edit') ?>" class="btn btn-xs btn-info" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>

                        <?php if (session()->get('admin_id') != $u->id): ?>
                            <button type="button"
                                class="btn btn-xs <?= $u->is_active ? 'btn-warning' : 'btn-success' ?> btn-toggle"
                                data-id="<?= $u->id ?>"
                                data-active="<?= $u->is_active ?>"
                                title="<?= $u->is_active ? 'Désactiver' : 'Activer' ?>">
                                <i class="fas <?= $u->is_active ? 'fa-ban' : 'fa-check' ?>"></i>
                            </button>

                            <button type="button" class="btn btn-xs btn-danger btn-delete"
                                data-id="<?= $u->id ?>"
                                data-name="<?= esc($u->first_name . ' ' . $u->last_name) ?>"
                                title="Supprimer">
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

    $(document).on('click', '.btn-toggle', function() {
        const id     = $(this).data('id');
        const active = $(this).data('active');
        Swal.fire({
            title: 'Confirmer',
            text: `Voulez-vous ${active ? 'désactiver' : 'activer'} cet utilisateur ?`,
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
                        btn.removeClass('btn-success').addClass('btn-warning').attr('title','Désactiver').data('active',1).html('<i class="fas fa-ban"></i>');
                    } else {
                        badge.removeClass('badge-success').addClass('badge-danger').text('Inactif');
                        btn.removeClass('btn-warning').addClass('btn-success').attr('title','Activer').data('active',0).html('<i class="fas fa-check"></i>');
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
            html: `Confirmer la suppression de <strong>${name}</strong> ?<br><small class="text-muted">Cette action est irréversible.</small>`,
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
