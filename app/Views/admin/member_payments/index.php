<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title">
            <i class="fas fa-euro-sign mr-2"></i>
            Cotisations — <?= esc($member->first_name . ' ' . $member->last_name) ?>
        </h3>
        <div class="ml-auto d-flex gap-2">
            <a href="<?= base_url('admin/members/' . $member->id . '/edit') ?>" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Retour fiche membre
            </a>
            <a href="<?= base_url('admin/members/' . $member->id . '/payments/add') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Ajouter une saison
            </a>
        </div>
    </div>
    <div class="card-body">

        <?php if (empty($payments)): ?>
        <div class="alert alert-info mb-0">Aucune cotisation enregistrée pour ce membre.</div>
        <?php else: ?>
        
        <div class="table-responsive">
            <table id="paymentsTable" class="table table-bordered table-hover table-striped">
                <thead class="thead-rbcd">
                    <tr>
                        <th>Saison</th>
                        <th class="text-center">Cotis. RBCD<br><small>jan–déc</small></th>
                        <th class="text-center">Cotis. FRBB<br><small>sep–juin</small></th>
                        <th class="text-center">Forfait F1<br><small>jan–juin</small></th>
                        <th class="text-center">Forfait F2<br><small>juil–déc</small></th>
                        <th class="text-center no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><strong><?= $p->year . '-' . ($p->year + 1) ?></strong></td>

                        <!-- RBCD -->
                        <td class="text-center">
                            <?php if ($p->rbcd_paid): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                <?php if ($p->rbcd_paid_date): ?>
                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($p->rbcd_paid_date)) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Non payé</span>
                            <?php endif; ?>
                        </td>

                        <!-- FRBB -->
                        <td class="text-center">
                            <?php if ($p->frbb_paid): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                <?php if ($p->frbb_paid_date): ?>
                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($p->frbb_paid_date)) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Non payé</span>
                            <?php endif; ?>
                        </td>

                        <!-- Forfait F1 -->
                        <td class="text-center">
                            <?php if (!$p->forfait_f1_choice): ?>
                                <span class="text-muted">—</span>
                            <?php elseif ($p->forfait_f1_paid): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                <?php if ($p->forfait_f1_paid_date): ?>
                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($p->forfait_f1_paid_date)) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>
                            <?php endif; ?>
                        </td>

                        <!-- Forfait F2 -->
                        <td class="text-center">
                            <?php if (!$p->forfait_f2_choice): ?>
                                <span class="text-muted">—</span>
                            <?php elseif ($p->forfait_f2_paid): ?>
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                <?php if ($p->forfait_f2_paid_date): ?>
                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($p->forfait_f2_paid_date)) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-center">
                            <a href="<?= base_url('admin/members/' . $member->id . '/payments/' . $p->id . '/edit') ?>"
                            class="btn btn-xs btn-info" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-xs btn-danger btn-delete"
                                    data-id="<?= $p->id ?>" data-year="<?= $p->year ?>" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            <form id="del-<?= $p->id ?>"
                                action="<?= base_url('admin/members/' . $member->id . '/payments/' . $p->id . '/delete') ?>"
                                method="post" class="d-none">
                                <?= csrf_field() ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#paymentsTable').DataTable({
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [1, 2, 3, 4, 5] }
        ]
    });

    $(document).on('click', '.btn-delete', function() {
        const id   = $(this).data('id');
        const year = $(this).data('year');
        Swal.fire({
            title: 'Supprimer la saison ' + year + '-' + (parseInt(year) + 1) + ' ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#84252B',
        }).then(result => {
            if (result.isConfirmed) $('#del-' + id).submit();
        });
    });
});
</script>
<?= $this->endSection() ?>
