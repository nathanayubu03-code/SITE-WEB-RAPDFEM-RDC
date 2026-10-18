<article class="card">
  <a class="card__media" href="<?= e(url('actualites/' . $article['slug'])) ?>" tabindex="-1" aria-hidden="true">
    <?= visual($article['image'], $article['title']) ?>
  </a>
  <div class="card__body">
    <p class="card__meta"><span class="tag tag--<?= e($article['type']) ?>"><?= e(ARTICLE_TYPES[$article['type']] ?? '') ?></span> <time datetime="<?= e(substr($article['published_at'], 0, 10)) ?>"><?= e(format_date($article['published_at'])) ?></time></p>
    <h3 class="card__title"><a href="<?= e(url('actualites/' . $article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
    <p class="card__text"><?= e(excerpt($article['excerpt'] ?: $article['body'], 140)) ?></p>
  </div>
</article>
