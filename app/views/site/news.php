<?= render_partial('site/_page_header', ['title' => 'Actualités', 'lead' => 'Articles, communiqués officiels et brèves humanitaires.', 'crumbs' => ['Actualités']]) ?>

<section class="section">
  <div class="container">
    <?php if (!is_static_demo()): ?>
    <nav class="tabs" aria-label="Filtrer par type">
      <a href="<?= e(url('actualites')) ?>"<?= $type === '' ? ' aria-current="page"' : '' ?>>Tout</a>
      <?php foreach (ARTICLE_TYPES as $k => $label): ?>
        <a href="<?= e(url('actualites?type=' . $k)) ?>"<?= $type === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?>s</a>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <?php if ($articles): ?>
      <div class="grid grid--3">
        <?php foreach ($articles as $article): ?><?= render_partial('site/_article_card', ['article' => $article]) ?><?php endforeach; ?>
      </div>
      <?= pager($pager) ?>
    <?php else: ?>
      <p class="empty">Aucune publication pour le moment.</p>
    <?php endif; ?>
  </div>
</section>
