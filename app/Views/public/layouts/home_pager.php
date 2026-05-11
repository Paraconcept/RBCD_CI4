<?php $pager->setSurroundCount(2) ?>

<?php if (true): ?>
<nav aria-label="Navigation des actualités" class="mt-20">
  <ul class="pagination justify-content-center">

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
