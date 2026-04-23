<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Membres</h3>
        <div class="ml-auto">
            <a href="<?= base_url('admin/members/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Nouveau membre
            </a>
        </div>
    </div>
    <div class="card-body">
        <table id="membersTable" class="table table-bordered table-hover table-striped">
            <thead class="thead-rbcd">
                <tr>
                    <th style="width:40px"></th>
                    <th>Nom</th>
                    <th class="text-center" style="width:50px">G.</th>
                    <th>Licence FRBB</th>
                    <th class="text-center">Comité</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center no-sort">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr id="row-<?= $m->id ?>">
                    <!-- Photo -->
                    <td class="text-center p-1">
                        <?php if ($m->photo): ?>
                            <img src="<?= base_url('uploads/members/' . $m->photo) ?>"
                                 alt="" class="img-circle" style="width:32px;height:32px;object-fit:cover;">
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-user-circle fa-lg"></i></span>
                        <?php endif; ?>
                    </td>
                    <!-- Nom -->
                    <td>
                        <strong><?= esc($m->last_name) ?></strong> <?= esc($m->first_name) ?>
                        <?php if ($m->is_federated && $m->frbb_license): ?>
                        <?php endif; ?>
                    </td>
                    <!-- Genre -->
                    <td class="text-center">
                        <span class="badge <?= $m->gender === 'F' ? 'badge-pink' : 'badge-info' ?>">
                            <?= $m->gender ?>
                        </span>
                    </td>
                    <!-- Licence -->
                    <td><?= $m->frbb_license ? esc($m->frbb_license) : '<span class="text-muted">—</span>' ?></td>
                    <!-- Comité -->
                    <td class="text-center">
                        <?php if ($m->is_committee ?? false): ?>
                            <i class="fas fa-star text-warning" title="Membre du comité"></i>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <!-- Statut -->
                    <td class="text-center">
                        <span id="badge-<?= $m->id ?>" class="badge <?= $m->is_active ? 'badge-success' : 'badge-danger' ?>">
                            <?= $m->is_active ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <!-- Actions -->
                    <td class="text-center">
                        <a href="<?= base_url('admin/members/' . $m->id . '/payments') ?>" class="btn btn-xs btn-outline-secondary" title="Cotisations">
                            <i class="fas fa-euro-sign"></i>
                        </a>
                        <a href="<?= base_url('admin/members/' . $m->id . '/edit') ?>" class="btn btn-xs btn-info" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button"
                            class="btn btn-xs <?= $m->is_active ? 'btn-warning' : 'btn-success' ?> btn-toggle"
                            data-id="<?= $m->id ?>" data-active="<?= $m->is_active ?>"
                            title="<?= $m->is_active ? 'Désactiver' : 'Activer' ?>">
                            <i class="fas <?= $m->is_active ? 'fa-ban' : 'fa-check' ?>"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-danger btn-delete"
                            data-id="<?= $m->id ?>"
                            data-name="<?= esc($m->first_name . ' ' . $m->last_name) ?>"
                            title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
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

<?= $this->section('styles') ?>
<style>
.badge-pink { background-color: #e83e8c; color: #fff; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#membersTable').DataTable({
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ]
    });

    $(document).on('click', '.btn-toggle', function() {
        const id     = $(this).data('id');
        const active = $(this).data('active');
        Swal.fire({
            title: 'Confirmer',
            text: `Voulez-vous ${active ? 'désactiver' : 'activer'} ce membre ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui',
            cancelButtonText: 'Annuler',
            confirmButtonColor: active ? '#e5a54b' : '#28a745',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(`<?= base_url('admin/members/') ?>${id}/toggle`, {
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
            $('#deleteForm').attr('action', `<?= base_url('admin/members/') ?>${id}/delete`).submit();
        });
    });
});
</script>
<?= $this->endSection() ?>
