<?= render_partial('site/_page_header', ['title' => 'Contact', 'lead' => 'Une question, une proposition de partenariat, une demande d\'information ? Écrivez-nous.', 'crumbs' => ['Contact']]) ?>

<section class="section">
  <div class="container contact">
    <div class="contact__info">
      <h2>Siège</h2>
      <address><?= nl2br(e(setting('address'))) ?></address>
      <?php if (setting('phone')): ?><p><strong>Téléphone :</strong> <a href="tel:<?= e(preg_replace('/[^\d+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p><?php endif; ?>
      <?php if (setting('whatsapp')): ?><p><strong>WhatsApp :</strong> <a href="https://wa.me/<?= e(preg_replace('/\D/', '', setting('whatsapp'))) ?>" target="_blank" rel="noopener noreferrer">Écrire sur WhatsApp</a></p><?php endif; ?>
      <?php if (setting('email')): ?><p><strong>E-mail :</strong> <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p><?php endif; ?>
      <?php if (setting('hours')): ?><p><strong>Horaires :</strong> <?= e(setting('hours')) ?></p><?php endif; ?>
      <?php $offices = setting_lines('offices'); if ($offices): ?>
        <h2>Bureaux de terrain</h2>
        <ul class="offices">
          <?php foreach ($offices as $o): ?><li><strong><?= e($o['title']) ?></strong><?= $o['text'] ? ' : ' . e($o['text']) : '' ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if ($socials = social_links()): ?>
        <h2>Réseaux sociaux</h2>
        <ul class="footer__social footer__social--dark">
          <?php foreach ($socials as $s): ?><li><a href="<?= e($s['href']) ?>" target="_blank" rel="noopener noreferrer"><?= e($s['label']) ?></a></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <?php if (is_static_demo()): ?>
    <div class="form contact__form">
      <h2>Formulaire de contact</h2>
      <p>Sur la version hébergée du site, cette page contient un formulaire sécurisé. Les messages arrivent dans l'espace d'administration.</p>
      <?php if (setting('email')): ?><a class="btn btn--green" href="mailto:<?= e(setting('email')) ?>">Écrire par e-mail</a><?php endif; ?>
    </div>
    <?php else: ?>
    <form class="form contact__form" method="post" action="<?= e(url('contact')) ?>" novalidate>
      <?= csrf_field() ?>
      <?php if (!empty($errors['form'])): ?><div class="alert alert--error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
      <div class="form__row">
        <label class="field">
          <span>Nom complet *</span>
          <input type="text" name="name" value="<?= e($values['name']) ?>" required maxlength="150" autocomplete="name">
          <?php if (!empty($errors['name'])): ?><small class="field__error"><?= e($errors['name']) ?></small><?php endif; ?>
        </label>
        <label class="field">
          <span>E-mail *</span>
          <input type="email" name="email" value="<?= e($values['email']) ?>" required maxlength="191" autocomplete="email">
          <?php if (!empty($errors['email'])): ?><small class="field__error"><?= e($errors['email']) ?></small><?php endif; ?>
        </label>
      </div>
      <div class="form__row">
        <label class="field">
          <span>Téléphone</span>
          <input type="tel" name="phone" value="<?= e($values['phone']) ?>" maxlength="50" autocomplete="tel">
        </label>
        <label class="field">
          <span>Organisation</span>
          <input type="text" name="organization" value="<?= e($values['organization']) ?>" maxlength="200" autocomplete="organization">
        </label>
      </div>
      <label class="field">
        <span>Objet *</span>
        <input type="text" name="subject" value="<?= e($values['subject']) ?>" required maxlength="200">
        <?php if (!empty($errors['subject'])): ?><small class="field__error"><?= e($errors['subject']) ?></small><?php endif; ?>
      </label>
      <label class="field">
        <span>Message *</span>
        <textarea name="message" rows="7" required maxlength="5000"><?= e($values['message']) ?></textarea>
        <?php if (!empty($errors['message'])): ?><small class="field__error"><?= e($errors['message']) ?></small><?php endif; ?>
      </label>
      <div class="hp" aria-hidden="true">
        <label>Ne pas remplir ce champ <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
      </div>
      <?php if ($recaptcha): ?>
        <div class="g-recaptcha" data-sitekey="<?= e(config('recaptcha.site_key')) ?>"></div>
        <script src="https://www.google.com/recaptcha/api.js?hl=fr" async defer></script>
      <?php endif; ?>
      <p class="form__note">Vos données servent uniquement à répondre à votre message. Elles ne sont jamais cédées à des tiers.</p>
      <button class="btn btn--green" type="submit">Envoyer le message</button>
    </form>
    <?php endif; ?>
  </div>
</section>
