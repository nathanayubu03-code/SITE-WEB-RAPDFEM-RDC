<?php if ($p['pages'] > 1): ?>
<nav class="pager" aria-label="Pagination">
  <?php for ($i = 1; $i <= $p['pages']; $i++): ?>
    <?php if ($i === $p['page']): ?><span aria-current="page"><?= $i ?></span><?php else: ?><a href="<?= e(page_url($i)) ?>"><?= $i ?></a><?php endif; ?>
  <?php endfor; ?>
</nav>
<?php endif; ?>
