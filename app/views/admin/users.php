<div class="page-head"><h1>Utilisateurs</h1><a class="btn btn--primary" href="<?= e(url('admin/users/new')) ?>">Nouvel utilisateur</a></div>
<div class="panel">
  <p class="muted"><strong>Administrateur</strong> : tout, y compris utilisateurs, paramètres et sauvegardes. <strong>Éditeur</strong> : crée, publie et supprime les contenus, lit les messages. <strong>Contributeur</strong> : prépare des contenus qu'un éditeur valide.</p>
</div>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>2FA</th><th>Dernière connexion</th><th class="actions">Actions</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $u): ?>
      <tr>
        <td data-label="Nom"><a class="strong" href="<?= e(url('admin/users/' . $u['id'] . '/edit')) ?>"><?= e($u['name']) ?></a><?= (int) $u['active'] ? '' : ' <span class="status status--off">Désactivé</span>' ?></td>
        <td data-label="E-mail"><?= e($u['email']) ?></td>
        <td data-label="Rôle"><?= e(ROLES[$u['role']] ?? $u['role']) ?></td>
        <td data-label="2FA"><?= (int) $u['totp_enabled'] ? '<span class="status status--on">Activée</span>' : '<span class="status status--off">Non</span>' ?></td>
        <td data-label="Dernière connexion"><?= e(format_date($u['last_login_at'], true) ?: 'Jamais') ?></td>
        <td class="actions">
          <a href="<?= e(url('admin/users/' . $u['id'] . '/edit')) ?>">Modifier</a>
          <?php if ((int) $u['id'] !== (int) $me['id']): ?>
            <?php if ((int) $u['totp_enabled']): ?>
              <form method="post" action="<?= e(url('admin/users/' . $u['id'] . '/reset-2fa')) ?>" data-confirm="Réinitialiser la double authentification de cet utilisateur ?"><?= csrf_field() ?><button class="link-btn" type="submit">Réinit. 2FA</button></form>
            <?php endif; ?>
            <form method="post" action="<?= e(url('admin/users/' . $u['id'] . '/delete')) ?>" data-confirm="Supprimer cet utilisateur ?"><?= csrf_field() ?><button class="link-btn link-btn--danger" type="submit">Supprimer</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
