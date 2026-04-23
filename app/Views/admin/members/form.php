<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit     = $member !== null;
$errors     = session()->getFlashdata('errors') ?? [];
$formAction = $isEdit
    ? base_url('admin/members/' . $member->id . '/update')
    : base_url('admin/members');

function v($field, $default = '') {
    global $isEdit, $member, $errors;
    return old($field, $isEdit ? ($member->$field ?? $default) : $default);
}
function chk($field, $default = false) {
    global $isEdit, $member;
    $oldVal = old($field);
    if ($oldVal !== null) return (bool)$oldVal;
    return $isEdit ? (bool)($member->$field ?? $default) : $default;
}
?>

<form action="<?= $formAction ?>" method="post" enctype="multipart/form-data" autocomplete="off">
<?= csrf_field() ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row">

    <!-- ── Colonne gauche ───────────────────────────────────── -->
    <div class="col-lg-8">

        <!-- Identité -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Identité</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Nom <span class="text-danger">*</span></label>
                            <input type="text" name="last_name"
                                   class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(v('last_name')) ?>" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="first_name"
                                   class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(v('first_name')) ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Genre <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control" required>
                                <option value="M" <?= v('gender','M') === 'M' ? 'selected' : '' ?>>M</option>
                                <option value="F" <?= v('gender','M') === 'F' ? 'selected' : '' ?>>F</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Date de naissance</label>
                            <input type="date" name="birth_date" class="form-control"
                                   value="<?= esc(v('birth_date')) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coordonnées -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Coordonnées</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= esc(v('address')) ?>">
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Code postal</label>
                            <input type="text" name="postal_code" class="form-control"
                                   value="<?= esc(v('postal_code')) ?>">
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label>Localité</label>
                            <input type="text" name="city" class="form-control"
                                   value="<?= esc(v('city')) ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= esc(v('phone')) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>GSM</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="<?= esc(v('mobile')) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email"
                                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc(v('email')) ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statut club -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Statut club</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Classement</label>
                            <input type="number" name="ranking" class="form-control"
                                   min="0" value="<?= esc(v('ranking')) ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>N° licence FRBB</label>
                            <input type="text" name="frbb_license" class="form-control"
                                   value="<?= esc(v('frbb_license')) ?>">
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <?php
                    $switches = [
                        'is_federated' => ['label' => 'Fédéré FRBB',    'default' => true],
                        'is_junior'    => ['label' => 'Junior',          'default' => false],
                        'is_supporter' => ['label' => 'Sympathisant',    'default' => false],
                        'is_school'    => ['label' => 'École de billard','default' => false],
                        'is_active'    => ['label' => 'Membre actif',    'default' => true],
                    ];
                    foreach ($switches as $name => $cfg):
                    ?>
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="<?= $name ?>" value="0">
                            <input type="checkbox" class="custom-control-input"
                                   id="<?= $name ?>" name="<?= $name ?>" value="1"
                                   <?= chk($name, $cfg['default']) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="<?= $name ?>">
                                <?= $cfg['label'] ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Colonne droite ────────────────────────────────────── -->
    <div class="col-lg-4">

        <!-- Photo -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-camera mr-2"></i>Photo</h3>
            </div>
            <div class="card-body text-center">
                <div id="photoPreview" class="mb-3">
                    <?php if ($isEdit && $member->photo): ?>
                        <img id="previewImg" src="<?= base_url('uploads/members/' . $member->photo) ?>"
                             class="img-fluid img-circle" style="width:120px;height:120px;object-fit:cover;">
                    <?php else: ?>
                        <img id="previewImg" src="#" class="img-fluid img-circle d-none"
                             style="width:120px;height:120px;object-fit:cover;">
                        <div id="noPhoto" class="text-muted">
                            <i class="fas fa-user-circle fa-5x"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-upload mr-1"></i> Choisir une photo
                        <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                    </label>
                    <div class="text-muted small mt-1">JPG, PNG, WebP — max 2 Mo</div>
                    <?php if (isset($errors['photo'])): ?>
                        <div class="text-danger small"><?= $errors['photo'] ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($isEdit && $member->photo): ?>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="remove_photo" name="remove_photo" value="1">
                        <label class="custom-control-label text-danger" for="remove_photo">Supprimer la photo</label>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Visibilité publique -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Visibilité publique</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Champs visibles sur le site public.</p>
                <?php
                $visFields = [
                    'show_birth_date' => ['label' => 'Date de naissance', 'default' => true],
                    'show_address'    => ['label' => 'Adresse',           'default' => false],
                    'show_phone'      => ['label' => 'Téléphone',         'default' => true],
                    'show_mobile'     => ['label' => 'GSM',               'default' => true],
                    'show_email'      => ['label' => 'Email',             'default' => true],
                ];
                foreach ($visFields as $name => $cfg):
                ?>
                <div class="custom-control custom-switch mb-2">
                    <input type="hidden" name="<?= $name ?>" value="0">
                    <input type="checkbox" class="custom-control-input"
                           id="<?= $name ?>" name="<?= $name ?>" value="1"
                           <?= chk($name, $cfg['default']) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="<?= $name ?>"><?= $cfg['label'] ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Compte admin lié -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield mr-2"></i>Compte admin</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Lier ce membre à un utilisateur admin (comité).</p>
                <div class="form-group mb-0">
                    <select name="admin_user_id" class="form-control select2" style="width:100%">
                        <option value="">— Aucun lien —</option>
                        <?php if ($linkedAdminUser): ?>
                            <option value="<?= $linkedAdminUser->id ?>" selected>
                                <?= esc($linkedAdminUser->last_name . ' ' . $linkedAdminUser->first_name) ?>
                            </option>
                        <?php endif; ?>
                        <?php foreach ($freeAdminUsers as $au): ?>
                            <?php if ($linkedAdminUser && $au->id == $linkedAdminUser->id) continue; ?>
                            <option value="<?= $au->id ?>">
                                <?= esc($au->last_name . ' ' . $au->first_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Boutons -->
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-primary mr-2">
            <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
        </button>
        <a href="<?= base_url('admin/members') ?>" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i> Annuler
        </a>
    </div>
</div>

</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function() {
    $('.select2').select2({ theme: 'bootstrap4', placeholder: '— Aucun lien —', allowClear: true });

    // Preview photo
    $('#photoInput').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#previewImg').attr('src', e.target.result).removeClass('d-none');
            $('#noPhoto').addClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // Décocher remove_photo si on choisit un nouveau fichier
    $('#photoInput').on('change', function() {
        if (this.files.length) $('#remove_photo').prop('checked', false);
    });
});
</script>
<?= $this->endSection() ?>
