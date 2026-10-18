<h1>Connexion à l'administration</h1>
<?php if ($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= e(url('admin/login')) ?>" class="stack">
  <?= csrf_field() ?>
  <label class="field"><span>E-mail</span><input type="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus></label>
  <label class="field"><span>Mot de passe</span><input type="password" name="password" required autocomplete="current-password"></label>
  <button class="btn btn--primary btn--block" type="submit">Se connecter</button>
</form>
<p class="auth__foot"><a href="<?= e(url()) ?>">Retour au site</a></p>
