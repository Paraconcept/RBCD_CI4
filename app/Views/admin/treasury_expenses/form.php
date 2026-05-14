<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $expense !== null; ?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas <?= $isEdit ? 'fa-pencil-alt' : 'fa-plus' ?> mr-2"></i>
            <?= $isEdit ? 'Modifier la dépense' : 'Nouvelle dépense' ?>
        </h3>
    </div>

    <form method="POST" action="<?= $isEdit
        ? base_url('admin/treasury/expenses/' . $expense->id . '/update')
        : base_url('admin/treasury/expenses') ?>">
        <?= csrf_field() ?>

        <div class="card-body">

            <?php if ($errors = session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Date <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" class="form-control" required
                           value="<?= esc(old('expense_date', $isEdit ? $expense->expense_date : date('Y-m-d'))) ?>">
                </div>

                <div class="col-md-4 form-group">
                    <label>Catégorie <span class="text-danger">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="">— Choisir —</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= $key ?>" <?= old('category', $isEdit ? $expense->category : '') === $key ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Montant (€) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="amount" class="form-control" required
                               min="0.01" step="0.01" placeholder="0,00"
                               value="<?= esc(old('amount', $isEdit ? number_format($expense->amount, 2, '.', '') : '')) ?>">
                        <div class="input-group-append"><span class="input-group-text">€</span></div>
                    </div>
                </div>

                <div class="col-md-2 form-group">
                    <label>Paiement</label>
                    <select name="payment_method" class="form-control">
                        <?php foreach ($paymentMethods as $key => $label): ?>
                            <option value="<?= $key ?>" <?= old('payment_method', $isEdit ? $expense->payment_method : 'caisse') === $key ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 form-group">
                    <label>Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control" required
                           maxlength="255" placeholder="ex: Achat d'une nouvelle imprimante"
                           value="<?= esc(old('description', $isEdit ? $expense->description : '')) ?>">
                </div>

                <div class="col-md-4 form-group">
                    <label>Notes internes</label>
                    <input type="text" name="notes" class="form-control"
                           placeholder="Remarque optionnelle"
                           value="<?= esc(old('notes', $isEdit ? $expense->notes ?? '' : '')) ?>">
                </div>
            </div>

        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Enregistrer la dépense' ?>
            </button>
            <a href="<?= base_url('admin/treasury/expenses') ?>" class="btn btn-secondary ml-2">Annuler</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
