<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit     = $user !== null;
$errors     = session()->getFlashdata('errors') ?? [];
$formAction = $isEdit
    ? base_url('admin/users/' . $user->id . '/update')
    : base_url('admin/users');
$selectedRoles = old('roles', $userRoles ?? []);
?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title">
            <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-user-shield' ?> mr-2"></i>
            <?= $isEdit ? 'Modifier un utilisateur' : 'Nouveau compte admin' ?>
        </h3>
    </div>
    <div class="card-body">

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <?php if (!$isEdit): ?>
                <input type="hidden" name="member_id" value="<?= $member->id ?>">
            <?php endif; ?>

            <?php if (!$isEdit): ?>
            <!-- Membre lié (lecture seule) -->
            <div class="alert alert-light border mb-4 d-flex align-items-center">
                <?php if ($member->photo): ?>
                    <img src="<?= base_url('uploads/members/' . $member->photo) ?>"
                         class="img-circle member-photo-thumb mr-3"
                         style="width:48px;height:48px;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-3x text-muted mr-3"></i>
                <?php endif; ?>
                <div>
                    <strong class="d-block"><?= esc($member->last_name . ' ' . $member->first_name) ?></strong>
                    <span class="text-muted small"><?= esc($member->email ?? '—') ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <?php if ($isEdit): ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                               class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                               value="<?= old('first_name', $isEdit ? $user->first_name : '') ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" name="last_name"
                               class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                               value="<?= old('last_name', $isEdit ? $user->last_name : '') ?>"
                               style="text-transform:uppercase" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= old('email', $isEdit ? $user->email : ($member->email ?? '')) ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Rôles <span class="text-danger">*</span></label>
                        <?php if (isset($errors['roles'])): ?>
                            <div class="text-danger small mb-1"><?= $errors['roles'] ?></div>
                        <?php endif; ?>
                        <div class="border rounded p-2 <?= isset($errors['roles']) ? 'border-danger' : '' ?>">
                            <?php foreach ($roles as $role): ?>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="role_<?= md5($role) ?>"
                                       name="roles[]"
                                       value="<?= esc($role) ?>"
                                       <?= in_array($role, (array)$selectedRoles) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="role_<?= md5($role) ?>">
                                    <?= esc($role) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mot de passe <small class="text-muted">(vide = inchangé)</small></label>
                        <input type="password" name="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               autocomplete="new-password"
                               placeholder="Laisser vide pour ne pas modifier">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Confirmer le mot de passe</label>
                        <input type="password" name="password_confirm"
                               class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                               autocomplete="new-password">
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="invalid-feedback"><?= $errors['password_confirm'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-key mr-2"></i>
                Mot de passe par défaut : <strong>Admin@2026</strong> — à communiquer à l'utilisateur.
            </div>
            <?php endif; ?>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <?php
                    $isActiveChecked = old('is_active') !== null
                        ? old('is_active') == '1'
                        : ($isEdit ? (bool) $user->is_active : true);
                    ?>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                        <?= $isActiveChecked ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="is_active">Compte actif</label>
                </div>
            </div>

            <hr>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer le compte' ?>
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i> Annuler
                </a>
            </div>
        </form>

    </div>
</div>

<?= $this->endSection() ?>
