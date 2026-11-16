<?php
/** @var \CodeIgniter\Pager\PagerRenderer $pager */
if ($pager->getPageCount() <= 1) return;
$pager->setSurroundCount(2);

// Chaque tableau a son propre groupe (page_<groupe>=) : on revient sur sa carte au lieu du haut de page
$anchor = preg_match('/[?&]page_(\w+)=/', $pager->getFirst(), $g) ? '#list-' . $g[1] : '';
$item = static fn (?string $uri, string $label, bool $active = false): string =>
    '<li class="page-item' . ($active ? ' active' : '') . ($uri === null ? ' disabled' : '') . '">'
    . ($uri === null || $active ? '<span class="page-link">' . $label . '</span>' : '<a class="page-link" href="' . esc($uri, 'attr') . $anchor . '">' . $label . '</a>')
    . '</li>';
?>
<nav aria-label="Pagination">
  <ul class="pagination pagination-sm mb-0">
    <?= $item($pager->hasPreviousPage() ? $pager->getFirst() : null, '&laquo;') ?>
    <?= $item($pager->hasPreviousPage() ? $pager->getPreviousPage() : null, '&lsaquo;') ?>
    <?php foreach ($pager->links() as $link): ?>
      <?= $item($link['uri'], (string) $link['title'], $link['active']) ?>
    <?php endforeach ?>
    <?= $item($pager->hasNextPage() ? $pager->getNextPage() : null, '&rsaquo;') ?>
    <?= $item($pager->hasNextPage() ? $pager->getLast() : null, '&raquo;') ?>
  </ul>
</nav>
