<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit   = $user !== null;
$errors   = session()->getFlashdata('errors') ?? [];
$formAction = $isEdit
    ? base_url('admin/users/' . $user->id . '/update')
    : base_url('admin/users');
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
                <!-- Prénom -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                               class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                               value="<?= old('first_name', $isEdit ? $user->first_name : '') ?>"
                               required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nom -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nom <span class="text-danger">*</span></label>
                        <input type="text" name="last_name"
                               class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                               value="<?= old('last_name', $isEdit ? $user->last_name : '') ?>"
                               required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Email -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= old('email', $isEdit ? $user->email : '') ?>"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rôle -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Rôle <span class="text-danger">*</span></label>
                        <select name="role" class="form-control <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r ?>" <?= old('role', $isEdit ? $user->role : '') === $r ? 'selected' : '' ?>>
                                    <?= $r ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback"><?= $errors['role'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Mot de passe -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label>
                            Mot de passe <?= !$isEdit ? '<span class="text-danger">*</span>' : '<small class="text-muted">(laisser vide = inchangé)</small>' ?>
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

                <!-- Confirmation mot de passe -->
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

            <!-- Statut actif -->
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
