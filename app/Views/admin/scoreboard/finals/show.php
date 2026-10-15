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

<div class="card card-outline card-primary" id="ordre">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-list-ol mr-2"></i>Ordre des matchs</h3></div>
  <div class="card-body p-0">
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="30" class="text-center"><i class="fas fa-arrows-alt-v"></i></th><th width="40">#</th><th width="110">Billard</th><th>Joueur</th><th>Joueur</th>
          <th width="100" class="text-center">Pts à jouer</th><th width="90" class="text-center">Score</th>
          <th width="80" class="text-center">Reprises</th><th width="90" class="text-center">Statut</th><th width="90" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="match-order">
      <?php foreach ($matches as $m): ?>
        <tr data-id="<?= (int) $m->id ?>">
          <td class="text-center align-middle sb-drag" title="Glisser pour changer l'ordre"><i class="fas fa-grip-vertical text-muted"></i></td>
          <td><?= (int) $m->final_order ?></td>
          <td><?= esc($m->table_name ?? 'au choix') ?></td>
          <td class="<?= (int) $m->winner === 1 && $m->status === 'finished' ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p1')) ?></td>
          <td class="<?= (int) $m->winner === 2 && $m->status === 'finished' ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p2')) ?></td>
          <td class="text-center"><?= (int) $m->p1_distance ?> / <?= (int) $m->p2_distance ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' ? '—' : (int) $m->p1_points . ' – ' . (int) $m->p2_points ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' ? '—' : max((int) $m->p1_innings, (int) $m->p2_innings) ?></td>
          <td class="text-center"><?= $statusBadge($m->status) ?></td>
          <td class="text-right text-nowrap">
            <?php if ($m->status === 'scheduled'): ?>
              <button type="button" class="btn btn-xs btn-warning" title="Modifier le match" data-toggle="modal" data-target="#match-edit"
                      data-action="<?= base_url('admin/scoreboard/finals/' . $final->id . '/matches/' . $m->id) ?>"
                      data-title="Match n°<?= (int) $m->final_order ?> : <?= esc(M::playerLabel($m, 'p1')) ?> – <?= esc(M::playerLabel($m, 'p2')) ?>"
                      data-p1="<?= esc(M::playerLabel($m, 'p1')) ?>" data-p2="<?= esc(M::playerLabel($m, 'p2')) ?>"
                      data-table="<?= (int) $m->table_id ?>" data-p1-distance="<?= (int) $m->p1_distance ?>" data-p2-distance="<?= (int) $m->p2_distance ?>"
                      data-date="<?= $m->scheduled_at ? date('Y-m-d', strtotime($m->scheduled_at)) : '' ?>"
                      data-time="<?= $m->scheduled_at ? date('H:i', strtotime($m->scheduled_at)) : '09:00' ?>"><i class="fas fa-pen"></i></button>
            <?php else: ?>
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
    <i class="fas fa-arrows-alt-v mr-1"></i>Glissez une ligne par la poignée pour changer l'ordre (ex. faire jouer d'abord deux joueurs du même club) ;
    il est enregistré aussitôt et les billards des matchs à jouer suivent la nouvelle position.<br>
    <i class="fas fa-pen mr-1"></i>Le bouton <span class="badge badge-warning"><i class="fas fa-pen"></i></span> change le billard, l'heure ou les points à jouer d'un match à jouer
    (un billard choisi à la main est redistribué si l'on change ensuite l'ordre : réglez l'ordre d'abord).<br>
    <i class="fas fa-tv mr-1"></i>Le jour même, chaque match apparaît sur la page de son billard (marquoir) avec « Démarrer », dans cet ordre.
  </div>
  <form method="post" id="match-order-form" action="<?= base_url('admin/scoreboard/finals/' . $final->id . '/reorder') ?>" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="order">
  </form>
</div>

<div class="modal fade" id="match-edit" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" data-field="title"></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="me_table">Billard</label>
          <select name="table_id" id="me_table" class="form-control">
            <option value="0">Au choix</option>
            <?php foreach ($allTables as $t): ?>
            <option value="<?= (int) $t->id ?>"><?= esc($t->name) ?> (<?= esc($t->format ?? 'PB') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="me_date">Date et heure</label>
          <div class="d-flex">
            <input type="date" name="scheduled_date" id="me_date" class="form-control mr-2">
            <input type="time" name="scheduled_time" class="form-control" style="max-width:120px">
          </div>
        </div>
        <div class="row">
          <div class="col-6 form-group mb-0">
            <label for="me_p1" data-field="p1"></label>
            <input type="number" name="p1_distance" id="me_p1" class="form-control" min="1" max="9999" required>
          </div>
          <div class="col-6 form-group mb-0">
            <label for="me_p2" data-field="p2"></label>
            <input type="number" name="p2_distance" id="me_p2" class="form-control" min="1" max="9999" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
        <button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
      </div>
    </form>
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
<?= $this->section('scripts') ?>
<style>
  .sb-drag { cursor: grab; }
  .sb-ghost { background: #f8e9ea !important; opacity: .7; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
(function () {
    const body = document.getElementById('match-order');
    const form = document.getElementById('match-order-form');
    if (!body || !form || !window.Sortable) return;
    Sortable.create(body, {
        handle: '.sb-drag',
        animation: 150,
        ghostClass: 'sb-ghost',
        onEnd: function (e) {
            if (e.oldIndex === e.newIndex) return;
            form.elements.order.value = [...body.querySelectorAll('tr[data-id]')].map((tr) => tr.dataset.id).join(',');
            form.submit();
        }
    });
})();

$('#match-edit').on('show.bs.modal', function (e) {
    const d = e.relatedTarget.dataset;
    const f = this.querySelector('form');
    f.action = d.action;
    this.querySelector('[data-field="title"]').textContent = d.title;
    this.querySelector('[data-field="p1"]').textContent = 'Pts à jouer — ' + d.p1;
    this.querySelector('[data-field="p2"]').textContent = 'Pts à jouer — ' + d.p2;
    f.elements.table_id.value = d.table || '0';
    if (!f.elements.table_id.value) f.elements.table_id.value = '0';
    f.elements.scheduled_date.value = d.date;
    f.elements.scheduled_time.value = d.time;
    f.elements.p1_distance.value = d.p1Distance;
    f.elements.p2_distance.value = d.p2Distance;
});
</script>
<?= $this->endSection() ?>
