<section class="page-header">
  <div class="container">
    <?php if (!empty($crumbs)): ?>
      <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="<?= e(url()) ?>">Accueil</a>
        <?php foreach ($crumbs as $href => $label): ?>
          <span aria-hidden="true">/</span><?php if (is_string($href)): ?><a href="<?= e(url($href)) ?>"><?= e($label) ?></a><?php else: ?><span><?= e($label) ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
    <h1><?= e($title) ?></h1>
    <?php if (!empty($lead)): ?><p class="page-header__lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
