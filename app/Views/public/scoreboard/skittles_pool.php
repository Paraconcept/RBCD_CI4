<?php
use App\Models\ScoreboardSkittlesPoolModel as P;

$query  = $billard ? '?billard=' . $billard : '';
$base   = base_url('marquoir/poule-5-quilles/' . $pool->id);
$signed = $signedAt !== null;
$toSign = $pool->signature_required && !$signed;
$v      = static fn ($x): string => $x === null ? '–' : esc((string) $x);
$states = [
    null        => ['far fa-clock text-secondary', 'Manquant'],
    'scheduled' => ['far fa-clock text-secondary', 'À jouer'],
    'live'      => ['fas fa-play text-warning', 'En cours'],
    'finished'  => ['fas fa-circle-check text-success', 'Terminé'],
];
?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= P::NAME ?> · <?= esc($pool->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title text-center mb-1"><?= P::NAME ?> — <?= esc($pool->name) ?></h1>
<p class="text-center text-secondary mb-3">Clôture de la poule · feuille récapitulative</p>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="sb-card mb-3">
            <table class="table table-sm table-borderless mb-0 sb-team-recap">
                <thead>
                    <tr><th>Match</th><th></th><th>Sets</th><th>Pts</th><th>Moy</th><th>PM</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($sheet['matches'] as $no => $item): [$a, $b] = $item['players']; $t = $item['totals']; $st = $states[$item['status']] ?? $states[null]; ?>
                    <?php foreach ([$a, $b] as $i => $slot): ?>
                    <tr>
                        <td><?= $i === 0 ? $no . ' · ' . P::letter($a) . '/' . P::letter($b) : '' ?></td>
                        <td class="name"><b><?= P::letter($slot) ?></b> <?= esc(P::playerName($pool, $slot)) ?></td>
                        <td><?= $t ? $t[$slot]['sets'] : '–' ?></td>
                        <td><?= $t ? $t[$slot]['points'] : '–' ?></td>
                        <td><?= $t ? $t[$slot]['average'] : '–' ?></td>
                        <td class="pm"><?= $v($t[$slot]['match_points'] ?? null) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="state"><td></td><td colspan="5"><i class="<?= $st[0] ?> me-1"></i><?= $st[1] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="sb-card mb-3">
            <table class="table table-sm table-borderless mb-0 sb-team-recap">
                <thead><tr><th></th><th>Récapitulatif</th><th>Pts de match</th><th>Pts de set</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($sheet['recap'] as $slot => $r): ?>
                    <tr>
                        <td><b><?= P::letter($slot) ?></b></td>
                        <td class="name"><?= esc($r['name']) ?></td>
                        <td><?= $v($r['match_points']) ?></td>
                        <td><?= $v($r['set_points']) ?></td>
                        <td class="pm"><?= $v($r['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form method="post" action="<?= $base . '/commentaire' . $query ?>" class="sb-card">
            <label for="poolComment" class="form-label sb-sign-label">Commentaire de la feuille récapitulative</label>
            <?php if ($signed): ?>
            <p class="mb-0"><?= $pool->comment ? nl2br(esc($pool->comment)) : '<span class="text-secondary">Aucun commentaire.</span>' ?></p>
            <?php else: ?>
            <textarea name="comment" id="poolComment" class="form-control mb-2" rows="2" maxlength="1000"
                      placeholder="Incident, réserve… (imprimé sur la feuille récapitulative)"><?= esc($pool->comment ?? '') ?></textarea>
            <button class="btn btn-outline-light btn-sm"><i class="fas fa-save me-1"></i>Enregistrer le commentaire</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="col-lg-6">
        <?php if (!$sheet['complete']): ?>
        <div class="sb-card mb-3 text-secondary">
            <i class="fas fa-hourglass-half me-2 sb-accent"></i>La clôture sera possible quand les trois matchs seront terminés.
        </div>
        <?php elseif ($toSign): ?>
        <form method="post" id="signForm" action="<?= $base . '/signer' . $query ?>">
            <div class="sb-sign-label">
                <span>Signature : <b id="signWho"></b></span>
                <button type="button" class="btn btn-sm btn-outline-light" id="signClear"><i class="fas fa-eraser me-1"></i>Effacer</button>
            </div>
            <canvas class="sb-sign-pad mb-3" id="signPad"></canvas>
            <div class="d-flex flex-column gap-2">
                <?php foreach (P::SLOTS as $slot => $letter): $k = P::key($slot); ?>
                <button type="button" class="btn sb-sign-role" data-role="<?= $k ?>" data-who="Joueur <?= $letter ?> — <?= esc(P::playerName($pool, $slot)) ?>">
                    <i class="far fa-circle sb-sign-check"></i>
                    <span>Joueur <?= $letter ?><small><?= esc(P::playerName($pool, $slot)) ?></small></span>
                </button>
                <input type="hidden" name="<?= $k ?>_signature">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-sb btn-lg" id="signSubmit" disabled><i class="fas fa-signature me-2"></i>Enregistrer les signatures</button>
                <div class="text-secondary small">Signatures pour accord. Une fois enregistrée, la feuille récapitulative est définitive.</div>
            </div>
        </form>
        <?php elseif ($signed): ?>
        <div class="sb-card mb-3">
            <span class="sbd-signed"><i class="fas fa-file-signature me-2"></i>Signée par les trois joueurs</span>
            <div class="text-secondary small mt-1">le <?= date('d/m/Y à H:i', strtotime($signedAt)) ?> — résultat définitif</div>
        </div>
        <?php else: ?>
        <div class="sb-card mb-3 text-secondary"><i class="fas fa-circle-check me-2 text-success"></i>Les trois matchs sont terminés. Signature des joueurs non demandée.</div>
        <?php endif; ?>

        <?php if ($mail['state'] === 'sent'): ?>
        <div class="sb-encounter-mail ok"><i class="fas fa-envelope-circle-check me-2"></i>Feuille récapitulative envoyée à <?= $mail['at'] ?></div>
        <?php elseif ($mail['state'] === 'error'): ?>
        <div class="sb-encounter-mail ko"><i class="fas fa-triangle-exclamation me-2"></i>Échec de l'envoi de la feuille récapitulative : prévenez le DS</div>
        <?php elseif ($mail['state'] === 'waiting'): ?>
        <div class="sb-encounter-mail wait"><i class="fas fa-hourglass-half me-2"></i>Envoi après les signatures, au plus tard à <?= $mail['due'] ?></div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-3">
            <a href="<?= esc($back) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Billard</a>
            <?php if ($pdfReady): ?>
            <a href="<?= $base . '/pdf' . $query ?>" target="_blank" class="btn btn-sb btn-lg flex-fill"><i class="fas fa-file-pdf me-2"></i>Feuille récapitulative PDF</a>
            <?php else: ?>
            <button type="button" class="btn btn-sb btn-lg flex-fill" disabled><i class="fas fa-file-pdf me-2"></i>Feuille récapitulative PDF</button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($sheet['complete'] && $toSign): ?>
<script src="<?= base_url('assets/scoreboard/signature-pad.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/signature-pad.js') ?>"></script>
<script>window.SbSignaturePad({ backUrl: <?= json_encode($back) ?> });</script>
<?php endif; ?>
<?= $this->endSection() ?>
