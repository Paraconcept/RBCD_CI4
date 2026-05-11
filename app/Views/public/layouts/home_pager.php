<?php
$pager->setSurroundCount(2);
$links = $pager->links();
$cur   = $pager->getCurrentPage();
$total = $pager->getPageCount();
$first = $pager->getFirst();
$last  = $pager->getLast();
$prev  = $cur > 1     ? preg_replace('/page=\d+/', 'page=' . ($cur - 1), $first) : null;
$next  = $cur < $total ? preg_replace('/page=\d+/', 'page=' . ($cur + 1), $first) : null;
?>

<?php if (true): ?>
<style>
.home-pager .page-link {
    color: #202C45;
}
.home-pager .page-item.active .page-link {
    background: transparent;
    border-color: #202C45;
    color: #202C45;
    font-weight: 700;
    cursor: default;
    pointer-events: none;
}
.home-pager .page-item:not(.active):not(.disabled) .page-link:hover {
    background-color: #202C45;
    border-color: #202C45;
    color: #fff;
}
</style>
<nav aria-label="Navigation des actualités" class="mt-20">
  <ul class="pagination home-pager">

    <li class="page-item <?= $prev === null ? 'disabled' : '' ?>">
      <?= $prev ? '<a class="page-link" href="' . $first . '#actualites">&laquo;</a>' : '<span class="page-link">&laquo;</span>' ?>
    </li>
    <li class="page-item <?= $prev === null ? 'disabled' : '' ?>">
      <?= $prev ? '<a class="page-link" href="' . $prev . '#actualites">&lsaquo;</a>' : '<span class="page-link">&lsaquo;</span>' ?>
    </li>

    <?php foreach ($links as $link): ?>
    <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
      <?php if ($link['active']): ?>
      <span class="page-link"><?= $link['title'] ?></span>
      <?php else: ?>
      <a class="page-link" href="<?= esc($link['uri']) ?>#actualites"><?= $link['title'] ?></a>
      <?php endif ?>
    </li>
    <?php endforeach ?>

    <li class="page-item <?= $next === null ? 'disabled' : '' ?>">
      <?= $next ? '<a class="page-link" href="' . $next . '#actualites">&rsaquo;</a>' : '<span class="page-link">&rsaquo;</span>' ?>
    </li>
    <li class="page-item <?= $next === null ? 'disabled' : '' ?>">
      <?= $next ? '<a class="page-link" href="' . $last . '#actualites">&raquo;</a>' : '<span class="page-link">&raquo;</span>' ?>
    </li>

  </ul>
</nav>
<?php endif ?>
