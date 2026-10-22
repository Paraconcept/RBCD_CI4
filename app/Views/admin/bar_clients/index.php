<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$reasons = ['inverse' => 'mêmes mots, autre ordre', 'proche' => 'une lettre de différence', 'abrege' => 'prénom abrégé', 'identique' => 'même nom (accents, tirets)'];
$who = static fn (object $c): string => esc($c->name)
    . ($c->member_id ? ' <span class="badge badge-success">membre</span>' : '')
    . ' <small class="text-muted">· ' . (int) $c->ticket_count . ' ticket' . ((int) $c->ticket_count > 1 ? 's' : '') . '</small>';
?>
<?php if ($duplicates): ?>
<div class="card card-outline card-warning" id="duplicates">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Doublons probables (<?= count($duplicates) ?>)</h3>
  </div>
  <div class="card-body">
    <p class="text-muted small">
      Ces noms se ressemblent : c'est sans doute la même personne. <b>Fusionner</b> garde le premier nom (le membre, sinon celui qui a le plus de tickets, sinon celui écrit NOM PRÉNOM) et lui rattache les tickets de l'autre ; <i class="fas fa-exchange-alt"></i> garde plutôt le second.
      Si ce sont deux personnes différentes, cliquez sur <b>Pas un doublon</b> : la paire ne sera plus proposée.
    </p>
    <table class="table table-sm table-bordered mb-0">
      <thead class="thead-rbcd">
        <tr><th>À conserver</th><th>Doublon</th><th width="190">Ressemblance</th><th width="250" class="text-right">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($duplicates as $d): ?>
        <tr>
          <td class="align-middle"><?= $who($d->keep) ?></td>
          <td class="align-middle"><?= $who($d->drop) ?></td>
          <td class="align-middle text-muted"><?= $reasons[$d->reason] ?? '' ?></td>
          <td class="text-right text-nowrap align-middle">
            <form method="post" action="<?= base_url('admin/caisse/clients/' . $d->drop->id . '/fusionner') ?>" class="d-inline"
                  onsubmit="return confirm('Fusionner « <?= esc($d->drop->name, 'js') ?> » dans « <?= esc($d->keep->name, 'js') ?> » ? Ses tickets lui seront rattachés et ce nom disparaîtra.')">
              <?= csrf_field() ?><input type="hidden" name="into_id" value="<?= $d->keep->id ?>">
              <button class="btn btn-xs btn-info"><i class="fas fa-compress-alt mr-1"></i>Fusionner</button>
            </form>
            <?php if (!$d->drop->member_id): ?>
            <form method="post" action="<?= base_url('admin/caisse/clients/' . $d->keep->id . '/fusionner') ?>" class="d-inline"
                  onsubmit="return confirm('Garder plutôt « <?= esc($d->drop->name, 'js') ?> » et y fusionner « <?= esc($d->keep->name, 'js') ?> » ?')">
              <?= csrf_field() ?><input type="hidden" name="into_id" value="<?= $d->drop->id ?>">
              <button class="btn btn-xs btn-outline-info" title="Garder plutôt le nom de droite"><i class="fas fa-exchange-alt"></i></button>
            </form>
            <?php endif; ?>
            <form method="post" action="<?= base_url('admin/caisse/clients/pas-un-doublon') ?>" class="d-inline">
              <?= csrf_field() ?><input type="hidden" name="a" value="<?= $d->keep->id ?>"><input type="hidden" name="b" value="<?= $d->drop->id ?>">
              <button class="btn btn-xs btn-outline-secondary">Pas un doublon</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-user-tag mr-2"></i>Clients de la caisse</h3>
    <div class="ml-auto">
      <form method="post" action="<?= base_url('admin/caisse/clients/synchroniser') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-primary" title="Ajoute les membres actifs manquants et met leurs noms à jour"><i class="fas fa-sync-alt mr-1"></i>Synchroniser avec les membres</button>
      </form>
    </div>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Ces noms alimentent l'autocomplétion à la création d'un ticket. Chaque membre actif est client automatiquement (son nom suit sa fiche membre) ;
      les autres clients sont créés au bar, à la volée, au format <b>NOM PRÉNOM</b>. Un doublon se <b>fusionne</b> dans le bon client : ses tickets lui sont rattachés.
    </p>
    <table class="table table-sm table-bordered table-hover" id="clientsTable">
      <thead class="thead-rbcd">
        <tr><th>Nom</th><th width="90" class="text-center">Membre</th><th width="120">Dernier passage</th><th width="80" class="text-center">Tickets</th><th width="320">Fusionner dans…</th><th width="90" class="text-right">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($clients as $c): ?>
        <tr>
          <td data-order="<?= esc($c->name) ?>">
            <?php if ($c->member_id): ?>
              <?= esc($c->name) ?>
            <?php else: ?>
              <form method="post" action="<?= base_url('admin/caisse/clients/' . $c->id . '/update') ?>" id="cl<?= $c->id ?>"><?= csrf_field() ?></form>
              <input type="text" name="name" form="cl<?= $c->id ?>" class="form-control form-control-sm" style="text-transform:uppercase" maxlength="100" value="<?= esc($c->name) ?>" required>
            <?php endif; ?>
          </td>
          <td class="text-center align-middle"><?= $c->member_id ? '<a href="' . base_url('admin/members/' . $c->member_id . '/edit') . '" title="' . esc($c->member_name) . '"><i class="fas fa-id-card text-success"></i></a>' : '' ?></td>
          <td class="align-middle" data-order="<?= esc((string) $c->last_visit_at) ?>"><?= $c->last_visit_at ? date('d/m/Y', strtotime($c->last_visit_at)) : '—' ?></td>
          <td class="text-center align-middle"><?= (int) $c->ticket_count ?></td>
          <td class="align-middle">
            <?php if (!$c->member_id): ?>
            <form method="post" action="<?= base_url('admin/caisse/clients/' . $c->id . '/fusionner') ?>" class="d-flex"
                  onsubmit="return confirm('Fusionner « <?= esc($c->name, 'js') ?> » dans le client choisi ? Ses tickets lui seront rattachés et ce nom disparaîtra.')">
              <?= csrf_field() ?>
              <select name="into_id" class="form-control form-control-sm mr-1" required>
                <option value="">— Client à conserver —</option>
                <?php foreach ($clients as $o): if ($o->id === $c->id) continue; ?>
                <option value="<?= $o->id ?>"><?= esc($o->name) ?><?= $o->member_id ? ' (membre)' : '' ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-xs btn-info" title="Fusionner"><i class="fas fa-compress-alt"></i></button>
            </form>
            <?php endif; ?>
          </td>
          <td class="text-right text-nowrap align-middle">
            <?php if (!$c->member_id): ?>
            <button form="cl<?= $c->id ?>" class="btn btn-xs btn-warning" title="Enregistrer"><i class="fas fa-save"></i></button>
            <?php endif; ?>
            <?php if ((int) $c->ticket_count): ?>
            <span class="d-inline-block" tabindex="0" title="A des tickets : ne peut pas être supprimé (fusionnez-le plutôt dans le bon client)">
              <button class="btn btn-xs btn-danger" disabled style="pointer-events:none"><i class="fas fa-trash"></i></button>
            </span>
            <?php else: ?>
            <form method="post" action="<?= base_url('admin/caisse/clients/' . $c->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Retirer « <?= esc($c->name, 'js') ?> » de la liste des clients ?<?= $c->member_id ? '\n\nC\'est un membre : il reviendra si sa fiche est réactivée, ou avec « Synchroniser avec les membres ».' : '' ?>')">
              <?= csrf_field() ?><button class="btn btn-xs btn-danger" title="Supprimer de la liste"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(function () { $('#clientsTable').DataTable({ pageLength: 50, order: [[0, 'asc']], columnDefs: [{ orderable: false, targets: [4, 5] }] }); });
</script>
<?= $this->endSection() ?>
