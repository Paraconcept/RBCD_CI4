<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Pagination actualités">
  <ul class="pagination justify-content-center">

    <?php if ($pager->hasPreviousPage()): ?>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getPreviousPage() ?>" aria-label="Précédent">
        <i class="fas fa-chevron-left"></i>
      </a>
    </li>
    <?php else: ?>
    <li class="page-item disabled">
      <span class="page-link"><i class="fas fa-chevron-left"></i></span>
    </li>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link): ?>
    <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
      <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
    </li>
    <?php endforeach; ?>

    <?php if ($pager->hasNextPage()): ?>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getNextPage() ?>" aria-label="Suivant">
        <i class="fas fa-chevron-right"></i>
      </a>
    </li>
    <?php else: ?>
    <li class="page-item disabled">
      <span class="page-link"><i class="fas fa-chevron-right"></i></span>
    </li>
    <?php endif; ?>

  </ul>
</nav>
