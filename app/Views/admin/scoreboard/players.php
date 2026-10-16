<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Joueurs externes</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Ces joueurs sont ajoutés automatiquement lors de l'encodage d'une rencontre et proposés ensuite
      en auto-complétion. Les membres du club n'apparaissent pas ici : ils viennent de la fiche membre.
    </p>
    <?php if (!$players): ?>
      <p class="text-muted text-center mb-0">Aucun joueur externe encodé pour l'instant.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover" id="playersTable">
      <thead class="thead-rbcd">
        <tr><th>Nom</th><th>Prénom</th><th>Club</th><th width="110">Code club</th><th width="120">Licence</th><th width="110" class="text-right">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($players as $p): ?>
        <tr>
          <form method="post" action="<?= base_url('admin/scoreboard/players/' . $p->id . '/update') ?>" id="pl<?= $p->id ?>">
            <?= csrf_field() ?>
          </form>
          <td data-order="<?= esc($p->last_name) ?>"><input type="text" name="last_name" form="pl<?= $p->id ?>" class="form-control form-control-sm" style="text-transform:uppercase" value="<?= esc($p->last_name) ?>" required></td>
          <td data-order="<?= esc($p->first_name) ?>"><input type="text" name="first_name" form="pl<?= $p->id ?>" class="form-control form-control-sm" value="<?= esc($p->first_name) ?>" required></td>
          <td data-order="<?= esc($p->club) ?>"><input type="text" name="club" form="pl<?= $p->id ?>" class="form-control form-control-sm" style="text-transform:uppercase" value="<?= esc($p->club) ?>"></td>
          <td data-order="<?= esc($p->club_code ?? '') ?>"><input type="text" name="club_code" form="pl<?= $p->id ?>" class="form-control form-control-sm" maxlength="10" style="text-transform:uppercase" placeholder="ex. LLL 30" value="<?= esc($p->club_code ?? '') ?>"></td>
          <td data-order="<?= esc($p->license ?? '') ?>"><input type="text" name="license" form="pl<?= $p->id ?>" class="form-control form-control-sm" maxlength="25" value="<?= esc($p->license ?? '') ?>"></td>
          <td class="text-right text-nowrap align-middle">
            <button form="pl<?= $p->id ?>" class="btn btn-xs btn-warning" title="Enregistrer"><i class="fas fa-save"></i></button>
            <form method="post" action="<?= base_url('admin/scoreboard/players/' . $p->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer ce joueur de l\'auto-complétion ?')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
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
<?= $this->section('scripts') ?>
<script>
$(function () { $('#playersTable').DataTable({ pageLength: 25, columnDefs: [{ orderable: false, targets: 3 }] }); });
</script>
<?= $this->endSection() ?>
