<?= render_partial('site/_page_header', ['title' => 'Nos partenaires', 'lead' => 'Notre action repose sur des partenariats solides avec des acteurs techniques, financiers et institutionnels.', 'crumbs' => ['Nos partenaires']]) ?>

<section class="section">
  <div class="container">
    <?php if ($grouped): ?>
      <?php foreach (PARTNER_TYPES as $type => $label): if (empty($grouped[$type])) continue; ?>
        <h2 class="section__title section__title--small"><?= e($label) ?>s</h2>
        <div class="partners">
          <?php foreach ($grouped[$type] as $partner): $href = safe_link($partner['url']); ?>
            <article class="partner">
              <div class="partner__logo">
                <?php if ($partner['logo']): ?>
                  <img src="<?= e(upload_url($partner['logo'], true)) ?>" alt="<?= e($partner['name']) ?>" loading="lazy">
                <?php else: ?>
                  <span><?= e($partner['name']) ?></span>
                <?php endif; ?>
              </div>
              <h3><?= e($partner['name']) ?></h3>
              <?php if ($partner['description']): ?><p><?= e($partner['description']) ?></p><?php endif; ?>
              <?php if ($href): ?><a class="link-arrow" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer">Site web</a><?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty">La liste de nos partenaires sera bientôt publiée.</p>
    <?php endif; ?>
  </div>
</section>

<section class="cta">
  <div class="container cta__inner">
    <div>
      <h2><?= e(setting('cta_title', 'Devenez partenaire de notre action')) ?></h2>
      <p><?= e(setting('cta_text')) ?></p>
    </div>
    <div class="cta__actions"><a class="btn btn--white" href="<?= e(url('contact')) ?>">Prendre contact</a></div>
  </div>
</section>
