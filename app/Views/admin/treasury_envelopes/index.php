<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $catLabels = ['bar' => 'Bar / Buvette', 'divers' => 'Divers']; ?>

<div class="d-flex align-items-center mb-3">
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
    <a href="<?= base_url('admin/treasury/envelopes/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Nouvelle enveloppe
    </a>
</div>

<?php if (empty($byMonth)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i> Aucune enveloppe enregistrée pour <?= $year ?>.
    </div>
<?php else: ?>
    <?php foreach ($byMonth as $month): ?>
    <?php $ecartMois = $month['found'] - $month['calculated']; ?>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-alt mr-2"></i><?= $month['label'] ?>
            </h3>
            <div class="card-tools">
                <span class="badge badge-light border mr-1">
                    Calculé : <strong><?= number_format($month['calculated'], 2, ',', '.') ?> €</strong>
                </span>
                <span class="badge badge-light border mr-1">
                    Trouvé : <strong><?= number_format($month['found'], 2, ',', '.') ?> €</strong>
                </span>
                <span class="badge <?= $ecartMois == 0 ? 'badge-success' : 'badge-danger' ?>">
                    Écart : <?= ($ecartMois >= 0 ? '+' : '') . number_format($ecartMois, 2, ',', '.') ?> €
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-rbcd">
                    <tr>
                        <th style="width:110px">Date</th>
                        <th>Catégorie</th>
                        <th class="text-right" style="width:130px">Calculé</th>
                        <th class="text-right" style="width:130px">Trouvé</th>
                        <th class="text-right" style="width:120px">Écart</th>
                        <th>Clôturé par</th>
                        <th>Encodé par</th>
                        <th class="text-center" style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($month['rows'] as $r): ?>
                <?php $ecart = (float)$r->amount_found - (float)$r->amount_calculated; ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($r->date)) ?></td>
                    <td><?= esc($catLabels[$r->category] ?? $r->category) ?></td>
                    <td class="text-right"><?= number_format((float)$r->amount_calculated, 2, ',', '.') ?> €</td>
                    <td class="text-right"><?= number_format((float)$r->amount_found, 2, ',', '.') ?> €</td>
                    <td class="text-right">
                        <span class="badge <?= $ecart == 0 ? 'badge-success' : 'badge-danger' ?>">
                            <?= ($ecart >= 0 ? '+' : '') . number_format($ecart, 2, ',', '.') ?> €
                        </span>
                    </td>
                    <td><?= esc($r->closer_name ?: '—') ?></td>
                    <td><?= esc($r->encoder_name ?: '—') ?></td>
                    <td class="text-center">
                        <a href="<?= base_url('admin/treasury/envelopes/' . $r->id . '/edit') ?>"
                           class="btn btn-xs btn-info" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-xs btn-danger btn-delete-env"
                                data-id="<?= $r->id ?>"
                                data-encoder-id="<?= (int)$r->encoded_by_member_id ?>"
                                data-date="<?= date('d/m/Y', strtotime($r->date)) ?>"
                                title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold bg-light">
                        <td colspan="2">Total <?= $month['label'] ?></td>
                        <td class="text-right"><?= number_format($month['calculated'], 2, ',', '.') ?> €</td>
                        <td class="text-right"><?= number_format($month['found'], 2, ',', '.') ?> €</td>
                        <td class="text-right">
                            <span class="badge <?= $ecartMois == 0 ? 'badge-success' : 'badge-danger' ?>">
                                <?= ($ecartMois >= 0 ? '+' : '') . number_format($ecartMois, 2, ',', '.') ?> €
                            </span>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<form id="deleteEnvForm" method="post" action=""><?= csrf_field() ?></form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    const currentYear     = <?= $currentYear ?>;
    const currentMonth    = <?= $currentMonth ?>;
    const allMonths       = <?= json_encode(array_slice($monthNames, 1, null, true)) ?>;
    const currentMemberId = <?= $currentMemberId ?>;

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

    $(document).on('click', '.btn-delete-env', function () {
        const id        = $(this).data('id');
        const encoderId = $(this).data('encoder-id');
        const date      = $(this).data('date');

        if (encoderId && encoderId !== currentMemberId) {
            Swal.fire({
                icon: 'error',
                title: 'Action non autorisée',
                text: 'Seul l\'encodeur peut supprimer cette enveloppe.',
                confirmButtonColor: '#84252B',
            });
            return;
        }

        Swal.fire({
            title: 'Supprimer l\'enveloppe ?',
            html: `Confirmer la suppression de l'enveloppe du <strong>${date}</strong> ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                $('#deleteEnvForm').attr('action', `<?= base_url('admin/treasury/envelopes/') ?>${id}/delete`).submit();
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
