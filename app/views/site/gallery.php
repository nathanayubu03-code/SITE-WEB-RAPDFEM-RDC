<?= render_partial('site/_page_header', ['title' => 'Galerie', 'lead' => 'Photothèque et vidéothèque de nos projets sur le terrain.', 'crumbs' => ['Galerie']]) ?>

<section class="section">
  <div class="container">
    <?php if ($albums): ?>
      <div class="grid grid--3">
        <?php foreach ($albums as $album):
          $cover = $album['cover'] ?: $album['first_photo'];
          $yt = !$cover ? youtube_id($album['first_video']) : null; ?>
          <article class="card card--album">
            <a class="card__media" href="<?= e(url('galerie/' . $album['slug'])) ?>" tabindex="-1" aria-hidden="true">
              <?php if ($yt): ?>
                <img class="visual" src="https://img.youtube.com/vi/<?= e($yt) ?>/hqdefault.jpg" alt="" loading="lazy">
              <?php else: ?>
                <?= visual($cover, $album['title']) ?>
              <?php endif; ?>
              <span class="badge"><?= (int) $album['media_count'] ?> élément<?= $album['media_count'] > 1 ? 's' : '' ?></span>
            </a>
            <div class="card__body">
              <?php if ($album['project_title']): ?><p class="card__meta"><?= e($album['project_title']) ?></p><?php endif; ?>
              <h2 class="card__title"><a href="<?= e(url('galerie/' . $album['slug'])) ?>"><?= e($album['title']) ?></a></h2>
              <?php if ($album['description']): ?><p class="card__text"><?= e(excerpt($album['description'], 120)) ?></p><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty">Les albums photo seront bientôt disponibles.</p>
    <?php endif; ?>
  </div>
</section>
