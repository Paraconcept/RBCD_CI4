<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if ($errors = session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
      <?php foreach ((array) $errors as $e): ?>
        <li><?= esc($e) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-user-shield mr-2"></i>
      <?= $member ? 'Modifier les rôles — ' . esc($member->last_name . ' ' . $member->first_name) : 'Donner un accès admin' ?>
    </h3>
  </div>

  <form method="POST" action="<?= $member ? base_url('admin/users/' . $member->id . '/update') : base_url('admin/users') ?>">
    <?= csrf_field() ?>

    <div class="card-body">

      <?php if (!$member): ?>
      <div class="form-group">
        <label>Membre <span class="text-danger">*</span></label>
        <select name="member_id" class="form-control select2" style="width:100%" required>
          <option value="">— Choisir un membre —</option>
          <?php foreach ($members as $m): ?>
            <option value="<?= $m->id ?>" <?= old('member_id') == $m->id ? 'selected' : '' ?>>
              <?= esc($m->last_name . ' ' . $m->first_name) ?>
              <?php if ($m->email): ?> (<?= esc($m->email) ?>)<?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Seuls les membres sans accès admin sont listés.</small>
      </div>
      <?php else: ?>
      <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle mr-1"></i>
        Le mot de passe est géré par le membre lui-même via "Première connexion / Mot de passe oublié".
      </div>
      <?php endif; ?>

      <div class="form-group">
        <label>Rôles <span class="text-danger">*</span></label>
        <div class="row">
          <?php foreach ($roles as $role): ?>
          <div class="col-md-4 col-sm-6">
            <div class="custom-control custom-checkbox mb-1">
              <input type="checkbox" name="roles[]" value="<?= esc($role) ?>"
                     id="role_<?= md5($role) ?>" class="custom-control-input"
                     <?= in_array($role, old('roles', $userRoles)) ? 'checked' : '' ?>>
              <label class="custom-control-label" for="role_<?= md5($role) ?>"><?= esc($role) ?></label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="<?= base_url('admin/users') ?>" class="btn btn-default">
        <i class="fas fa-arrow-left mr-1"></i> Retour
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>
        <?= $member ? 'Enregistrer les rôles' : 'Donner l\'accès' ?>
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
