<div class="page-head"><h1>Mon compte</h1></div>
<div class="cols">
  <section class="panel">
    <h2>Double authentification (2FA)</h2>
    <?php if ((int) $me['totp_enabled']): ?>
      <p><span class="status status--on">Activée</span> Un code de votre application vous est demandé à chaque connexion.</p>
      <?php if (!$mandatory): ?>
        <form method="post" action="<?= e(url('admin/account')) ?>" class="stack">
          <?= csrf_field() ?><input type="hidden" name="action" value="disable_2fa">
          <label class="field"><span>Mot de passe pour confirmer</span><input type="password" name="password" required autocomplete="current-password"></label>
          <?php if (!empty($errors['disable'])): ?><small class="error"><?= e($errors['disable']) ?></small><?php endif; ?>
          <button class="btn btn--danger" type="submit">Désactiver la 2FA</button>
        </form>
      <?php else: ?>
        <p class="muted">Obligatoire pour votre rôle.</p>
      <?php endif; ?>
    <?php else: ?>
      <ol class="steps">
        <li>Installez une application d'authentification sur votre téléphone : Google Authenticator, Microsoft Authenticator ou Aegis.</li>
        <li>Scannez ce QR code avec l'application.</li>
        <li>Saisissez le code à 6 chiffres affiché pour valider.</li>
      </ol>
      <div class="qr" data-qr="<?= e($otpUri) ?>"></div>
      <p class="muted">Impossible de scanner ? Saisissez cette clé manuellement :<br><code class="secret"><?= e(trim(chunk_split($setupSecret, 4, ' '))) ?></code></p>
      <form method="post" action="<?= e(url('admin/account')) ?>" class="stack">
        <?= csrf_field() ?><input type="hidden" name="action" value="enable_2fa">
        <label class="field"><span>Code à 6 chiffres</span><input type="text" name="code" inputmode="numeric" maxlength="7" required autocomplete="one-time-code" class="code-input"></label>
        <?php if (!empty($errors['code'])): ?><small class="error"><?= e($errors['code']) ?></small><?php endif; ?>
        <button class="btn btn--primary" type="submit">Activer la double authentification</button>
      </form>
    <?php endif; ?>
  </section>

  <section class="panel">
    <h2>Changer mon mot de passe</h2>
    <form method="post" action="<?= e(url('admin/account')) ?>" class="stack">
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <label class="field"><span>Mot de passe actuel</span><input type="password" name="current_password" required autocomplete="current-password"></label>
      <?php if (!empty($errors['current_password'])): ?><small class="error"><?= e($errors['current_password']) ?></small><?php endif; ?>
      <label class="field"><span>Nouveau mot de passe</span><input type="password" name="new_password" required minlength="12" autocomplete="new-password"></label>
      <?php if (!empty($errors['new_password'])): ?><small class="error"><?= e($errors['new_password']) ?></small><?php endif; ?>
      <label class="field"><span>Confirmer</span><input type="password" name="confirm_password" required minlength="12" autocomplete="new-password"></label>
      <?php if (!empty($errors['confirm_password'])): ?><small class="error"><?= e($errors['confirm_password']) ?></small><?php endif; ?>
      <button class="btn btn--primary" type="submit">Modifier le mot de passe</button>
    </form>
  </section>
</div>
