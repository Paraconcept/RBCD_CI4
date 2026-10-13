<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;

$hasForfeit  = (bool) array_filter($matches, static fn ($m) => (int) $m->absent > 0);
$statusBadge = static fn (string $s, int $absent = 0): string => $absent ? '<span class="badge badge-dark"><i class="fas fa-user-slash mr-1"></i>Forfait</span>' : match ($s) {
    'live'      => '<span class="badge badge-danger"><i class="fas fa-circle mr-1"></i>En cours</span>',
    'scheduled' => '<span class="badge badge-info">Prévu</span>',
    'finished'  => '<span class="badge badge-success">Terminé</span>',
    default     => '<span class="badge badge-secondary">' . esc(M::STATUSES[$s] ?? $s) . '</span>',
};
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title">
      <i class="fas fa-users mr-2"></i><strong><?= esc($encounter->code) ?></strong>
      · <?= esc(TE::COMPETITIONS[$encounter->competition] ?? $encounter->competition) ?>
      <?= $encounter->scheduled_at ? ' · ' . date('d/m/Y H:i', strtotime($encounter->scheduled_at)) : '' ?>
      · <?= esc(M::disciplineLabel($encounter)) ?>
    </h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/edit') ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen mr-1"></i>Modifier</a>
    </div>
  </div>
  <div class="card-body">
    <h4 class="mb-0">
      <?= esc(TE::homeLabel($encounter)) ?> <small class="text-muted">contre</small> <?= esc(TE::awayLabel($encounter)) ?>
    </h4>
    <?php if ($encounter->notes): ?><p class="text-muted mt-2 mb-0"><?= nl2br(esc($encounter->notes)) ?></p><?php endif; ?>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-list-ol mr-2"></i>Matchs de la rencontre</h3>
    <div class="ml-auto d-flex">
      <div class="dropdown mr-1">
        <button class="btn btn-outline-danger btn-sm dropdown-toggle" type="button" data-toggle="dropdown"><i class="fas fa-user-slash mr-1"></i>Forfait complet</button>
        <div class="dropdown-menu dropdown-menu-right">
          <?php foreach (['away' => TE::awayLabel($encounter), 'home' => TE::homeLabel($encounter)] as $side => $label): ?>
          <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/forfeit') ?>"
                onsubmit="return confirm(<?= esc(json_encode('Déclarer le forfait complet de ' . $label . " ?\n\nTous les matchs non joués sont perdus par forfait (complétés jusqu'à 3 matchs).", JSON_UNESCAPED_UNICODE), 'attr') ?>)">
            <?= csrf_field() ?>
            <input type="hidden" name="side" value="<?= $side ?>">
            <button class="dropdown-item">Forfait de <strong><?= esc($label) ?></strong></button>
          </form>
          <?php endforeach; ?>
          <?php if ($hasForfeit): ?>
          <div class="dropdown-divider"></div>
          <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/forfeit/cancel') ?>" onsubmit="return confirm('Annuler tous les forfaits de cette rencontre ?')">
            <?= csrf_field() ?>
            <button class="dropdown-item text-danger"><i class="fas fa-undo mr-1"></i>Annuler les forfaits</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <a href="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/matches/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter un match</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!$matches): ?>
      <p class="text-muted text-center mb-0">Aucun match pour l'instant. Ajoutez-en un, ou rattachez un match déjà encodé ci-dessous.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="40">#</th>
          <th width="70">Heure</th>
          <th width="100">Billard</th>
          <th>Notre joueur</th>
          <th>Adversaire</th>
          <th width="90" class="text-center">Pts à jouer</th>
          <th width="90" class="text-center">Score</th>
          <th width="90" class="text-center">Reprises</th>
          <th width="90" class="text-center">Statut</th>
          <th width="150" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($matches as $i => $m): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= $m->scheduled_at ? date('H:i', strtotime($m->scheduled_at)) : '—' ?></td>
          <td><?= esc($m->table_name ?? '—') ?></td>
          <td class="<?= (int) $m->winner === 1 ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p1')) ?></td>
          <td class="<?= (int) $m->winner === 2 ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p2')) ?></td>
          <td class="text-center"><?= (int) $m->p1_distance ?> / <?= (int) $m->p2_distance ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' || $m->absent ? '—' : M::score($m, 'p1') . ' – ' . M::score($m, 'p2') . (M::isSets($m) ? ' sets' : '') ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' || $m->absent ? '—' : max((int) $m->p1_innings, (int) $m->p2_innings) ?></td>
          <td class="text-center"><?= $statusBadge($m->status, (int) $m->absent) ?></td>
          <td class="text-right text-nowrap">
            <?php if ($m->status !== 'scheduled' && !$m->absent): ?>
              <a href="<?= base_url('admin/scoreboard/' . $m->id) ?>" class="btn btn-xs btn-info" title="Feuille de match"><i class="fas fa-list-ol"></i></a>
              <a href="<?= base_url('admin/scoreboard/' . $m->id . '/pdf') ?>" target="_blank" class="btn btn-xs btn-primary" title="Feuille de match PDF"><i class="fas fa-file-pdf"></i></a>
            <?php endif; ?>
            <a href="<?= base_url('admin/scoreboard/' . $m->id . '/edit') ?>" class="btn btn-xs btn-warning" title="Modifier"><i class="fas fa-pen"></i></a>
            <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/detach/' . $m->id) ?>" class="d-inline"
                  onsubmit="return confirm('Détacher ce match de la rencontre ? Il reste disponible comme match individuel.')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-secondary" title="Détacher de la rencontre"><i class="fas fa-unlink"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <?php if ($attachable): ?>
    <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/attach') ?>" class="form-inline mt-3">
      <?= csrf_field() ?>
      <label class="mr-2 text-muted" for="match_id"><i class="fas fa-link mr-1"></i>Rattacher un match déjà encodé :</label>
      <select name="match_id" id="match_id" class="form-control form-control-sm mr-2" style="max-width:560px" required>
        <option value="">— Choisir un match —</option>
        <?php foreach ($attachable as $a): ?>
        <option value="<?= $a->id ?>">
          <?= esc(($a->scheduled_at ? date('d/m H:i', strtotime($a->scheduled_at)) . ' · ' : '') . M::playerLabel($a, 'p1') . ' – ' . M::playerLabel($a, 'p2') . ($a->competition ? ' · ' . $a->competition : '') . ' (' . (M::STATUSES[$a->status] ?? $a->status) . ')') ?>
        </option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-primary"><i class="fas fa-link mr-1"></i>Rattacher</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-flag-checkered mr-2"></i>Résultat de la rencontre</h3>
    <div class="ml-auto">
      <?php if ($result['complete']): ?>
      <a href="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/pdf') ?>" target="_blank" class="btn btn-sm btn-primary">
        <i class="fas fa-file-pdf mr-1"></i>Feuille récapitulative
      </a>
      <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/send') ?>" class="d-inline"
            onsubmit="return confirm(<?= esc(json_encode(($encounter->recap_sent_at ? 'La feuille a déjà été envoyée le ' . date('d/m/Y à H:i', strtotime($encounter->recap_sent_at)) . ".\n\n" : '') . "Envoyer la feuille récapitulative à :\n" . implode("\n", $recipients), JSON_UNESCAPED_UNICODE), 'attr') ?>)">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-success ml-1" <?= $recipients ? '' : 'disabled title="Aucune adresse e-mail encodée"' ?>>
          <i class="fas fa-paper-plane mr-1"></i><?= $encounter->recap_sent_at ? 'Renvoyer' : 'Envoyer' ?> par e-mail
        </button>
      </form>
      <?php else: ?>
      <button class="btn btn-sm btn-primary" disabled title="Disponible quand tous les matchs sont terminés">
        <i class="fas fa-file-pdf mr-1"></i>Feuille récapitulative
      </button>
      <button class="btn btn-sm btn-success ml-1" disabled title="Disponible quand tous les matchs sont terminés">
        <i class="fas fa-paper-plane mr-1"></i>Envoyer par e-mail
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php if ($encounter->recap_sent_at): ?>
  <div class="px-3 pt-3 mb-n2">
    <div class="alert alert-light border mb-0 py-2">
      <i class="fas fa-check-circle text-success mr-2"></i>Feuille récapitulative envoyée le
      <strong><?= date('d/m/Y à H:i', strtotime($encounter->recap_sent_at)) ?></strong> à <?= esc($encounter->recap_sent_to) ?>.
    </div>
  </div>
  <?php endif; ?>
  <div class="card-body">
    <?php if ($result['total'] === 0): ?>
      <p class="text-muted mb-0">En attente des matchs.</p>
    <?php else: ?>
      <?php if (!$result['complete']): ?>
      <p class="mb-3"><i class="fas fa-hourglass-half mr-2 text-muted"></i>Résultat provisoire : <strong><?= $result['finished'] ?> / <?= $result['total'] ?></strong> match<?= $result['total'] > 1 ? 's' : '' ?> terminé<?= $result['finished'] > 1 ? 's' : '' ?>.</p>
      <?php endif; ?>
      <?php foreach (['home' => TE::homeLabel($encounter), 'away' => TE::awayLabel($encounter)] as $side => $label): $t = $result['teams'][$side]; ?>
      <table class="table table-sm table-bordered mb-3">
        <thead class="thead-rbcd">
          <tr>
            <th><?= esc($label) ?></th>
            <th width="90" class="text-center">N° lic.</th>
            <th width="90" class="text-center">Pts à jouer</th>
            <th width="90" class="text-center">Pts réalisés</th>
            <th width="80" class="text-center">Reprises</th>
            <th width="90" class="text-center">Moyenne</th>
            <th width="70" class="text-center">Série</th>
            <th width="90" class="text-center">Pts match</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($t['rows'] as $r): ?>
          <tr>
            <td>
              <?php if ($r['absent']): ?><span class="badge badge-dark mr-1">ABSENT</span><?php endif; ?>
              <?= $r['name'] !== 'ABSENT' ? esc($r['name']) : '' ?>
              <?php if ($r['forfeit'] && !$r['absent']): ?><small class="text-muted ml-1">(forfait adverse)</small><?php endif; ?>
            </td>
            <td class="text-center"><?= esc($r['license'] ?? '') ?></td>
            <td class="text-center"><?= $r['distance'] ?: '—' ?></td>
            <td class="text-center"><?= $r['played'] ? $r['points'] : '—' ?></td>
            <td class="text-center"><?= $r['played'] ? $r['innings'] : '—' ?></td>
            <td class="text-center"><?= $r['played'] ? M::average($r['points'], $r['innings']) : '—' ?></td>
            <td class="text-center"><?= $r['played'] ? $r['high_run'] : '—' ?></td>
            <td class="text-center font-weight-bold"><?= $r['match_points'] ?? '—' ?></td>
          </tr>
          <?php endforeach; ?>
          <tr class="font-weight-bold" style="background:#f4f6f9">
            <td colspan="3" class="text-right">Équipe</td>
            <td class="text-center"><?= $t['points'] ?></td>
            <td class="text-center"><?= $t['innings'] ?></td>
            <td class="text-center"><?= M::average($t['points'], $t['innings']) ?></td>
            <td class="text-center"><?= $t['high_run'] ?></td>
            <td class="text-center" style="font-size:1.15rem"><?= $t['match_points'] ?></td>
          </tr>
        </tbody>
      </table>
      <?php endforeach; ?>
      <h5 class="mb-0 text-center">
        Résultat <?= $result['complete'] ? 'final' : 'provisoire' ?> :
        <strong><?= esc(TE::homeLabel($encounter)) ?> <?= $result['teams']['home']['match_points'] ?> / <?= $result['teams']['away']['match_points'] ?> <?= esc(TE::awayLabel($encounter)) ?></strong>
      </h5>
    <?php endif; ?>
  </div>
</div>

<div class="card card-outline card-primary" id="commentaire">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-comment-dots mr-2"></i>Commentaire pour la feuille récapitulative</h3>
  </div>
  <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/comment') ?>">
    <?= csrf_field() ?>
    <div class="card-body">
      <textarea name="recap_comment" class="form-control" rows="3" maxlength="1000"
                placeholder="Incident, réserve, remarque de l'arbitre… (imprimé sur la feuille récapitulative et repris dans l'e-mail)"><?= esc($encounter->recap_comment ?? '') ?></textarea>
    </div>
    <div class="card-footer text-right">
      <button class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>Enregistrer le commentaire</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
