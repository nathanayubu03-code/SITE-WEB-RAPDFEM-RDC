<?= render_partial('site/_page_header', ['title' => $project['title'], 'lead' => $project['summary'], 'crumbs' => ['projets' => 'Nos projets', $project['title']]]) ?>

<section class="section">
  <div class="container detail">
    <div class="detail__main">
      <div class="detail__cover"><?= visual($project['image'], $project['title'], false) ?></div>
      <div class="prose">
        <?= markup($project['body']) ?>
      </div>
      <?php if (trim((string) $project['results']) !== ''): ?>
        <div class="results">
          <h2>Résultats</h2>
          <div class="prose"><?= markup($project['results']) ?></div>
        </div>
      <?php endif; ?>

      <?php if ($photos): ?>
        <h2 class="detail__subtitle">En images</h2>
        <div class="photo-grid" data-lightbox-group>
          <?php foreach ($photos as $photo): ?>
            <a href="<?= e(upload_url($photo['file'])) ?>" data-lightbox data-caption="<?= e($photo['caption']) ?>">
              <img src="<?= e(upload_url($photo['file'], true)) ?>" alt="<?= e($photo['caption'] ?: $project['title']) ?>" loading="lazy">
            </a>
          <?php endforeach; ?>
        </div>
        <?php foreach ($albums as $album): ?>
          <p><a class="link-arrow" href="<?= e(url('galerie/' . $album['slug'])) ?>">Album : <?= e($album['title']) ?></a></p>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <aside class="detail__aside">
      <dl class="facts">
        <div><dt>Statut</dt><dd><span class="badge badge--inline badge--<?= e($project['status']) ?>"><?= e(PROJECT_STATUSES[$project['status']] ?? '') ?></span></dd></div>
        <div><dt>Zone d'intervention</dt><dd><a href="<?= e(url('projets?zone=' . rawurlencode((string) $project['zone']))) ?>"><?= e($project['zone']) ?></a></dd></div>
        <?php if ($project['domain']): ?><div><dt>Domaine</dt><dd><?= e($project['domain']) ?></dd></div><?php endif; ?>
        <?php if (project_period($project)): ?><div><dt>Période</dt><dd><?= e(project_period($project)) ?></dd></div><?php endif; ?>
        <?php if ($project['beneficiaries']): ?><div><dt>Bénéficiaires</dt><dd><?= e($project['beneficiaries']) ?></dd></div><?php endif; ?>
        <?php if ($project['donors']): ?><div><dt>Bailleurs de fonds</dt><dd><?= e($project['donors']) ?></dd></div><?php endif; ?>
      </dl>
      <a class="btn btn--green btn--block" href="<?= e(url('contact')) ?>">Soutenir ce type de projet</a>
    </aside>
  </div>
</section>

<?php if ($related): ?>
<section class="section section--alt">
  <div class="container">
    <h2 class="section__title">Projets similaires</h2>
    <div class="grid grid--3">
      <?php foreach ($related as $p): ?><?= render_partial('site/_project_card', ['project' => $p]) ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
