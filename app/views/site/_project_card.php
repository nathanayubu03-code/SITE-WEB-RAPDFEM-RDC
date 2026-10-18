<article class="card">
  <a class="card__media" href="<?= e(url('projets/' . $project['slug'])) ?>" tabindex="-1" aria-hidden="true">
    <?= visual($project['image'], $project['title']) ?>
    <span class="badge badge--<?= e($project['status']) ?>"><?= e(PROJECT_STATUSES[$project['status']] ?? '') ?></span>
  </a>
  <div class="card__body">
    <p class="card__meta"><?= e($project['zone']) ?><?= $project['domain'] ? ' &middot; ' . e($project['domain']) : '' ?></p>
    <h3 class="card__title"><a href="<?= e(url('projets/' . $project['slug'])) ?>"><?= e($project['title']) ?></a></h3>
    <p class="card__text"><?= e(excerpt($project['summary'], 150)) ?></p>
    <?php if ($project['donors']): ?><p class="card__foot">Avec le soutien de : <?= e($project['donors']) ?></p><?php endif; ?>
  </div>
</article>
