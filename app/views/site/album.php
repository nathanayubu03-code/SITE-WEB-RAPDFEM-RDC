<?= render_partial('site/_page_header', ['title' => $album['title'], 'lead' => $album['description'], 'crumbs' => ['galerie' => 'Galerie', $album['title']]]) ?>

<section class="section">
  <div class="container">
    <?php if ($album['project_title'] && $album['project_published']): ?>
      <p><a class="link-arrow" href="<?= e(url('projets/' . $album['project_slug'])) ?>">Projet : <?= e($album['project_title']) ?></a></p>
    <?php endif; ?>
    <?php if ($media): ?>
      <div class="photo-grid photo-grid--large" data-lightbox-group>
        <?php foreach ($media as $item): ?>
          <?php if ($item['type'] === 'video' && ($yt = youtube_id($item['video_url']))): ?>
            <a href="https://www.youtube-nocookie.com/embed/<?= e($yt) ?>?autoplay=1&rel=0" data-lightbox data-video data-caption="<?= e($item['caption']) ?>" class="photo-grid__video">
              <img src="https://img.youtube.com/vi/<?= e($yt) ?>/hqdefault.jpg" alt="<?= e($item['caption'] ?: 'Vidéo') ?>" loading="lazy">
              <span class="play" aria-hidden="true"></span>
            </a>
          <?php elseif ($item['type'] === 'photo' && $item['file']): ?>
            <a href="<?= e(upload_url($item['file'])) ?>" data-lightbox data-caption="<?= e($item['caption']) ?>">
              <img src="<?= e(upload_url($item['file'], true)) ?>" alt="<?= e($item['caption'] ?: $album['title']) ?>" loading="lazy">
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty">Cet album est vide pour le moment.</p>
    <?php endif; ?>
  </div>
</section>
