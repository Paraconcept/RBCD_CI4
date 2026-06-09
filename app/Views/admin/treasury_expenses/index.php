<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if ($success = session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible flash-msg">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> <?= esc($success) ?>
    </div>
<?php endif; ?>

<!-- Filtres + bouton -->
<div class="d-flex align-items-center mb-3 flex-wrap" style="gap:.5rem">
    <form method="get" class="d-flex align-items-center mr-3" id="filterForm">
        <label class="mr-2 mb-0 font-weight-bold">Année :</label>
        <select name="year" id="yearSelect" class="form-control form-control-sm mr-3" style="width:100px">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
        <label class="mr-2 mb-0 font-weight-bold">Mois :</label>
        <select name="month" id="monthSelect" class="form-control form-control-sm mr-3" style="width:140px">
            <?php for ($m = 1; $m <= $maxMonth; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
            <?php endfor; ?>
        </select>
    </form>
    <a href="<?= base_url('admin/treasury/expenses/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Nouvelle dépense
    </a>
</div>

<!-- Cartes résumé par catégorie -->
<?php if (!empty($byCategory)): ?>
<div class="row mb-2">
    <?php
    $totalCat = array_sum($byCategory);
    foreach ($categories as $key => $label):
        $montant = $byCategory[$key] ?? 0;
        if ($montant <= 0) continue;
        $pct = $totalCat > 0 ? round($montant / $totalCat * 100) : 0;
    ?>
    <div class="col-6 col-lg-3 mb-2">
        <div class="small-box bg-white border" style="min-height:0">
            <div class="inner" style="padding:12px 15px">
                <h5 class="mb-0" style="font-size:1.1rem"><?= number_format($montant, 2, ',', '.') ?> €</h5>
                <p class="mb-0 text-muted" style="font-size:.8rem"><?= esc($label) ?></p>
                <div class="progress mt-1" style="height:4px">
                    <div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Totaux : toujours sur leur propre ligne -->
<div class="row mb-3">
    <?php if (!empty($byCategory)): ?>
    <div class="col-6 mb-2">
        <div class="small-box bg-danger" style="min-height:0">
            <div class="inner" style="padding:12px 15px">
                <h5 class="mb-0 text-white" style="font-size:1.1rem"><strong><?= number_format($total, 2, ',', '.') ?> €</strong></h5>
                <p class="mb-0 text-white" style="font-size:.8rem">TOTAL <?= $monthNames[$month] ?> <?= $year ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-6 mb-2">
        <div class="small-box bg-dark" style="min-height:0">
            <div class="inner" style="padding:12px 15px">
                <h5 class="mb-0 text-white" style="font-size:1.1rem"><strong><?= number_format($totalYear, 2, ',', '.') ?> €</strong></h5>
                <p class="mb-0 text-white" style="font-size:.8rem">TOTAL ANNÉE <?= $year ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tableau -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-arrow-down mr-2"></i>Dépenses — <?= $monthNames[$month] ?> <?= $year ?></h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="p-3 text-muted"><i class="fas fa-info-circle mr-1"></i>Aucune dépense enregistrée pour <?= $monthNames[$month] ?> <?= $year ?>.</div>
        <?php else: ?>
        <table class="table table-sm table-hover mb-0" id="expensesTable">
            <thead class="thead-rbcd">
                <tr>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Description</th>
                    <th class="text-right">Montant</th>
                    <th>Paiement</th>
                    <th>Encodé par</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($r->expense_date)) ?></td>
                    <td><span class="badge badge-secondary"><?= esc($categories[$r->category] ?? $r->category) ?></span></td>
                    <td>
                        <?= esc($r->description) ?>
                        <?php if ($r->notes): ?>
                            <br><small class="text-muted"><?= esc($r->notes) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right text-nowrap font-weight-bold text-danger">
                        <?= number_format($r->amount, 2, ',', '.') ?> €
                    </td>
                    <td><span class="badge badge-light border"><?= esc($paymentMethods[$r->payment_method] ?? $r->payment_method) ?></span></td>
                    <td class="text-nowrap">
                        <?php if ($r->last_name): ?>
                            <?= esc($r->last_name) ?> <?= esc(member_initials($r->first_name)) ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="<?= base_url('admin/treasury/expenses/' . $r->id . '/edit') ?>"
                           class="btn btn-xs btn-warning mr-1" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-delete"
                                data-id="<?= $r->id ?>" data-desc="<?= esc($r->description) ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="del-<?= $r->id ?>" method="POST"
                              action="<?= base_url('admin/treasury/expenses/' . $r->id . '/delete') ?>" class="d-none">
                            <?= csrf_field() ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="tfoot-total">
                <tr>
                    <td colspan="3" class="font-weight-bold">TOTAL <?= $monthNames[$month] ?></td>
                    <td class="text-right font-weight-bold text-danger"><?= number_format($total, 2, ',', '.') ?> €</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    const currentYear  = <?= $currentYear ?>;
    const currentMonth = <?= $currentMonth ?>;
    const allMonths    = <?= json_encode(array_slice($monthNames, 1, null, true)) ?>;

    $('#yearSelect').on('change', function () {
        const selectedYear = parseInt($(this).val());
        const max = (selectedYear >= currentYear) ? currentMonth : 12;

        const $ms = $('#monthSelect').empty();
        for (let m = 1; m <= max; m++) {
            $ms.append(new Option(allMonths[m], m));
        }
        $ms.val(max);
        $('#filterForm').submit();
    });

    $('#monthSelect').on('change', function () {
        $('#filterForm').submit();
    });

    <?php if (!empty($rows)): ?>
    $('#expensesTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        columnDefs: [{
            targets: 0,
            render: function(data, type) {
                if (type === 'sort' || type === 'type') {
                    var p = data.split('/');
                    return p.length === 3 ? p[2] + p[1] + p[0] : data;
                }
                return data;
            }
        }]
    });
    <?php endif; ?>

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const desc = this.dataset.desc;
            Swal.fire({
                title: 'Supprimer cette dépense ?',
                html: `<strong>${desc}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#84252B',
                cancelButtonText: 'Annuler',
                confirmButtonText: 'Oui, supprimer',
            }).then(r => { if (r.isConfirmed) document.getElementById('del-' + id).submit(); });
        });
    });
});
</script>
<?= $this->endSection() ?>
