<?= render_partial('site/_page_header', ['title' => 'Publications', 'lead' => 'Rapports annuels, rapports de projet, notes et études à consulter et télécharger.', 'crumbs' => ['Publications']]) ?>

<section class="section">
  <div class="container">
    <form class="filters" method="get" action="<?= e(url('publications')) ?>" data-autosubmit>
      <label>
        <span>Catégorie</span>
        <select name="categorie">
          <option value="">Toutes les catégories</option>
          <?php foreach (PUBLICATION_CATEGORIES as $k => $label): ?>
            <option value="<?= e($k) ?>"<?= $category === $k ? ' selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Année</span>
        <select name="annee">
          <option value="">Toutes les années</option>
          <?php foreach ($years as $y): ?>
            <option value="<?= (int) $y['year'] ?>"<?= $year === (int) $y['year'] ? ' selected' : '' ?>><?= (int) $y['year'] ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn--navy" type="submit">Filtrer</button>
    </form>

    <?php if ($publications): ?>
      <ul class="docs">
        <?php foreach ($publications as $pub): ?>
          <li class="doc">
            <div class="doc__cover">
              <?php if ($pub['cover']): ?>
                <img src="<?= e(upload_url($pub['cover'], true)) ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="doc__icon" aria-hidden="true">PDF</span>
              <?php endif; ?>
            </div>
            <div class="doc__body">
              <p class="card__meta"><?= e(PUBLICATION_CATEGORIES[$pub['category']] ?? '') ?> &middot; <?= (int) $pub['year'] ?></p>
              <h2 class="doc__title"><?= e($pub['title']) ?></h2>
              <?php if ($pub['description']): ?><p class="card__text"><?= e($pub['description']) ?></p><?php endif; ?>
              <a class="btn btn--green btn--small" href="<?= e(url('publications/telecharger/' . $pub['id'])) ?>" target="_blank" rel="noopener">Télécharger le PDF<?= $pub['file_size'] ? ' (' . e(human_size((int) $pub['file_size'])) . ')' : '' ?></a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?= pager($pager) ?>
    <?php else: ?>
      <p class="empty">Aucun document disponible pour ces critères.</p>
    <?php endif; ?>
  </div>
</section>
