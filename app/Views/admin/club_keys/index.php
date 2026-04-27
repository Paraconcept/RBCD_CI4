<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-key mr-2"></i>Clés du club</h3>
        <div class="ml-auto">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addKeyModal">
                <i class="fas fa-plus mr-1"></i> Ajouter une clé
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="keysTable" class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-rbcd">
                    <tr>
                        <th>N° badge</th>
                        <th class="text-center">Statut</th>
                        <th>Titulaire</th>
                        <th class="text-center">Attribuée le</th>
                        <th class="text-center">Retournée le</th>
                        <th>Notes</th>
                        <th class="text-center no-sort" style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keys as $k): ?>
                    <?php $assigned = $k->member_id !== null; ?>
                    <tr>
                        <td><strong><?= $k->badge_number ? esc($k->badge_number) : '<span class="text-muted">—</span>' ?></strong></td>
                        <td class="text-center">
                            <?php if ($assigned): ?>
                                <span class="badge badge-warning text-dark"><i class="fas fa-user mr-1"></i>Attribuée</span>
                            <?php else: ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $assigned ? esc($k->holder_name) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center"><?= $k->given_date ? date('d/m/Y', strtotime($k->given_date)) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center"><?= $k->returned_date ? date('d/m/Y', strtotime($k->returned_date)) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= $k->notes ? esc($k->notes) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center">
                            <?php if (!$assigned): ?>
                                <button type="button" class="btn btn-xs btn-primary btn-assign tt-rbcd"
                                        data-id="<?= $k->id ?>"
                                        data-badge="<?= esc($k->badge_number ?? '') ?>"
                                        data-toggle="tooltip" data-placement="top"
                                        title="Attribuer la clé <?= $k->badge_number ? '#' . esc($k->badge_number) : 'sans numéro' ?>">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-xs btn-warning btn-return tt-rbcd"
                                        data-id="<?= $k->id ?>"
                                        data-badge="<?= esc($k->badge_number ?? '') ?>"
                                        data-holder="<?= esc($k->holder_name) ?>"
                                        data-toggle="tooltip" data-placement="top"
                                        title="Retourner la clé de <?= esc($k->holder_name) ?>">
                                    <i class="fas fa-undo"></i>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-xs btn-danger btn-delete tt-rbcd"
                                    data-id="<?= $k->id ?>"
                                    data-badge="<?= esc($k->badge_number ?? 'sans numéro') ?>"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Supprimer la clé <?= $k->badge_number ? '#' . esc($k->badge_number) : 'sans numéro' ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Formulaires POST cachés -->
<form id="returnForm" method="post" action=""><?= csrf_field() ?></form>
<form id="deleteForm" method="post" action=""><?= csrf_field() ?></form>

<!-- Modal : Ajouter une clé -->
<div class="modal fade" id="addKeyModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="post" action="<?= base_url('admin/club-keys') ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Nouvelle clé</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>N° de badge</label>
                        <input type="text" name="badge_number" class="form-control" placeholder="ex : 042">
                    </div>
                    <div class="form-group mb-0">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal : Attribuer une clé -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="assignForm" method="post" action="">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Attribuer la clé <span id="assignBadgeLabel"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Membre <span class="text-danger">*</span></label>
                        <select name="member_id" id="assignMemberSelect" class="form-control select2-assign" style="width:100%" required>
                            <option value="">— Choisir un membre —</option>
                            <?php foreach ($activeMembers as $m): ?>
                                <option value="<?= $m->id ?>"><?= esc($m->last_name . ' ' . $m->first_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Date d'attribution</label>
                        <input type="date" name="given_date" class="form-control" value="<?= date('Y-m-d') ?>" style="max-width:180px">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Attribuer</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$('.tt-rbcd').tooltip({
    html:     true,
    template: '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
});

$(function () {
    $('#keysTable').DataTable({
        order: [[2, 'asc']],
        columnDefs: [{ orderable: false, targets: 'no-sort' }],
        pageLength: 25,
    });

    $('.select2-assign').select2({ theme: 'bootstrap4', dropdownParent: $('#assignModal') });

    // Attribuer
    $(document).on('click', '.btn-assign', function () {
        const id    = $(this).data('id');
        const badge = $(this).data('badge');
        $('#assignBadgeLabel').text(badge ? '#' + badge : '');
        $('#assignForm').attr('action', `<?= base_url('admin/club-keys/') ?>${id}/assign`);
        $('#assignMemberSelect').val('').trigger('change');
        $('#assignModal').modal('show');
    });

    // Retourner
    $(document).on('click', '.btn-return', function () {
        const id     = $(this).data('id');
        const badge  = $(this).data('badge') || 'sans numéro';
        const holder = $(this).data('holder');
        Swal.fire({
            title: 'Retourner la clé ?',
            html: `Clé <strong>${badge}</strong> actuellement chez <strong>${holder}</strong>.<br>Elle repassera en stock disponible.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, retourner',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#84252B',
        }).then(result => {
            if (result.isConfirmed) {
                $('#returnForm').attr('action', `<?= base_url('admin/club-keys/') ?>${id}/return`).submit();
            }
        });
    });

    // Supprimer
    $(document).on('click', '.btn-delete', function () {
        const id    = $(this).data('id');
        const badge = $(this).data('badge');
        Swal.fire({
            title: 'Supprimer la clé ?',
            html: `Confirmer la suppression de la clé <strong>${badge}</strong> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                $('#deleteForm').attr('action', `<?= base_url('admin/club-keys/') ?>${id}/delete`).submit();
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
