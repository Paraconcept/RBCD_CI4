<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('styles') ?>
<style>
#memberTabs .nav-link.active {
    background-color: #84252B;
    color: #fff !important;
}
#memberTabs .nav-link:hover:not(.active):not(.disabled) {
    background-color: rgba(132,37,43,.08);
    color: #84252B;
}
.cat-section-title {
    font-size:.85rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
    color:#fff; background:#84252B; padding:6px 14px; border-radius:3px;
    margin-bottom:14px; display:flex; align-items:center; gap:8px;
}
.cat-row { display:flex; align-items:center; gap:8px; margin-bottom:10px; }
.cat-label { min-width:115px; font-weight:600; font-size:.88rem; flex-shrink:0; }
.cat-select { flex:1; min-width:0; }
.cat-select .form-control { font-size:.88rem; }
.cat-st { flex:0 0 150px; }
.cat-st .form-control { font-size:.82rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="row">

    <!-- ── Nav gauche ───────────────────────────────────────── -->
    <div class="col-md-3 col-lg-2">
        <div class="card card-outline card-primary sticky-top" style="top:1rem">

            <div class="card-header text-center pt-3 pb-2 border-bottom-0">
                <?php if ($member->photo): ?>
                    <img src="<?= base_url('uploads/members/' . $member->photo) ?>"
                         class="img-circle" style="width:72px;height:72px;object-fit:cover">
                <?php else: ?>
                    <span class="text-muted"><i class="fas fa-user-circle" style="font-size:72px"></i></span>
                <?php endif; ?>
            </div>

            <div class="card-body p-1">
                <div class="nav flex-column nav-pills" id="memberTabs" role="tablist">

                    <a class="nav-link <?= $activeTab === 'coordonnees' ? 'active' : '' ?>"
                       href="#coordonnees" data-toggle="pill" role="tab">
                        <i class="fas fa-id-card fa-fw mr-2"></i>Coordonnées
                    </a>
                    <a class="nav-link <?= $activeTab === 'visibilite' ? 'active' : '' ?>"
                       href="#visibilite" data-toggle="pill" role="tab">
                        <i class="fas fa-eye fa-fw mr-2"></i>Visibilité publique
                    </a>
                    <a class="nav-link <?= $activeTab === 'photo' ? 'active' : '' ?>"
                       href="#photo" data-toggle="pill" role="tab">
                        <i class="fas fa-camera fa-fw mr-2"></i>Photo de profil
                    </a>
                    <a class="nav-link <?= $activeTab === 'cles' ? 'active' : '' ?>"
                       href="#cles" data-toggle="pill" role="tab">
                        <i class="fas fa-key fa-fw mr-2"></i>Clés du club
                    </a>
                    <a class="nav-link <?= $activeTab === 'cotisations' ? 'active' : '' ?>"
                       href="#cotisations" data-toggle="pill" role="tab">
                        <i class="fas fa-euro-sign fa-fw mr-2"></i>Cotisations
                    </a>
                    <a class="nav-link <?= $activeTab === 'categories' ? 'active' : '' ?>"
                       href="#categories" data-toggle="pill" role="tab">
                        <i class="fas fa-layer-group fa-fw mr-2"></i>Catégories
                    </a>

                </div>
            </div>
        </div>
    </div>

    <!-- ── Contenu à droite ──────────────────────────────────── -->
    <div class="col-md-9 col-lg-10">

        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle mr-1"></i>
            <?= session()->getFlashdata('success') ?>
        </div>
        <?php endif; ?>

        <div class="tab-content" id="memberTabsContent">

            <!-- ══════════════════════════════════════════════════════
                 ONGLET — Coordonnées
            ══════════════════════════════════════════════════════ -->
            <div class="tab-pane fade <?= $activeTab === 'coordonnees' ? 'show active' : '' ?>"
                 id="coordonnees" role="tabpanel">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form action="<?= base_url('admin/members/' . $member->id . '/update/identity') ?>"
                      method="post" autocomplete="off">
                    <?= csrf_field() ?>

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
                                               value="<?= esc(old('last_name', $member->last_name)) ?>"
                                               style="text-transform:uppercase" required>
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
                                               value="<?= esc(old('first_name', $member->first_name)) ?>" required>
                                        <?php if (isset($errors['first_name'])): ?>
                                            <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Genre <span class="text-danger">*</span></label>
                                        <select name="gender" class="form-control" required>
                                            <?php $g = old('gender', $member->gender); ?>
                                            <option value="M" <?= $g === 'M' ? 'selected' : '' ?>>M</option>
                                            <option value="F" <?= $g === 'F' ? 'selected' : '' ?>>F</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Date de naissance</label>
                                        <input type="date" name="birth_date" class="form-control"
                                               value="<?= esc(old('birth_date', $member->birth_date ?? '')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>N° Registre National</label>
                                        <input type="text" name="reg_nat" class="form-control"
                                               placeholder="00.00.00-000.00"
                                               value="<?= esc(old('reg_nat', $member->reg_nat ?? '')) ?>">
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
                                       value="<?= esc(old('address', $member->address ?? '')) ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Code postal</label>
                                        <input type="text" name="postal_code" class="form-control"
                                               value="<?= esc(old('postal_code', $member->postal_code ?? '')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label>Localité</label>
                                        <input type="text" name="city" class="form-control"
                                               value="<?= esc(old('city', $member->city ?? '')) ?>"
                                               style="text-transform:uppercase">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="text" name="phone" class="form-control"
                                               value="<?= esc(old('phone', $member->phone ?? '')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>GSM</label>
                                        <input type="text" name="mobile" class="form-control"
                                               value="<?= esc(old('mobile', $member->mobile ?? '')) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email"
                                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                               value="<?= esc(old('email', $member->email ?? '')) ?>">
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
                            <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Statut club</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>N° licence FRBB</label>
                                        <input type="text" name="frbb_license" class="form-control"
                                               value="<?= esc(old('frbb_license', $member->frbb_license ?? '')) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <?php
                                $switches = [
                                    'is_federated' => 'Fédéré FRBB',
                                    'is_junior'    => 'Junior',
                                    'is_supporter' => 'Sympathisant',
                                    'is_school'    => 'École de billard',
                                    'is_active'    => 'Membre actif',
                                ];
                                foreach ($switches as $name => $label):
                                    $checked = (old($name) !== null)
                                        ? (bool) old($name)
                                        : (bool)($member->$name ?? 0);
                                ?>
                                <div class="col-md-4 mb-2">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="<?= $name ?>" value="0">
                                        <input type="checkbox" class="custom-control-input"
                                               id="sw_<?= $name ?>" name="<?= $name ?>" value="1"
                                               <?= $checked ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="sw_<?= $name ?>">
                                            <?= $label ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Mettre à jour
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 ONGLET — Visibilité publique
            ══════════════════════════════════════════════════════ -->
            <div class="tab-pane fade <?= $activeTab === 'visibilite' ? 'show active' : '' ?>"
                 id="visibilite" role="tabpanel">

                <form action="<?= base_url('admin/members/' . $member->id . '/update/visibility') ?>"
                      method="post" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Visibilité publique</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Champs visibles par les autres membres sur le site public.</p>
                            <?php
                            $visFields = [
                                'show_birth_date' => 'Date de naissance',
                                'show_address'    => 'Adresse',
                                'show_phone'      => 'Téléphone',
                                'show_mobile'     => 'GSM',
                                'show_email'      => 'Email',
                            ];
                            foreach ($visFields as $name => $label):
                                $checked = (bool)($member->$name ?? 0);
                            ?>
                            <div class="custom-control custom-switch mb-3">
                                <input type="hidden" name="<?= $name ?>" value="0">
                                <input type="checkbox" class="custom-control-input"
                                       id="sw_<?= $name ?>" name="<?= $name ?>" value="1"
                                       <?= $checked ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="sw_<?= $name ?>"><?= $label ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Mettre à jour
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 ONGLET — Photo de profil
            ══════════════════════════════════════════════════════ -->
            <div class="tab-pane fade <?= $activeTab === 'photo' ? 'show active' : '' ?>"
                 id="photo" role="tabpanel">

                <form action="<?= base_url('admin/members/' . $member->id . '/update/photo') ?>"
                      method="post" enctype="multipart/form-data" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-camera mr-2"></i>Photo de profil</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <?php if ($member->photo): ?>
                                    <img id="previewImg"
                                         src="<?= base_url('uploads/members/' . $member->photo) ?>"
                                         class="img-circle" style="width:140px;height:140px;object-fit:cover">
                                <?php else: ?>
                                    <img id="previewImg" src="#" class="img-circle d-none"
                                         style="width:140px;height:140px;object-fit:cover">
                                    <div id="noPhoto" class="text-muted mb-3">
                                        <i class="fas fa-user-circle fa-6x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="btn btn-outline-secondary">
                                    <i class="fas fa-upload mr-1"></i> Choisir une photo
                                    <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                                </label>
                                <div class="text-muted small mt-1">JPG, PNG, WebP — max 2 Mo</div>
                                <?php if (!empty($errors)): ?>
                                    <div class="text-danger small mt-1"><?= esc(implode(' ', $errors)) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($member->photo): ?>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                       id="remove_photo" name="remove_photo" value="1">
                                <label class="custom-control-label text-danger" for="remove_photo">
                                    Supprimer la photo actuelle
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Mettre à jour
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 ONGLET — Clés du club
            ══════════════════════════════════════════════════════ -->
            <div class="tab-pane fade <?= $activeTab === 'cles' ? 'show active' : '' ?>"
                 id="cles" role="tabpanel">

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key mr-2"></i>Clés du club</h3>
                    </div>

                    <?php if (!empty($memberKeys)): ?>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>N° badge</th>
                                    <th>Remise le</th>
                                    <th>Retournée le</th>
                                    <th>Notes</th>
                                    <th class="text-center" style="width:120px">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($memberKeys as $key): ?>
                            <tr>
                                <td><?= esc($key->badge_number ?: '—') ?></td>
                                <td><?= $key->given_date ? date('d/m/Y', strtotime($key->given_date)) : '—' ?></td>
                                <td><?= $key->returned_date ? date('d/m/Y', strtotime($key->returned_date)) : '—' ?></td>
                                <td><?= esc($key->notes ?: '') ?></td>
                                <td class="text-center">
                                    <?php if (!$key->returned_date): ?>
                                        <span class="badge badge-success">Active</span>
                                        <form id="returnKeyForm-<?= $key->id ?>"
                                              action="<?= base_url('admin/members/' . $member->id . '/keys/' . $key->id . '/return') ?>"
                                              method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="button"
                                                    class="btn btn-xs btn-warning mt-1 btn-return-key"
                                                    data-form="returnKeyForm-<?= $key->id ?>"
                                                    data-badge="<?= esc($key->badge_number ?: 'sans numéro') ?>">
                                                <i class="fas fa-undo mr-1"></i>Retourner
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Retournée</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="card-body">
                        <p class="text-muted mb-0">Aucune clé enregistrée pour ce membre.</p>
                    </div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <?php if (empty($availableKeys)): ?>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                Aucune clé disponible en stock —
                                <a href="<?= base_url('admin/club-keys') ?>">gérer les clés du club</a>.
                            </p>
                        <?php else: ?>
                        <p class="text-muted small mb-2"><i class="fas fa-plus-circle mr-1"></i>Attribuer une clé disponible</p>
                        <form action="<?= base_url('admin/members/' . $member->id . '/keys') ?>"
                              method="post" autocomplete="off">
                            <?= csrf_field() ?>
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="small mb-1">Clé <span class="text-danger">*</span></label>
                                        <select name="key_id" class="form-control form-control-sm" required>
                                            <option value="">— Choisir —</option>
                                            <?php foreach ($availableKeys as $k): ?>
                                                <option value="<?= $k->id ?>">
                                                    <?= $k->badge_number ? 'Badge #' . esc($k->badge_number) : 'Clé sans numéro' ?>
                                                    <?= $k->notes ? ' — ' . esc($k->notes) : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="small mb-1">Date de remise</label>
                                        <input type="date" name="given_date"
                                               class="form-control form-control-sm"
                                               value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-key mr-1"></i> Attribuer
                                    </button>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════════════════
                 ONGLET — Cotisations
            ══════════════════════════════════════════════════════ -->
            <div class="tab-pane fade <?= $activeTab === 'cotisations' ? 'show active' : '' ?>"
                 id="cotisations" role="tabpanel">

                <div class="card card-outline card-primary">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-euro-sign mr-2"></i>Cotisations
                        </h3>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-primary btn-sm" id="btnAddPayment">
                                <i class="fas fa-plus mr-1"></i> Ajouter une saison
                            </button>
                        </div>
                    </div>

                    <?php if (empty($payments)): ?>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">Aucune cotisation enregistrée pour ce membre.</div>
                    </div>
                    <?php else: ?>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped mb-0">
                            <thead class="thead-rbcd">
                                <tr>
                                    <th>Saison</th>
                                    <th class="text-center">Cotis. RBCD<br><small>jan–déc</small></th>
                                    <th class="text-center">Cotis. FRBB<br><small>sep–juin</small></th>
                                    <th class="text-center">Forfait F1<br><small>jan–juin</small></th>
                                    <th class="text-center">Forfait F2<br><small>juil–déc</small></th>
                                    <th class="text-center" style="width:90px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><strong><?= $p->year . '-' . ($p->year + 1) ?></strong></td>

                                    <td class="text-center">
                                        <?php if ($p->rbcd_paid): ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                            <?php if ($p->rbcd_paid_date): ?><br><small class="text-muted"><?= date('d/m/Y', strtotime($p->rbcd_paid_date)) ?></small><?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Non payé</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($p->frbb_paid): ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                            <?php if ($p->frbb_paid_date): ?><br><small class="text-muted"><?= date('d/m/Y', strtotime($p->frbb_paid_date)) ?></small><?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Non payé</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if (!$p->forfait_f1_choice): ?>
                                            <span class="text-muted">—</span>
                                        <?php elseif ($p->forfait_f1_paid): ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                            <?php if ($p->forfait_f1_paid_date): ?><br><small class="text-muted"><?= date('d/m/Y', strtotime($p->forfait_f1_paid_date)) ?></small><?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if (!$p->forfait_f2_choice): ?>
                                            <span class="text-muted">—</span>
                                        <?php elseif ($p->forfait_f2_paid): ?>
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>
                                            <?php if ($p->forfait_f2_paid_date): ?><br><small class="text-muted"><?= date('d/m/Y', strtotime($p->forfait_f2_paid_date)) ?></small><?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-info btn-edit-payment" title="Modifier"
                                                data-id="<?= $p->id ?>"
                                                data-year="<?= $p->year ?>"
                                                data-rbcd-paid="<?= (int)$p->rbcd_paid ?>"
                                                data-rbcd-paid-date="<?= esc($p->rbcd_paid_date ?? '') ?>"
                                                data-frbb-paid="<?= (int)$p->frbb_paid ?>"
                                                data-frbb-paid-date="<?= esc($p->frbb_paid_date ?? '') ?>"
                                                data-f1-choice="<?= (int)$p->forfait_f1_choice ?>"
                                                data-f1-paid="<?= (int)$p->forfait_f1_paid ?>"
                                                data-f1-paid-date="<?= esc($p->forfait_f1_paid_date ?? '') ?>"
                                                data-f2-choice="<?= (int)$p->forfait_f2_choice ?>"
                                                data-f2-paid="<?= (int)$p->forfait_f2_paid ?>"
                                                data-f2-paid-date="<?= esc($p->forfait_f2_paid_date ?? '') ?>"
                                                data-update-url="<?= base_url('admin/members/' . $member->id . '/payments/' . $p->id . '/update') ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-danger btn-delete-payment" title="Supprimer"
                                                data-id="<?= $p->id ?>" data-year="<?= $p->year ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="del-pay-<?= $p->id ?>"
                                              action="<?= base_url('admin/members/' . $member->id . '/payments/' . $p->id . '/delete') ?>"
                                              method="post" class="d-none">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_back" value="member_edit">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ══════════════════════════════════════════════════════
                 ONGLET — Catégories
            ══════════════════════════════════════════════════════ -->
            <div class="tab-pane fade <?= $activeTab === 'categories' ? 'show active' : '' ?>"
                 id="categories" role="tabpanel">

                <form action="<?= base_url('admin/members/' . $member->id . '/categories/save') ?>"
                      method="post" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Catégories du joueur</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            $uc = $memberCategories ? (array) $memberCategories : [];

                            $pfModes = [
                                'PLPF'  => 'Partie Libre',
                                'BPF'   => 'Bande',
                                'C38_2' => 'Cadre 38/2',
                                'C57_2' => 'Cadre 57/2',
                                'B3PF'  => '3 Bandes',
                            ];
                            $gfModes = [
                                'PLGF'  => 'Partie Libre',
                                'BGF'   => 'Bande',
                                'C47_2' => 'Cadre 47/2',
                                'C47_1' => 'Cadre 47/1',
                                'C71_2' => 'Cadre 71/2',
                                'B3GF'  => '3 Bandes',
                            ];
                            $statuts = [
                                'NJ'  => 'NJ — Nouveau Joueur',
                                'JR'  => 'JR — Joueur reprenant',
                                'NJR' => 'NJR — Reprenant cat. inf.',
                                'REP' => 'REP — Report rétrogradation',
                            ];

                            $renderCol = function(array $modes) use ($uc, $categoryOptions, $statuts) {
                                foreach ($modes as $col => $label): ?>
                                <div class="cat-row">
                                    <span class="cat-label"><?= esc($label) ?> :</span>
                                    <div class="cat-select">
                                        <select name="<?= $col ?>" class="form-control form-control-sm">
                                            <option value="">— Non classé —</option>
                                            <?php foreach ($categoryOptions[$col] ?? [] as $o): ?>
                                                <option value="<?= esc($o['val']) ?>"
                                                    <?= ($uc[$col] ?? null) === $o['val'] ? 'selected' : '' ?>>
                                                    <?= esc($o['val']) ?> — <?= esc($o['label']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="cat-st">
                                        <select name="<?= $col ?>_st" class="form-control form-control-sm">
                                            <option value="">—</option>
                                            <?php foreach ($statuts as $sv => $sl): ?>
                                                <option value="<?= $sv ?>"
                                                    <?= ($uc[$col . '_st'] ?? null) === $sv ? 'selected' : '' ?>>
                                                    <?= $sl ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endforeach;
                            };
                            ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="cat-section-title">
                                        <img src="<?= base_url('assets/images/icon_billard_white_150x150.png') ?>"
                                             alt="" style="width:16px;height:16px;object-fit:contain">
                                        Petit Billard <small style="font-weight:400;opacity:.85;">(2m30)</small>
                                    </div>
                                    <?php $renderCol($pfModes); ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="cat-section-title">
                                        <img src="<?= base_url('assets/images/icon_billard_white_150x150.png') ?>"
                                             alt="" style="width:16px;height:16px;object-fit:contain">
                                        Grand Billard <small style="font-weight:400;opacity:.85;">(2m84)</small>
                                    </div>
                                    <?php $renderCol($gfModes); ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Enregistrer les catégories
                            </button>
                        </div>
                    </div>
                </form>

            </div>

        </div><!-- /.tab-content -->
    </div><!-- /.col -->

</div><!-- /.row -->

<!-- ══ Modal — Cotisations (ajouter / modifier) ══════════ -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form id="paymentForm" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="_back" value="member_edit">

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="paymentModalTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <!-- Année (création seulement) -->
                    <div id="yearWrap" class="form-group">
                        <label>Année de début <span class="text-danger">*</span>
                            <small class="text-muted">(ex : <?= ANNEE_1 ?> pour la saison <?= SAISON_EN_COURS ?>)</small>
                        </label>
                        <input type="number" name="year" id="modalYear" class="form-control"
                               value="<?= ANNEE_1 ?>" min="2000" max="2100" style="max-width:120px">
                    </div>

                    <div class="row">
                        <div class="col-lg-6">

                            <!-- Cotisation RBCD -->
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" style="height:1.4em;vertical-align:middle" class="mr-2">
                                        Cotisation RBCD
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="hidden" name="rbcd_paid" value="0">
                                        <input type="checkbox" class="custom-control-input" id="pm_rbcd_paid" name="rbcd_paid" value="1">
                                        <label class="custom-control-label" for="pm_rbcd_paid">Cotisation payée</label>
                                    </div>
                                    <div class="form-group mb-0" id="pm_rbcd_date_wrap" style="display:none">
                                        <label>Date de paiement</label>
                                        <input type="date" name="rbcd_paid_date" id="pm_rbcd_paid_date" class="form-control" style="max-width:180px">
                                    </div>
                                </div>
                            </div>

                            <!-- Cotisation FRBB -->
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <img src="<?= base_url('assets/images/Ecusson_FRBB-LL.png') ?>" style="height:1.4em;vertical-align:middle" class="mr-2">
                                        Cotisation FRBB
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="hidden" name="frbb_paid" value="0">
                                        <input type="checkbox" class="custom-control-input" id="pm_frbb_paid" name="frbb_paid" value="1">
                                        <label class="custom-control-label" for="pm_frbb_paid">Cotisation payée</label>
                                    </div>
                                    <div class="form-group mb-0" id="pm_frbb_date_wrap" style="display:none">
                                        <label>Date de paiement</label>
                                        <input type="date" name="frbb_paid_date" id="pm_frbb_paid_date" class="form-control" style="max-width:180px">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-6">

                            <!-- Forfait F1 -->
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <img src="<?= base_url('assets/images/75euros.gif') ?>" style="height:1.4em;vertical-align:middle" class="mr-2">
                                        Forfait F1 <small class="text-muted">(jan–juin — 75 €)</small>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="hidden" name="forfait_f1_choice" value="0">
                                        <input type="checkbox" class="custom-control-input pm-choice-toggle"
                                               id="pm_f1_choice" name="forfait_f1_choice" value="1"
                                               data-target="#pm_f1_details">
                                        <label class="custom-control-label" for="pm_f1_choice">Le membre a souscrit au forfait F1</label>
                                    </div>
                                    <div id="pm_f1_details" style="display:none">
                                        <div class="custom-control custom-switch mb-3">
                                            <input type="hidden" name="forfait_f1_paid" value="0">
                                            <input type="checkbox" class="custom-control-input" id="pm_f1_paid" name="forfait_f1_paid" value="1">
                                            <label class="custom-control-label" for="pm_f1_paid">Forfait F1 payé</label>
                                        </div>
                                        <div class="form-group mb-0" id="pm_f1_date_wrap" style="display:none">
                                            <label>Date de paiement</label>
                                            <input type="date" name="forfait_f1_paid_date" id="pm_f1_paid_date" class="form-control" style="max-width:180px">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Forfait F2 -->
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <img src="<?= base_url('assets/images/75euros.gif') ?>" style="height:1.4em;vertical-align:middle" class="mr-2">
                                        Forfait F2 <small class="text-muted">(juil–déc — 75 €)</small>
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="hidden" name="forfait_f2_choice" value="0">
                                        <input type="checkbox" class="custom-control-input pm-choice-toggle"
                                               id="pm_f2_choice" name="forfait_f2_choice" value="1"
                                               data-target="#pm_f2_details">
                                        <label class="custom-control-label" for="pm_f2_choice">Le membre a souscrit au forfait F2</label>
                                    </div>
                                    <div id="pm_f2_details" style="display:none">
                                        <div class="custom-control custom-switch mb-3">
                                            <input type="hidden" name="forfait_f2_paid" value="0">
                                            <input type="checkbox" class="custom-control-input" id="pm_f2_paid" name="forfait_f2_paid" value="1">
                                            <label class="custom-control-label" for="pm_f2_paid">Forfait F2 payé</label>
                                        </div>
                                        <div class="form-group mb-0" id="pm_f2_date_wrap" style="display:none">
                                            <label>Date de paiement</label>
                                            <input type="date" name="forfait_f2_paid_date" id="pm_f2_paid_date" class="form-control" style="max-width:180px">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- /.modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Enregistrer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<!-- ══ /Modal cotisations ════════════════════════════════ -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {

    // Mettre à jour l'URL quand on change d'onglet (pour que F5 reste sur le bon onglet)
    $('[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        const tabId = $(e.target).attr('href').replace('#', '');
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        history.replaceState(null, '', url.toString());
    });

    // Auto-format N° Registre National → XX.XX.XX-XXX.XX
    (function () {
        const rn = document.querySelector('input[name="reg_nat"]');
        if (!rn) return;

        function fmt(digits) {
            digits = digits.replace(/\D/g, '').slice(0, 11);
            let out = '';
            for (let i = 0; i < digits.length; i++) {
                if (i === 2 || i === 4) out += '.';
                else if (i === 6)       out += '-';
                else if (i === 9)       out += '.';
                out += digits[i];
            }
            return out;
        }

        rn.addEventListener('keydown', function (e) {
            const pos = this.selectionStart;
            if (e.key === 'Backspace' && this.selectionStart === this.selectionEnd
                    && pos > 0 && /[.\-]/.test(this.value[pos - 1])) {
                e.preventDefault();
                const newVal = fmt(this.value.slice(0, pos - 2) + this.value.slice(pos));
                this.value = newVal;
                this.setSelectionRange(pos - 2, pos - 2);
            }
        });

        rn.addEventListener('input', function () {
            const pos      = this.selectionStart;
            const raw      = this.value;
            const dBefore  = raw.slice(0, pos).replace(/\D/g, '').length;
            this.value     = fmt(raw);
            let np = 0, cnt = 0;
            while (np < this.value.length && cnt < dBefore) {
                if (/\d/.test(this.value[np])) cnt++;
                np++;
            }
            this.setSelectionRange(np, np);
        });
    })();

    // Prévisualisation photo
    $('#photoInput').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#previewImg').attr('src', e.target.result).removeClass('d-none');
            $('#noPhoto').addClass('d-none');
        };
        reader.readAsDataURL(file);
        $('#remove_photo').prop('checked', false);
    });

    // ── Cotisations — modal add/edit ─────────────────────────
    const addPaymentUrl = '<?= base_url('admin/members/' . $member->id . '/payments') ?>';

    function resetPaymentModal() {
        $('#paymentForm')[0].reset();
        $('#pm_rbcd_date_wrap, #pm_frbb_date_wrap, #pm_f1_details, #pm_f1_date_wrap, #pm_f2_details, #pm_f2_date_wrap').hide();
    }

    $('#btnAddPayment').on('click', function () {
        resetPaymentModal();
        $('#paymentForm').attr('action', addPaymentUrl);
        $('#paymentModalTitle').text('Ajouter une saison');
        $('#yearWrap').show();
        $('#modalYear').attr('required', true).val('<?= ANNEE_1 ?>');
        $('#paymentModal').modal('show');
    });

    $(document).on('click', '.btn-edit-payment', function () {
        resetPaymentModal();
        const d = $(this).data();
        $('#paymentForm').attr('action', d.updateUrl);
        $('#paymentModalTitle').text('Modifier saison ' + d.year + '-' + (parseInt(d.year) + 1));
        $('#yearWrap').hide();
        $('#modalYear').removeAttr('required');

        // RBCD
        $('#pm_rbcd_paid').prop('checked', d.rbcdPaid == 1);
        $('#pm_rbcd_paid_date').val(d.rbcdPaidDate || '');
        $('#pm_rbcd_date_wrap').toggle(d.rbcdPaid == 1);

        // FRBB
        $('#pm_frbb_paid').prop('checked', d.frbbPaid == 1);
        $('#pm_frbb_paid_date').val(d.frbbPaidDate || '');
        $('#pm_frbb_date_wrap').toggle(d.frbbPaid == 1);

        // F1
        $('#pm_f1_choice').prop('checked', d.f1Choice == 1);
        $('#pm_f1_details').toggle(d.f1Choice == 1);
        $('#pm_f1_paid').prop('checked', d.f1Paid == 1);
        $('#pm_f1_paid_date').val(d.f1PaidDate || '');
        $('#pm_f1_date_wrap').toggle(d.f1Paid == 1);

        // F2
        $('#pm_f2_choice').prop('checked', d.f2Choice == 1);
        $('#pm_f2_details').toggle(d.f2Choice == 1);
        $('#pm_f2_paid').prop('checked', d.f2Paid == 1);
        $('#pm_f2_paid_date').val(d.f2PaidDate || '');
        $('#pm_f2_date_wrap').toggle(d.f2Paid == 1);

        $('#paymentModal').modal('show');
    });

    // Afficher/cacher date quand on coche payé
    $(document).on('change', '#pm_rbcd_paid',  function () { $('#pm_rbcd_date_wrap').toggle(this.checked); });
    $(document).on('change', '#pm_frbb_paid',  function () { $('#pm_frbb_date_wrap').toggle(this.checked); });
    $(document).on('change', '#pm_f1_paid',    function () { $('#pm_f1_date_wrap').toggle(this.checked); });
    $(document).on('change', '#pm_f2_paid',    function () { $('#pm_f2_date_wrap').toggle(this.checked); });

    // Afficher/cacher détails forfait
    $(document).on('change', '.pm-choice-toggle', function () {
        const $target = $($(this).data('target'));
        $target.toggle(this.checked);
        if (!this.checked) {
            $target.find('input[type=checkbox]').prop('checked', false).trigger('change');
        }
    });

    // Supprimer une cotisation
    $(document).on('click', '.btn-delete-payment', function () {
        const id   = $(this).data('id');
        const year = $(this).data('year');
        Swal.fire({
            title: 'Supprimer la saison ' + year + '-' + (parseInt(year) + 1) + ' ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#84252B',
        }).then(result => {
            if (result.isConfirmed) $('#del-pay-' + id).submit();
        });
    });
    // ── /Cotisations ──────────────────────────────────────────

    // Confirmation retour de clé
    $(document).on('click', '.btn-return-key', function () {
        const formId = $(this).data('form');
        const badge  = $(this).data('badge');
        Swal.fire({
            title: 'Retourner la clé ?',
            html: `Marquer la clé <strong>${badge}</strong> comme retournée ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, retourner',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#84252B',
        }).then(result => {
            if (result.isConfirmed) {
                $('#' + formId).submit();
            }
        });
    });

});
</script>
<?= $this->endSection() ?>
