<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;

$result  = $state['result'];
$home    = TE::homeLabel($encounter);
$away    = TE::awayLabel($encounter);
$query   = $billard ? '?billard=' . $billard : '';
$base    = base_url('marquoir/rencontre/' . $encounter->id);
$signed  = $signedAt !== null;
$toSign  = $encounter->recap_signature_required && !$signed;
$states  = [
    'scheduled' => ['far fa-clock text-secondary', 'À jouer'],
    'live'      => ['fas fa-play text-warning', 'En cours'],
    'to_sign'   => ['fas fa-signature text-warning', 'Terminé — feuille de match à signer'],
    'signed'    => ['fas fa-circle-check text-success', 'Terminé, signé'],
    'done'      => ['fas fa-circle-check text-success', 'Terminé'],
    'forfeit'   => ['fas fa-user-slash text-secondary', 'Forfait'],
];
?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc(TE::COMPETITIONS[$encounter->competition] ?? $encounter->competition) ?> · <?= esc($encounter->code) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title text-center mb-1"><?= esc($home) ?> <span class="text-secondary">–</span> <?= esc($away) ?></h1>
<p class="text-center text-secondary mb-3">Clôture de la rencontre · feuille récapitulative</p>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="sb-card mb-3">
            <table class="table table-sm table-borderless mb-0 sb-team-recap">
                <thead>
                    <tr><th></th><th><?= esc($home) ?></th><th>Pts</th><th>Rep</th><th>Moy</th><th>PM</th><th><?= esc($away) ?></th><th>Pts</th><th>Rep</th><th>Moy</th><th>PM</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($state['matches'] as $i => $item): $m = $item['match']; $h = $result['teams']['home']['rows'][$i]; $a = $result['teams']['away']['rows'][$i]; ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <?php foreach ([$h, $a] as $r): ?>
                        <td class="name"><?= esc($r['name']) ?><?= $r['absent'] ? ' <small class="text-secondary">(absent)</small>' : '' ?></td>
                        <td><?= $r['played'] ? $r['points'] . '/' . $r['distance'] : '–' ?></td>
                        <td><?= $r['played'] ? $r['innings'] : '–' ?></td>
                        <td><?= $r['played'] ? M::average($r['points'], $r['innings']) : '–' ?></td>
                        <td class="pm"><?= $r['match_points'] ?? '–' ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr class="state">
                        <td></td>
                        <td colspan="10">
                            <i class="<?= $states[$item['state']][0] ?> me-1"></i><?= $states[$item['state']][1] ?>
                            <?php if ($item['state'] === 'live' && $m->table_id): ?> · <?= esc($tables[(int) $m->table_id] ?? '') ?><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <?php foreach (['home', 'away'] as $side): $t = $result['teams'][$side]; ?>
                        <td>Total</td>
                        <td><?= $t['points'] ?></td>
                        <td><?= $t['innings'] ?></td>
                        <td><?= M::average($t['points'], $t['innings']) ?></td>
                        <td class="pm"><?= $t['match_points'] ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form method="post" action="<?= $base . '/commentaire' . $query ?>" class="sb-card">
            <label for="recapComment" class="form-label sb-sign-label">Commentaire de la feuille récapitulative</label>
            <?php if ($signed): ?>
            <p class="mb-0"><?= $encounter->recap_comment ? nl2br(esc($encounter->recap_comment)) : '<span class="text-secondary">Aucun commentaire.</span>' ?></p>
            <?php else: ?>
            <textarea name="recap_comment" id="recapComment" class="form-control mb-2" rows="2" maxlength="1000"
                      placeholder="Incident, réserve… (imprimé sur la feuille récapitulative)"><?= esc($encounter->recap_comment ?? '') ?></textarea>
            <button class="btn btn-outline-light btn-sm"><i class="fas fa-save me-1"></i>Enregistrer le commentaire</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="col-lg-6">
        <?php if (!$state['ready']): ?>
        <div class="sb-card mb-3 text-secondary">
            <i class="fas fa-hourglass-half me-2 sb-accent"></i>La clôture sera possible quand tous les matchs seront terminés,
            et leurs feuilles signées lorsque c'est demandé.
        </div>
        <?php elseif ($toSign): ?>
        <form method="post" id="signForm" action="<?= $base . '/signer' . $query ?>">
            <div class="sb-sign-label">
                <span>Signature : <b id="signWho"></b></span>
                <button type="button" class="btn btn-sm btn-outline-light" id="signClear"><i class="fas fa-eraser me-1"></i>Effacer</button>
            </div>
            <canvas class="sb-sign-pad mb-3" id="signPad"></canvas>
            <div class="d-flex flex-column gap-2">
                <?php foreach (['home' => $home, 'away' => $away] as $role => $label): ?>
                <button type="button" class="btn sb-sign-role" data-role="<?= $role ?>" data-who="Capitaine <?= esc($label) ?>">
                    <i class="far fa-circle sb-sign-check"></i>
                    <span>Capitaine<small><?= esc($label) ?></small></span>
                </button>
                <input type="hidden" name="<?= $role ?>_signature">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-sb btn-lg" id="signSubmit" disabled><i class="fas fa-signature me-2"></i>Enregistrer les signatures</button>
                <div class="text-secondary small">Une fois enregistrée, la feuille récapitulative est définitive.</div>
            </div>
        </form>
        <?php elseif ($signed): ?>
        <div class="sb-card mb-3">
            <span class="sbd-signed"><i class="fas fa-file-signature me-2"></i>Signée par les capitaines</span>
            <div class="text-secondary small mt-1">le <?= date('d/m/Y à H:i', strtotime($signedAt)) ?> — résultat définitif</div>
        </div>
        <?php else: ?>
        <div class="sb-card mb-3 text-secondary"><i class="fas fa-circle-check me-2 text-success"></i>Tous les matchs sont terminés. Signature des capitaines non demandée.</div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-3">
            <a href="<?= esc($back) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Billard</a>
            <?php if ($pdfReady): ?>
            <a href="<?= $base . '/pdf' . $query ?>" target="_blank" class="btn btn-sb btn-lg flex-fill"><i class="fas fa-file-pdf me-2"></i>Feuille récapitulative PDF</a>
            <?php else: ?>
            <button type="button" class="btn btn-sb btn-lg flex-fill" disabled><i class="fas fa-file-pdf me-2"></i>Feuille récapitulative PDF</button>
            <?php endif; ?>
        </div>
        <div class="text-secondary small mt-2">L'envoi par e-mail est fait par le directeur sportif.</div>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($state['ready'] && $toSign): ?>
<script src="<?= base_url('assets/scoreboard/signature-pad.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/signature-pad.js') ?>"></script>
<script>window.SbSignaturePad({ backUrl: <?= json_encode($back) ?> });</script>
<?php endif; ?>
<?= $this->endSection() ?>
