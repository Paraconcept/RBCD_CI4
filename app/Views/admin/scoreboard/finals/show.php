<?php
use App\Models\ScoreboardMatchModel as M;

$total    = count($matches);
$finished = count(array_filter($matches, static fn ($m) => $m->status === 'finished'));
$complete = $total > 0 && $finished === $total;
$fmt      = static fn (?float $v): string => $v === null ? '—' : number_format($v, 3, ',', '');
$statusBadge = static fn (string $s): string => match ($s) {
    'live'      => '<span class="badge badge-danger"><i class="fas fa-circle mr-1"></i>En cours</span>',
    'scheduled' => '<span class="badge badge-info">À jouer</span>',
    'finished'  => '<span class="badge badge-success">Terminé</span>',
    default     => '<span class="badge badge-secondary">' . esc(M::STATUSES[$s] ?? $s) . '</span>',
};
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title">
      <i class="fas fa-trophy mr-2"></i><strong><?= esc($final->name) ?></strong>
      <?= $final->scheduled_at ? ' · ' . date('d/m/Y H:i', strtotime($final->scheduled_at)) : '' ?>
      · <?= esc(M::disciplineLabel($final)) ?>
    </h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/finals/' . $final->id . '/edit') ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen mr-1"></i>Modifier</a>
      <a href="<?= base_url('admin/scoreboard/finals/' . $final->id . '/pdf') ?>" target="_blank" class="btn btn-primary btn-sm ml-1">
        <i class="fas fa-file-pdf mr-1"></i>Feuille récapitulative<?= $complete ? '' : ' (provisoire)' ?>
      </a>
    </div>
  </div>
  <div class="card-body py-2 text-muted">
    <?= count($players) ?> joueurs · <?= $total ?> matchs
    <?= $final->innings_limit ? ' · limite ' . (int) $final->innings_limit . ' reprises' : '' ?>
    <?= $final->unequal_innings ? ' · reprises inégales' : ' · reprise d\'égalisation' ?>
    · billards : <?= $tables ? esc(implode(', ', $tables)) : 'au choix' ?>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-medal mr-2"></i>Classement <?= $complete ? 'final' : 'provisoire' ?></h3>
    <span class="ml-auto text-muted small"><?= $finished ?> / <?= $total ?> match<?= $total > 1 ? 's' : '' ?> terminé<?= $finished > 1 ? 's' : '' ?></span>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm table-bordered mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="50" class="text-center">Rang</th><th>Joueur</th><th class="text-center">Cat.</th><th class="text-center">Pts à jouer</th>
          <th class="text-center">J</th><th class="text-center">G</th><th class="text-center">N</th><th class="text-center">P</th>
          <th class="text-center">Pts match</th><th class="text-center">Points</th><th class="text-center">Reprises</th>
          <th class="text-center">Moyenne</th><th class="text-center">Moy. prop.</th><th class="text-center">MS</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($standings as $r): $p = $r['player']; ?>
        <tr class="<?= $complete && $r['rank'] === 1 ? 'font-weight-bold' : '' ?>">
          <td class="text-center"><?= $r['rank'] ?><?= $complete && $r['rank'] === 1 ? ' <i class="fas fa-trophy text-warning"></i>' : '' ?></td>
          <td><?= esc(trim($p->last_name . ' ' . $p->first_name)) ?> <small class="text-muted"><?= esc($p->club ?? '') ?></small></td>
          <td class="text-center"><?= esc($p->category ?? '—') ?></td>
          <td class="text-center"><?= (int) $p->distance ?></td>
          <td class="text-center"><?= $r['played'] ?></td>
          <td class="text-center"><?= $r['won'] ?></td>
          <td class="text-center"><?= $r['drawn'] ?></td>
          <td class="text-center"><?= $r['lost'] ?></td>
          <td class="text-center font-weight-bold" style="font-size:1.1rem"><?= $r['match_points'] ?></td>
          <td class="text-center"><?= $r['points'] ?></td>
          <td class="text-center"><?= $r['innings'] ?></td>
          <td class="text-center"><?= number_format($r['average'], 3, ',', '') ?></td>
          <td class="text-center"><?= $fmt($r['proportional']) ?></td>
          <td class="text-center"><?= $r['high_run'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer text-muted small">
    <i class="fas fa-balance-scale mr-1"></i>2 points de match pour une victoire, 1 pour un nul, 0 pour une défaite.
    Égalité : moyenne proportionnelle (moyenne ÷ moyenne minimum de la catégorie), puis meilleure série, puis match entre les ex æquo.
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-th mr-2"></i>Grille des résultats</h3></div>
  <div class="card-body p-0 table-responsive">
    <table class="table table-sm table-bordered text-center mb-0">
      <thead class="thead-rbcd">
        <tr><th class="text-left">Joueur</th><?php foreach ($players as $i => $p): ?><th><?= $i + 1 ?>. <?= esc($p->last_name) ?></th><?php endforeach; ?></tr>
      </thead>
      <tbody>
      <?php foreach ($players as $i => $a): ?>
        <tr>
          <th class="text-left"><?= $i + 1 ?>. <?= esc(trim($a->last_name . ' ' . $a->first_name)) ?></th>
          <?php foreach ($players as $b): ?>
            <?php if ((int) $a->id === (int) $b->id): ?>
              <td style="background:#e9ecef"></td>
            <?php else: $cell = $grid[(int) $a->id][(int) $b->id] ?? null; $m = $cell['match'] ?? null; ?>
              <td>
                <?php if ($m && $m->status === 'finished'):
                    $n = $cell['side']; $o = 3 - $n; $w = (int) $m->winner; $mp = $w === 0 ? 1 : ($w === $n ? 2 : 0); ?>
                  <strong class="<?= $mp === 2 ? 'text-success' : ($mp === 0 ? 'text-danger' : '') ?>"><?= $mp ?></strong>
                  <small class="text-muted d-block"><?= (int) $m->{'p' . $n . '_points'} ?> – <?= (int) $m->{'p' . $o . '_points'} ?> (<?= max((int) $m->p1_innings, (int) $m->p2_innings) ?> rep.)</small>
                <?php elseif ($m && $m->status === 'live'): ?>
                  <span class="badge badge-danger">en cours</span>
                <?php else: ?>
                  <span class="text-muted">·</span>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer text-muted small">Points de match du joueur de la ligne contre celui de la colonne, puis son score.</div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-list-ol mr-2"></i>Ordre des matchs</h3></div>
  <div class="card-body p-0">
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="40">#</th><th width="110">Billard</th><th>Joueur</th><th>Joueur</th>
          <th width="100" class="text-center">Pts à jouer</th><th width="90" class="text-center">Score</th>
          <th width="80" class="text-center">Reprises</th><th width="90" class="text-center">Statut</th><th width="90" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($matches as $m): ?>
        <tr>
          <td><?= (int) $m->final_order ?></td>
          <td><?= esc($m->table_name ?? 'au choix') ?></td>
          <td class="<?= (int) $m->winner === 1 && $m->status === 'finished' ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p1')) ?></td>
          <td class="<?= (int) $m->winner === 2 && $m->status === 'finished' ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p2')) ?></td>
          <td class="text-center"><?= (int) $m->p1_distance ?> / <?= (int) $m->p2_distance ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' ? '—' : (int) $m->p1_points . ' – ' . (int) $m->p2_points ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' ? '—' : max((int) $m->p1_innings, (int) $m->p2_innings) ?></td>
          <td class="text-center"><?= $statusBadge($m->status) ?></td>
          <td class="text-right text-nowrap">
            <?php if ($m->status !== 'scheduled'): ?>
              <a href="<?= base_url('admin/scoreboard/' . $m->id) ?>" class="btn btn-xs btn-info" title="Feuille de match"><i class="fas fa-list-ol"></i></a>
              <a href="<?= base_url('admin/scoreboard/' . $m->id . '/pdf') ?>" target="_blank" class="btn btn-xs btn-primary" title="Feuille de match PDF"><i class="fas fa-file-pdf"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer text-muted small">
    <i class="fas fa-tv mr-1"></i>Le jour même, chaque match apparaît sur la page de son billard (marquoir) avec « Démarrer », dans cet ordre.
  </div>
</div>

<div class="card card-outline card-primary" id="commentaire">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-comment-dots mr-2"></i>Commentaire pour la feuille récapitulative</h3></div>
  <form method="post" action="<?= base_url('admin/scoreboard/finals/' . $final->id . '/comment') ?>">
    <?= csrf_field() ?>
    <div class="card-body">
      <textarea name="comment" class="form-control" rows="3" maxlength="1000"
                placeholder="Incident, réserve, remarque de l'arbitre… (imprimé sur la feuille récapitulative)"><?= esc($final->comment ?? '') ?></textarea>
    </div>
    <div class="card-footer text-right">
      <button class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>Enregistrer le commentaire</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
