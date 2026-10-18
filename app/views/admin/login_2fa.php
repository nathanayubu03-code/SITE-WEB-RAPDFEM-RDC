<h1>Vérification en deux étapes</h1>
<p class="muted">Ouvrez votre application d'authentification et saisissez le code à 6 chiffres affiché pour <?= e(setting('org_name', 'RAPDFEM')) ?>.</p>
<?php if ($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e(url('admin/login/2fa')) ?>" class="stack">
  <?= csrf_field() ?>
  <label class="field"><span>Code de sécurité</span><input type="text" name="code" inputmode="numeric" pattern="[0-9 ]{6,7}" maxlength="7" required autocomplete="one-time-code" autofocus class="code-input"></label>
  <button class="btn btn--primary btn--block" type="submit">Valider</button>
</form>
<p class="auth__foot">Téléphone perdu ? Demandez à un administrateur de réinitialiser votre double authentification.</p>
