<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardSkittlesPoolModel as P;

$statusBadge = static fn (?string $s): string => match ($s) {
    'live'      => '<span class="badge badge-danger"><i class="fas fa-circle mr-1"></i>En cours</span>',
    'scheduled' => '<span class="badge badge-info">Prévu</span>',
    'finished'  => '<span class="badge badge-success">Terminé</span>',
    null        => '<span class="badge badge-secondary">Manquant</span>',
    default     => '<span class="badge badge-secondary">' . esc(M::STATUSES[$s] ?? $s) . '</span>',
};
$v = static fn ($x): string => $x === null ? '—' : esc((string) $x);
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title">
      <i class="fas fa-chess-pawn mr-2"></i><strong><?= esc($pool->name) ?></strong>
      <?= $pool->scheduled_at ? ' · ' . date('d/m/Y H:i', strtotime($pool->scheduled_at)) : '' ?>
      · <?= esc($table->name ?? "n'importe quel billard") ?>
      · <?= esc(P::BILLIARDS[$pool->billiard]['label'] ?? '') ?>, <?= (int) $pool->set_points ?> pts par set
    </h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/skittles/' . $pool->id . '/edit') ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen mr-1"></i>Modifier</a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm table-bordered mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="40">#</th>
          <th width="80" class="text-center">Joueurs</th>
          <th>Match</th>
          <th width="110" class="text-center">Sets</th>
          <th width="160" class="text-center">Pts réalisés</th>
          <th width="160" class="text-center">Moyennes</th>
          <th width="130" class="text-center">Pts de match</th>
          <th width="90" class="text-center">Statut</th>
          <th width="110" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($sheet['matches'] as $no => $item): [$a, $b] = $item['players']; $m = $item['match']; $t = $item['totals']; ?>
        <tr>
          <td><?= $no ?></td>
          <td class="text-center font-weight-bold"><?= P::letter($a) ?> / <?= P::letter($b) ?></td>
          <td><?= esc(P::playerName($pool, $a)) ?> <span class="text-muted">contre</span> <?= esc(P::playerName($pool, $b)) ?></td>
          <td class="text-center"><?= $t ? $t[$a]['sets'] . ' – ' . $t[$b]['sets'] : '—' ?></td>
          <td class="text-center"><?= $t ? $t[$a]['points'] . ' – ' . $t[$b]['points'] : '—' ?></td>
          <td class="text-center"><?= $t ? $t[$a]['average'] . ' – ' . $t[$b]['average'] : '—' ?></td>
          <td class="text-center font-weight-bold"><?= $t && $t[$a]['match_points'] !== null ? $t[$a]['match_points'] . ' – ' . $t[$b]['match_points'] : '—' ?></td>
          <td class="text-center">
            <?php if ($item['absent']): ?>
              <span class="badge badge-dark"><i class="fas fa-user-slash mr-1"></i>Forfait <?= implode(' + ', array_map([P::class, 'letter'], $item['absent'])) ?></span>
            <?php else: ?>
              <?= $statusBadge($item['status']) ?>
            <?php endif; ?>
          </td>
          <td class="text-right text-nowrap">
            <?php if ($m && $item['absent']): ?>
              <form method="post" action="<?= base_url('admin/scoreboard/skittles/' . $pool->id . '/forfeit/' . $m->id . '/cancel') ?>" class="d-inline"
                    onsubmit="return confirm('Annuler le forfait ? Le match redevient à jouer.')">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-secondary" title="Annuler le forfait"><i class="fas fa-undo mr-1"></i>Forfait</button>
              </form>
            <?php elseif ($m && $m->status === 'scheduled'): ?>
              <div class="dropdown d-inline">
                <button class="btn btn-xs btn-outline-danger dropdown-toggle" type="button" data-toggle="dropdown" title="Déclarer un forfait"><i class="fas fa-user-slash"></i></button>
                <div class="dropdown-menu dropdown-menu-right">
                  <?php foreach ([$a, $b] as $slot): ?>
                  <form method="post" action="<?= base_url('admin/scoreboard/skittles/' . $pool->id . '/forfeit/' . $m->id) ?>"
                        onsubmit="return confirm(<?= esc(json_encode('Forfait de ' . P::playerName($pool, $slot) . ' (joueur ' . P::letter($slot) . ") au match n°{$no} ?\n\nIl a 0 partout sur ce match ; son adversaire reçoit " . P::FORFEIT_POINTS . ' points de match et aucun point de set.', JSON_UNESCAPED_UNICODE), 'attr') ?>)">
                    <?= csrf_field() ?>
                    <input type="hidden" name="slot" value="<?= $slot ?>">
                    <button class="dropdown-item">Forfait de <strong><?= P::letter($slot) ?></strong> — <?= esc(P::playerName($pool, $slot)) ?></button>
                  </form>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php elseif ($m && $m->status !== 'scheduled'): ?>
              <a href="<?= base_url('admin/scoreboard/' . $m->id) ?>" class="btn btn-xs btn-info" title="Détail des coups"><i class="fas fa-list-ol"></i></a>
              <form method="post" action="<?= base_url('admin/scoreboard/' . $m->id . '/reset') ?>" class="d-inline"
                    onsubmit="return confirm('Remettre ce match à zéro ? Tous les coups joués sont effacés<?= $signedAt ? ', ainsi que les signatures de la feuille récapitulative' : '' ?>.')">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-danger" title="Remettre à zéro"><i class="fas fa-undo"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-flag-checkered mr-2"></i>Récapitulatif des points</h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/skittles/' . $pool->id . '/pdf') ?>" target="_blank" class="btn btn-sm btn-primary">
        <i class="fas fa-file-pdf mr-1"></i>Feuille récapitulative<?= $sheet['complete'] ? '' : ' (provisoire)' ?>
      </a>
      <form method="post" action="<?= base_url('admin/scoreboard/skittles/' . $pool->id . '/send') ?>" class="d-inline"
            onsubmit="return confirm(<?= esc(json_encode(($pool->sent_at ? 'La feuille a déjà été envoyée le ' . date('d/m/Y à H:i', strtotime($pool->sent_at)) . ".\n\n" : '') . "Envoyer la feuille récapitulative à :\n" . implode("\n", $recipients), JSON_UNESCAPED_UNICODE), 'attr') ?>)">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-success ml-1" <?= $sheet['complete'] && $recipients ? '' : 'disabled title="Disponible quand les trois matchs sont terminés et un destinataire coché"' ?>>
          <i class="fas fa-paper-plane mr-1"></i><?= $pool->sent_at ? 'Renvoyer' : 'Envoyer' ?> par e-mail
        </button>
      </form>
    </div>
  </div>
  <div class="px-3 pt-3 mb-n2">
    <div class="alert alert-light border mb-2 py-2">
      <?php if ($signedAt): ?>
      <i class="fas fa-file-signature text-success mr-2"></i>Feuille récapitulative signée par les trois joueurs le <strong><?= date('d/m/Y à H:i', strtotime($signedAt)) ?></strong>.
      <?php elseif ($pool->signature_required): ?>
      <i class="fas fa-signature text-muted mr-2"></i>Feuille récapitulative non signée (signatures au marquoir, page du billard, après les trois matchs).
      <?php else: ?>
      <i class="fas fa-signature text-muted mr-2"></i>Signature des joueurs non demandée.
      <?php endif; ?>
    </div>
    <?php if ($pool->sent_at): ?>
    <div class="alert alert-light border mb-2 py-2">
      <i class="fas fa-check-circle text-success mr-2"></i>Envoyée le <strong><?= date('d/m/Y à H:i', strtotime($pool->sent_at)) ?></strong> à <?= esc($pool->sent_to) ?>.
    </div>
    <?php elseif ($pool->send_error): ?>
    <div class="alert alert-danger mb-2 py-2">
      <i class="fas fa-exclamation-triangle mr-2"></i>Échec de l'envoi automatique (<?= esc($pool->send_error) ?>) : utilisez « Envoyer par e-mail ».
    </div>
    <?php elseif ($recipients): ?>
    <div class="alert alert-light border mb-2 py-2 text-muted">
      <i class="fas fa-paper-plane mr-2"></i>Envoi automatique à <?= esc(implode(', ', $recipients)) ?> dès que les trois matchs sont terminés
      <?= $pool->signature_required ? 'et la feuille signée (ou ' . \App\Libraries\MatchResultMailer::AUTO_DELAY . ' minutes après le dernier match)' : '' ?>.
    </div>
    <?php else: ?>
    <div class="alert alert-warning mb-2 py-2"><i class="fas fa-exclamation-triangle mr-2"></i>Aucun destinataire coché : la feuille ne sera pas envoyée.</div>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <table class="table table-sm table-bordered mb-0" style="max-width:720px">
      <thead class="thead-rbcd">
        <tr><th width="60" class="text-center">Joueur</th><th>Nom</th><th width="120" class="text-center">Pts de match</th><th width="120" class="text-center">Pts de set</th><th width="100" class="text-center">Total</th></tr>
      </thead>
      <tbody>
      <?php foreach ($sheet['recap'] as $slot => $r): ?>
        <tr>
          <td class="text-center font-weight-bold"><?= P::letter($slot) ?></td>
          <td><?= esc($r['name']) ?> <small class="text-muted"><?= esc($pool->{P::key($slot) . '_club'} ?? '') ?></small></td>
          <td class="text-center"><?= $v($r['match_points']) ?></td>
          <td class="text-center"><?= $v($r['set_points']) ?></td>
          <td class="text-center font-weight-bold"><?= $v($r['total']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <small class="text-muted">Pas de classement : ordre A, B, C. Total = points de match + points de set. Forfait : l'absent a 0 partout, son adversaire <?= P::FORFEIT_POINTS ?> points de match sans point de set. Moyenne = points réalisés / points de l'adversaire, tronquée à 3 décimales.</small>
  </div>
</div>

<div class="card card-outline card-primary" id="commentaire">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-comment-dots mr-2"></i>Commentaire pour la feuille récapitulative</h3>
  </div>
  <form method="post" action="<?= base_url('admin/scoreboard/skittles/' . $pool->id . '/comment') ?>">
    <?= csrf_field() ?>
    <div class="card-body">
      <textarea name="comment" class="form-control" rows="3" maxlength="1000"
                placeholder="Incident, réserve, remarque… (imprimé sur la feuille récapitulative et repris dans l'e-mail)"><?= esc($pool->comment ?? '') ?></textarea>
    </div>
    <div class="card-footer text-right">
      <button class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>Enregistrer le commentaire</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
