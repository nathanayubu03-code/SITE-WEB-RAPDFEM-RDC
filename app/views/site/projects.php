<?= render_partial('site/_page_header', ['title' => 'Nos projets', 'lead' => 'Projets réalisés et en cours, par zone d\'intervention, avec nos bailleurs et nos résultats.', 'crumbs' => ['Nos projets']]) ?>

<section class="section">
  <div class="container">
    <?php if (!is_static_demo()): ?>
    <form class="filters" method="get" action="<?= e(url('projets')) ?>" data-autosubmit>
      <label>
        <span>Zone d'intervention</span>
        <select name="zone">
          <option value="">Toutes les zones</option>
          <?php foreach ($usedZones as $z): if (!$z['zone']) continue; ?>
            <option value="<?= e($z['zone']) ?>"<?= $zone === $z['zone'] ? ' selected' : '' ?>><?= e($z['zone']) ?> (<?= (int) $z['n'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <span>Statut</span>
        <select name="statut">
          <option value="">Tous les statuts</option>
          <?php foreach (PROJECT_STATUSES as $k => $label): ?>
            <option value="<?= e($k) ?>"<?= $status === $k ? ' selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn--navy" type="submit">Filtrer</button>
      <?php if ($zone || $status): ?><a class="filters__reset" href="<?= e(url('projets')) ?>">Réinitialiser</a><?php endif; ?>
    </form>
    <?php endif; ?>

    <p class="results-count"><?= (int) $pager['total'] ?> projet<?= $pager['total'] > 1 ? 's' : '' ?></p>

    <?php if ($projects): ?>
      <div class="grid grid--3">
        <?php foreach ($projects as $project): ?>
          <?= render_partial('site/_project_card', ['project' => $project]) ?>
        <?php endforeach; ?>
      </div>
      <?= pager($pager) ?>
    <?php else: ?>
      <p class="empty">Aucun projet ne correspond à ces critères.</p>
    <?php endif; ?>
  </div>
</section>
