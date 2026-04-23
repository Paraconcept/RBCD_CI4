<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= base_url('admin/members/' . $member->id . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Retour fiche membre
        </a>
    </div>
    <a href="<?= base_url('admin/members/' . $member->id . '/payments/add') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Ajouter une saison
    </a>
</div>

<?php if (empty($payments)): ?>
<div class="alert alert-info">Aucune cotisation enregistrée pour ce membre.</div>
<?php else: ?>

<div class="table-responsive">
<table class="table table-bordered table-hover table-sm align-middle">
    <thead class="thead-light">
        <tr>
            <th>Saison</th>
            <th class="text-center">Cotis. RBCD<br><small class="text-muted">jan–déc</small></th>
            <th class="text-center">Cotis. FRBB<br><small class="text-muted">sep–jun</small></th>
            <th class="text-center">Forfait H1<br><small class="text-muted">jan–juin</small></th>
            <th class="text-center">Forfait H2<br><small class="text-muted">jul–déc</small></th>
            <th class="text-center" style="width:120px">Actions</th>
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

            <!-- Forfait H1 -->
            <td class="text-center">
                <?php if (!$p->forfait_h1_choice): ?>
                    <span class="text-muted small">—</span>
                <?php elseif ($p->forfait_h1_paid): ?>
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                    <?php if ($p->forfait_h1_paid_date): ?>
                        <br><small class="text-muted"><?= date('d/m/Y', strtotime($p->forfait_h1_paid_date)) ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>
                <?php endif; ?>
            </td>

            <!-- Forfait H2 -->
            <td class="text-center">
                <?php if (!$p->forfait_h2_choice): ?>
                    <span class="text-muted small">—</span>
                <?php elseif ($p->forfait_h2_paid): ?>
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                    <?php if ($p->forfait_h2_paid_date): ?>
                        <br><small class="text-muted"><?= date('d/m/Y', strtotime($p->forfait_h2_paid_date)) ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>
                <?php endif; ?>
            </td>

            <td class="text-center">
                <a href="<?= base_url('admin/members/' . $member->id . '/payments/' . $p->id . '/edit') ?>"
                   class="btn btn-xs btn-outline-primary" title="Modifier">
                    <i class="fas fa-edit"></i>
                </a>
                <button type="button" class="btn btn-xs btn-outline-danger btn-delete"
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('.btn-delete').on('click', function() {
        const id   = $(this).data('id');
        const year = $(this).data('year');
        Swal.fire({
            title: 'Supprimer la saison ' + year + '-' + (parseInt(year)+1) + ' ?',
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
