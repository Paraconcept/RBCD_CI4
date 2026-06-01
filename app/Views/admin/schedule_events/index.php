<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
            <i class="fas fa-calendar-alt mr-2"></i>Événements du tableau
        </h3>
        <a href="<?= base_url('admin/schedule-events/create') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>Nouvel événement
        </a>
    </div>
    <div class="card-body p-0">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success m-3 mb-0">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($events)): ?>
            <p class="text-muted p-3 mb-0">Aucun événement enregistré.</p>
        <?php else: ?>
        <table class="table table-hover table-sm mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Couleur</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $ev): ?>
                <?php $c = $colors[$ev->color] ?? $colors['blue']; ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($ev->event_date)) ?></td>
                    <td><?= $ev->start_time ? substr($ev->start_time, 0, 5) : '—' ?></td>
                    <td>
                        <span class="badge" style="background:<?= $c['border'] ?>;color:#fff;font-size:.82rem;">
                            <?= esc($ev->title) ?>
                        </span>
                    </td>
                    <td style="font-size:.85rem;color:#555;"><?= esc($ev->description ?? '—') ?></td>
                    <td>
                        <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:<?= $c['border'] ?>;vertical-align:middle;"></span>
                        <small><?= esc($c['label']) ?></small>
                    </td>
                    <td class="text-right">
                        <a href="<?= base_url("admin/schedule-events/{$ev->id}/duplicate") ?>"
                           class="btn btn-xs btn-info mr-1" title="Dupliquer">
                            <i class="fas fa-copy"></i>
                        </a>
                        <a href="<?= base_url("admin/schedule-events/{$ev->id}/edit") ?>"
                           class="btn btn-xs btn-warning mr-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="post" action="<?= base_url("admin/schedule-events/{$ev->id}/delete") ?>"
                              class="d-inline"
                              onsubmit="return confirm('Supprimer cet événement ?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-xs btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
