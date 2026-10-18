<h1>Installation du site</h1>
<p class="muted">Cette page n'apparaît qu'une seule fois. Créez maintenant le compte du premier administrateur.</p>

<ul class="checks">
  <?php foreach ($checks as $c): ?>
    <li class="<?= $c['ok'] === true ? 'is-ok' : ($c['ok'] === false ? 'is-ko' : 'is-warn') ?>"><?= e($c['label']) ?></li>
  <?php endforeach; ?>
</ul>

<?php if (!empty($errors['form'])): ?><div class="alert alert--error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

<form method="post" action="<?= e(url('install')) ?>" class="stack">
  <?= csrf_field() ?>
  <label class="field"><span>Votre nom</span><input type="text" name="name" value="<?= e($values['name']) ?>" required maxlength="150" autocomplete="name"></label>
  <?php if (!empty($errors['name'])): ?><small class="error"><?= e($errors['name']) ?></small><?php endif; ?>
  <label class="field"><span>E-mail (identifiant de connexion)</span><input type="email" name="email" value="<?= e($values['email']) ?>" required autocomplete="username"></label>
  <?php if (!empty($errors['email'])): ?><small class="error"><?= e($errors['email']) ?></small><?php endif; ?>
  <label class="field"><span>Mot de passe (12 caractères minimum, lettres et chiffres)</span><input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
  <?php if (!empty($errors['password'])): ?><small class="error"><?= e($errors['password']) ?></small><?php endif; ?>
  <label class="field"><span>Confirmer le mot de passe</span><input type="password" name="confirm" required minlength="12" autocomplete="new-password"></label>
  <?php if (!empty($errors['confirm'])): ?><small class="error"><?= e($errors['confirm']) ?></small><?php endif; ?>
  <button class="btn btn--primary btn--block" type="submit"<?= $ready ? '' : ' disabled' ?>>Installer le site</button>
</form>
