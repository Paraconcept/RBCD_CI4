<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit     = $user !== null;
$errors     = session()->getFlashdata('errors') ?? [];
$formAction = $isEdit
    ? base_url('admin/users/' . $user->id . '/update')
    : base_url('admin/users');
// Rôles sélectionnés : old() en création, $userRoles en édition
$selectedRoles = old('roles', $userRoles ?? []);
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus' ?> mr-2"></i>
            <?= $isEdit ? 'Modifier un utilisateur' : 'Nouvel utilisateur' ?>
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

            <div class="row">
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
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= old('email', $isEdit ? $user->email : '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Rôles (multi-sélection par cases à cocher) -->
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

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            Mot de passe
                            <?= !$isEdit ? '<span class="text-danger">*</span>' : '<small class="text-muted">(vide = inchangé)</small>' ?>
                        </label>
                        <input type="password" name="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               <?= !$isEdit ? 'required' : '' ?>
                               autocomplete="new-password"
                               placeholder="<?= $isEdit ? 'Laisser vide pour ne pas modifier' : 'Min. 8 caractères' ?>">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Confirmer le mot de passe <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="password" name="password_confirm"
                               class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                               <?= !$isEdit ? 'required' : '' ?>
                               autocomplete="new-password">
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="invalid-feedback"><?= $errors['password_confirm'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                        <?= old('is_active', $isEdit ? $user->is_active : 1) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="is_active">Compte actif</label>
                </div>
            </div>

            <hr>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
                </button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i> Annuler
                </a>
            </div>
        </form>

    </div>
</div>

<?= $this->endSection() ?>
