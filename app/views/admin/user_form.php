<div class="page-head"><h1><?= e($title) ?></h1><a class="btn" href="<?= e(url('admin/users')) ?>">Retour</a></div>
<form class="panel form" method="post" action="<?= e(url($user ? 'admin/users/' . $user['id'] . '/edit' : 'admin/users/new')) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <?= render_partial('admin/_field', ['fname' => 'name', 'f' => ['type' => 'text', 'label' => 'Nom complet', 'required' => true, 'max' => 150], 'value' => $values['name'], 'error' => $errors['name'] ?? null]) ?>
    <?= render_partial('admin/_field', ['fname' => 'email', 'f' => ['type' => 'email', 'label' => 'E-mail', 'required' => true], 'value' => $values['email'], 'error' => $errors['email'] ?? null]) ?>
    <?= render_partial('admin/_field', ['fname' => 'role', 'f' => ['type' => 'select', 'label' => 'Rôle', 'options' => ROLES, 'required' => true], 'value' => $values['role'], 'error' => $errors['role'] ?? null]) ?>
    <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
      <label for="f_password">Mot de passe<?= $user ? ' (laisser vide pour ne pas changer)' : ' <span class="req">*</span>' ?></label>
      <input id="f_password" type="password" name="password" autocomplete="new-password"<?= $user ? '' : ' required' ?> minlength="12">
      <small class="help">12 caractères minimum, avec des lettres et des chiffres.</small>
      <?php if (isset($errors['password'])): ?><small class="error"><?= e($errors['password']) ?></small><?php endif; ?>
    </div>
    <?= render_partial('admin/_field', ['fname' => 'active', 'f' => ['type' => 'checkbox', 'label' => 'Compte actif'], 'value' => $values['active'], 'error' => null]) ?>
  </div>
  <div class="form-actions"><button class="btn btn--primary" type="submit">Enregistrer</button></div>
</form>
