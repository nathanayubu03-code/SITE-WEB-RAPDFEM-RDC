<article>
  <header class="page-header page-header--article">
    <div class="container narrow">
      <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="<?= e(url()) ?>">Accueil</a><span aria-hidden="true">/</span><a href="<?= e(url('actualites')) ?>">Actualités</a>
      </nav>
      <p class="card__meta"><span class="tag tag--<?= e($article['type']) ?>"><?= e(ARTICLE_TYPES[$article['type']] ?? '') ?></span> <time datetime="<?= e(substr($article['published_at'], 0, 10)) ?>"><?= e(format_date($article['published_at'])) ?></time></p>
      <h1><?= e($article['title']) ?></h1>
      <?php if ($article['excerpt']): ?><p class="page-header__lead"><?= e($article['excerpt']) ?></p><?php endif; ?>
    </div>
  </header>
  <div class="section">
    <div class="container narrow">
      <?php if ($article['image']): ?><figure class="article__cover"><?= visual($article['image'], $article['title'], false) ?></figure><?php endif; ?>
      <div class="prose"><?= markup($article['body']) ?></div>
      <div class="share">
        <span>Partager :</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode(abs_url('actualites/' . $article['slug'])) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode(abs_url('actualites/' . $article['slug'])) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
        <a href="https://wa.me/?text=<?= rawurlencode($article['title'] . ' ' . abs_url('actualites/' . $article['slug'])) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
      </div>
    </div>
  </div>
</article>

<?php if ($recent): ?>
<section class="section section--alt">
  <div class="container">
    <h2 class="section__title">À lire aussi</h2>
    <div class="grid grid--3">
      <?php foreach ($recent as $a): ?><?= render_partial('site/_article_card', ['article' => $a]) ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
