<?= render_partial('site/_page_header', ['title' => 'Qui sommes-nous ?', 'lead' => setting('tagline'), 'crumbs' => ['Qui sommes-nous ?']]) ?>

<nav class="subnav" aria-label="Sections de la page">
  <div class="container">
    <a href="#histoire">Historique</a><a href="#mission">Vision et mission</a><a href="#valeurs">Valeurs</a><a href="#gouvernance">Gouvernance</a><a href="#domaines">Domaines d'intervention</a>
  </div>
</nav>

<section class="section">
  <div class="container narrow">
    <p class="lead"><?= e(setting('about_intro')) ?></p>
  </div>
</section>

<section class="section section--alt" id="histoire">
  <div class="container narrow prose">
    <p class="eyebrow">Notre parcours</p>
    <h2 class="section__title">Historique en RDC</h2>
    <?= markup(setting('history')) ?>
  </div>
</section>

<section class="section" id="mission">
  <div class="container split">
    <div class="pillar pillar--navy">
      <p class="eyebrow">Vision</p>
      <p class="pillar__text"><?= e(setting('vision')) ?></p>
    </div>
    <div class="pillar pillar--green">
      <p class="eyebrow">Mission</p>
      <p class="pillar__text"><?= e(setting('mission')) ?></p>
    </div>
  </div>
</section>

<?php $values = setting_lines('values'); if ($values): ?>
<section class="section section--alt" id="valeurs">
  <div class="container">
    <p class="eyebrow">Ce qui nous guide</p>
    <h2 class="section__title">Nos valeurs</h2>
    <div class="values">
      <?php foreach ($values as $value): ?>
        <div class="value"><h3><?= e($value['title']) ?></h3><p><?= e($value['text']) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" id="gouvernance">
  <div class="container narrow prose">
    <p class="eyebrow">Transparence</p>
    <h2 class="section__title">Gouvernance</h2>
    <?= markup(setting('governance')) ?>
    <p><a class="link-arrow" href="<?= e(url('publications?categorie=rapport_annuel')) ?>">Consulter nos rapports annuels</a></p>
  </div>
</section>

<?php if ($domains): ?>
<section class="section section--alt" id="domaines">
  <div class="container">
    <p class="eyebrow">Notre expertise</p>
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

<?php if ($stats): ?>
<section class="stats" aria-label="Chiffres clés">
  <div class="container stats__grid">
    <?php foreach ($stats as $stat): ?>
      <div class="stat"><span class="stat__value"><?= e($stat['value']) ?></span><span class="stat__label"><?= e($stat['label']) ?></span></div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
