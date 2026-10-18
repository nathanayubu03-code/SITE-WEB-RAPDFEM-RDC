<section class="hero" aria-label="À la une">
  <?php if ($slides): ?>
    <div class="slider" data-slider>
      <?php foreach ($slides as $i => $slide): ?>
        <div class="slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
          <img class="slide__img" src="<?= e(upload_url($slide['image'])) ?>" alt="" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
          <div class="slide__overlay"></div>
          <div class="container slide__content">
            <<?= $i === 0 ? 'h1' : 'h2' ?> class="hero__title"><?= e($slide['title']) ?></<?= $i === 0 ? 'h1' : 'h2' ?>>
            <?php if ($slide['subtitle']): ?><p class="hero__text"><?= e($slide['subtitle']) ?></p><?php endif; ?>
            <?php if ($slide['cta_label'] && safe_link($slide['cta_url'])): ?>
              <a class="btn btn--green" href="<?= e(safe_link($slide['cta_url'])) ?>"><?= e($slide['cta_label']) ?></a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (count($slides) > 1): ?>
        <div class="slider__controls">
          <button type="button" class="slider__btn" data-prev aria-label="Diapositive précédente">&#8249;</button>
          <div class="slider__dots">
            <?php foreach ($slides as $i => $slide): ?>
              <button type="button" class="slider__dot<?= $i === 0 ? ' is-active' : '' ?>" data-dot="<?= $i ?>" aria-label="Afficher la diapositive <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
          <button type="button" class="slider__btn" data-next aria-label="Diapositive suivante">&#8250;</button>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="hero__static">
      <div class="container slide__content">
        <h1 class="hero__title"><?= e(setting('tagline', setting('org_name', 'RAPDFEM'))) ?></h1>
        <p class="hero__text"><?= e(excerpt(setting('mission'), 220)) ?></p>
        <div class="hero__actions">
          <a class="btn btn--green" href="<?= e(url('projets')) ?>">Découvrir nos projets</a>
          <a class="btn btn--ghost" href="<?= e(url('qui-sommes-nous')) ?>">Qui sommes-nous ?</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="section">
  <div class="container intro">
    <div>
      <p class="eyebrow">Notre organisation</p>
      <h2 class="section__title">Au service des communautés de RDC</h2>
      <p class="lead"><?= e(setting('about_intro')) ?></p>
      <a class="link-arrow" href="<?= e(url('qui-sommes-nous')) ?>">En savoir plus sur nous</a>
    </div>
    <div class="mission-box">
      <h3>Notre mission</h3>
      <p><?= e(setting('mission')) ?></p>
      <h3>Notre vision</h3>
      <p><?= e(setting('vision')) ?></p>
    </div>
  </div>
</section>

<?php if ($stats): ?>
<section class="stats" aria-label="Chiffres clés">
  <div class="container stats__grid">
    <?php foreach ($stats as $stat): ?>
      <div class="stat">
        <span class="stat__value"><?= e($stat['value']) ?></span>
        <span class="stat__label"><?= e($stat['label']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($domains): ?>
<section class="section section--alt">
  <div class="container">
    <p class="eyebrow">Ce que nous faisons</p>
    <h2 class="section__title">Domaines d'intervention</h2>
    <div class="domains">
      <?php foreach ($domains as $i => $domain): ?>
        <div class="domain">
          <span class="domain__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <h3><?= e($domain['title']) ?></h3>
          <p><?= e($domain['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($projects): ?>
<section class="section">
  <div class="container">
    <div class="section__head">
      <div>
        <p class="eyebrow">Sur le terrain</p>
        <h2 class="section__title">Nos projets</h2>
      </div>
      <a class="link-arrow" href="<?= e(url('projets')) ?>">Tous les projets</a>
    </div>
    <div class="grid grid--3">
      <?php foreach ($projects as $project): ?>
        <?= render_partial('site/_project_card', ['project' => $project]) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($articles): ?>
<section class="section section--alt">
  <div class="container">
    <div class="section__head">
      <div>
        <p class="eyebrow">Actualités</p>
        <h2 class="section__title">Dernières nouvelles</h2>
      </div>
      <a class="link-arrow" href="<?= e(url('actualites')) ?>">Toutes les actualités</a>
    </div>
    <div class="grid grid--3">
      <?php foreach ($articles as $article): ?>
        <?= render_partial('site/_article_card', ['article' => $article]) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($partners): ?>
<section class="section">
  <div class="container">
    <div class="section__head">
      <div>
        <p class="eyebrow">Ils nous font confiance</p>
        <h2 class="section__title">Nos partenaires</h2>
      </div>
      <a class="link-arrow" href="<?= e(url('partenaires')) ?>">Voir tous les partenaires</a>
    </div>
    <ul class="logos">
      <?php foreach ($partners as $partner): ?>
        <li class="logos__item">
          <?php if ($partner['logo']): ?>
            <img src="<?= e(upload_url($partner['logo'], true)) ?>" alt="<?= e($partner['name']) ?>" loading="lazy">
          <?php else: ?>
            <span><?= e($partner['name']) ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="cta">
  <div class="container cta__inner">
    <div>
      <h2><?= e(setting('cta_title', 'Devenez partenaire de notre action')) ?></h2>
      <p><?= e(setting('cta_text')) ?></p>
    </div>
    <div class="cta__actions">
      <a class="btn btn--white" href="<?= e(url('contact')) ?>">Nous contacter</a>
      <a class="btn btn--outline-white" href="<?= e(url('publications')) ?>">Lire nos rapports</a>
    </div>
  </div>
</section>
