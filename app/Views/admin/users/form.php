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
            <?php if (!$isEdit && $member !== null): ?>
                <input type="hidden" name="member_id" value="<?= $member->id ?>">
            <?php endif; ?>

            <?php if (!$isEdit && $member !== null): ?>
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

            <?php if ($isEdit || $member === null): ?>
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
            <?php endif; ?>

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
                        <?php if ($isExternal): ?>
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Un compte externe ne peut être que <strong>Webmaster</strong>.<br>
                                <small>Les autres rôles exigent d'être membre du club en ordre de cotisation.</small>
                            </div>
                            <input type="hidden" name="roles[]" value="Webmaster">
                            <div class="border rounded p-2 bg-light">
                                <span class="badge badge-secondary px-3 py-2">Webmaster</span>
                            </div>
                        <?php else: ?>
                            <?php
                            $assignedRoles   = array_values($selectedRoles);
                            $availableRoles  = array_values(array_diff($roles, $assignedRoles));
                            ?>

                            <!-- Zone 1 : rôles assignés (drag & drop) -->
                            <p class="text-muted small mb-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Glisser pour réordonner — cliquer <strong>×</strong> pour retirer
                            </p>
                            <ul id="rolesSortable"
                                class="roles-sortable <?= isset($errors['roles']) ? 'border border-danger rounded' : '' ?>"
                                data-placeholder="Aucun rôle assigné — ajoutez-en un ci-dessous">
                                <?php foreach ($assignedRoles as $role): ?>
                                <li class="role-item" data-role="<?= esc($role) ?>">
                                    <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                    <span class="role-name"><?= esc($role) ?></span>
                                    <input type="hidden" name="roles[]" value="<?= esc($role) ?>">
                                    <button type="button" class="btn-remove-role" title="Retirer ce rôle">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Zone 2 : rôles disponibles à ajouter -->
                            <div id="rolesAvailable" class="roles-available mt-2">
                                <?php foreach ($availableRoles as $role): ?>
                                <span class="role-badge-add" data-role="<?= esc($role) ?>">
                                    <i class="fas fa-plus-circle mr-1"></i><?= esc($role) ?>
                                </span>
                                <?php endforeach; ?>
                                <?php if (empty($availableRoles)): ?>
                                <span class="text-muted small">Tous les rôles sont assignés.</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
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
            <div class="alert alert-info">
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

<?= $this->section('styles') ?>
<style>
/* Liste sortable */
.roles-sortable {
    list-style: none;
    padding: 6px;
    margin: 0;
    min-height: 48px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: #fff;
}
.roles-sortable:empty::before {
    content: attr(data-placeholder);
    display: block;
    padding: 8px 6px;
    color: #aaa;
    font-size: .88rem;
    font-style: italic;
}
.role-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 10px;
    margin-bottom: 4px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: default;
    transition: background .15s, box-shadow .15s;
}
.role-item:last-child { margin-bottom: 0; }
.role-item.sortable-ghost {
    background: #e3f2fd;
    border-color: #90caf9;
    opacity: .6;
}
.drag-handle {
    cursor: grab;
    color: #aaa;
    font-size: .9rem;
    flex-shrink: 0;
    padding: 0 2px;
}
.drag-handle:active { cursor: grabbing; }
.role-name {
    flex: 1;
    font-weight: 600;
    font-size: .9rem;
    color: #333;
}
.btn-remove-role {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0 4px;
    font-size: .85rem;
    flex-shrink: 0;
    opacity: .7;
    transition: opacity .15s;
}
.btn-remove-role:hover { opacity: 1; }
/* Badges des rôles disponibles */
.roles-available { display: flex; flex-wrap: wrap; gap: 6px; }
.role-badge-add {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    background: #e9ecef;
    border: 1px solid #ced4da;
    border-radius: 20px;
    font-size: .83rem;
    color: #555;
    cursor: pointer;
    transition: background .15s, color .15s;
    user-select: none;
}
.role-badge-add:hover {
    background: #84252B;
    border-color: #84252B;
    color: #fff;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
(function () {
    const list      = document.getElementById('rolesSortable');
    const available = document.getElementById('rolesAvailable');
    if (!list) return;

    // Init SortableJS
    Sortable.create(list, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
    });

    // Retirer un rôle (délégation)
    list.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-role');
        if (!btn) return;
        const li   = btn.closest('.role-item');
        const role = li.dataset.role;
        li.remove();
        addBadge(role);
        checkEmpty();
    });

    // Ajouter un rôle depuis les badges
    if (available) {
        available.addEventListener('click', function (e) {
            const badge = e.target.closest('.role-badge-add');
            if (!badge) return;
            const role = badge.dataset.role;
            badge.remove();
            addRoleItem(role);
            checkEmpty();
        });
    }

    function addRoleItem(role) {
        const li = document.createElement('li');
        li.className   = 'role-item';
        li.dataset.role = role;
        li.innerHTML = `
            <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
            <span class="role-name">${escHtml(role)}</span>
            <input type="hidden" name="roles[]" value="${escHtml(role)}">
            <button type="button" class="btn-remove-role" title="Retirer ce rôle">
                <i class="fas fa-times"></i>
            </button>`;
        list.appendChild(li);
    }

    function addBadge(role) {
        if (!available) return;
        const badge = document.createElement('span');
        badge.className    = 'role-badge-add';
        badge.dataset.role = role;
        badge.innerHTML    = `<i class="fas fa-plus-circle mr-1"></i>${escHtml(role)}`;
        available.appendChild(badge);
        // Nettoyer le message "tous assignés" s'il existe
        available.querySelectorAll('.text-muted').forEach(el => el.remove());
    }

    function checkEmpty() {
        if (list.querySelectorAll('.role-item').length === 0 && !list.querySelector('.roles-empty-msg')) {
            // Le pseudo-element CSS ::before gère le message vide
        }
        if (available && available.querySelectorAll('.role-badge-add').length === 0
            && !available.querySelector('.text-muted')) {
            const msg = document.createElement('span');
            msg.className   = 'text-muted small';
            msg.textContent = 'Tous les rôles sont assignés.';
            available.appendChild(msg);
        }
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
<?= $this->endSection() ?>
