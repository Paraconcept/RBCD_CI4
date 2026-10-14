<article class="news-card h-100">
  <a href="<?= base_url('actualites/' . $n->slug) ?>" class="news-card-img-wrap">
    <?php if ($n->image): ?>
      <img src="<?= base_url('uploads/news/' . $n->image) ?>"
           alt="<?= esc($n->title) ?>"
           class="news-card-img">
    <?php else: ?>
      <div class="news-card-img-placeholder"><i class="fas fa-newspaper"></i></div>
    <?php endif; ?>
  </a>
  <div class="news-card-body">
    <?php if ($n->published_at): ?>
      <div class="news-card-date"><i class="fas fa-calendar-day me-1"></i><?= date('d/m/Y', strtotime($n->published_at)) ?></div>
    <?php endif; ?>
    <h5 class="news-card-title">
      <a href="<?= base_url('actualites/' . $n->slug) ?>"><?= esc($n->title) ?></a>
    </h5>
    <?php
      $_t  = strip_tags($n->content ?? '');
      $_ex = $n->excerpt ?: (mb_strlen($_t) > 120 ? mb_substr($_t, 0, 120) . '…' : $_t);
    ?>
    <?php if ($_ex): ?>
      <p class="news-card-excerpt"><?= esc($_ex) ?></p>
    <?php endif; ?>
    <a href="<?= base_url('actualites/' . $n->slug) ?>" class="news-card-link">Lire la suite →</a>
  </div>
</article>
