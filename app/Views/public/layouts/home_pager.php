<?php $pager->setSurroundCount(2) ?>

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

    <?php if ($pager->hasPrevious()): ?>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getFirst() ?>#actualites">&laquo;</a>
    </li>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getPrevious() ?>#actualites">&lsaquo;</a>
    </li>
    <?php else: ?>
    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
    <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
    <?php endif ?>

    <?php foreach ($pager->links() as $link): ?>
    <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
      <?php if ($link['active']): ?>
      <span class="page-link"><?= $link['title'] ?></span>
      <?php else: ?>
      <a class="page-link" href="<?= esc($link['uri']) ?>#actualites"><?= $link['title'] ?></a>
      <?php endif ?>
    </li>
    <?php endforeach ?>

    <?php if ($pager->hasNext()): ?>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getNext() ?>#actualites">&rsaquo;</a>
    </li>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getLast() ?>#actualites">&raquo;</a>
    </li>
    <?php else: ?>
    <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
    <?php endif ?>

  </ul>
</nav>
<?php endif ?>
