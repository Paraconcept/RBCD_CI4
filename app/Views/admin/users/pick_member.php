<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Choisir un membre</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Sélectionnez le membre à qui vous souhaitez créer un compte admin.</p>

        <?php if (empty($members)): ?>
            <div class="alert alert-info mb-0">Tous les membres actifs ont déjà un compte admin.</div>
        <?php else: ?>

        <table id="pickTable" class="table table-bordered table-hover table-striped">
            <thead class="thead-rbcd">
                <tr>
                    <th style="width:40px"></th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th class="text-center no-sort" style="width:120px">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td class="text-center align-middle p-1">
                        <?php if ($m->photo): ?>
                            <img src="<?= base_url('uploads/members/' . $m->photo) ?>"
                                 class="img-circle member-photo-thumb"
                                 style="width:32px;height:32px;object-fit:cover;">
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-user-circle fa-lg"></i></span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle"><strong><?= esc($m->last_name) ?></strong> <?= esc($m->first_name) ?></td>
                    <td class="align-middle"><?= $m->email ? esc($m->email) : '<span class="text-muted">—</span>' ?></td>
                    <td class="text-center align-middle">
                        <a href="<?= base_url('admin/users/from-member/' . $m->id) ?>"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-user-shield mr-1"></i> Choisir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('#pickTable').DataTable({
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [0, 3] }]
    });
});
</script>
<?= $this->endSection() ?>
