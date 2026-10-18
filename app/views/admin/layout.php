<?php
$nav = [
    'Contenus' => [
        'projects' => ['admin/projects', 'Projets', 'content'],
        'articles' => ['admin/articles', 'Actualités', 'content'],
        'albums' => ['admin/albums', 'Galerie', 'content'],
        'publications' => ['admin/publications', 'Publications', 'content'],
        'partners' => ['admin/partners', 'Partenaires', 'content'],
    ],
    'Page d\'accueil' => [
        'slides' => ['admin/slides', 'Bannière', 'content'],
        'stats' => ['admin/stats', 'Chiffres clés', 'content'],
        'domains' => ['admin/domains', 'Domaines d\'intervention', 'content'],
    ],
    'Administration' => [
        'messages' => ['admin/messages', 'Messages', 'messages'],
        'users' => ['admin/users', 'Utilisateurs', 'users'],
        'settings' => ['admin/settings', 'Paramètres du site', 'settings'],
        'backups' => ['admin/backups', 'Sauvegardes', 'backups'],
        'activity' => ['admin/activity', 'Journal d\'activité', 'users'],
    ],
];
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Administration <?= e(setting('org_name', 'RAPDFEM')) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<aside class="sidebar" id="sidebar">
  <a class="sidebar__brand" href="<?= e(url('admin')) ?>"><span class="sidebar__mark"></span><?= e(setting('org_name', 'RAPDFEM')) ?><small>Administration</small></a>
  <nav>
    <a href="<?= e(url('admin')) ?>" class="<?= $section === '' ? 'is-active' : '' ?>">Tableau de bord</a>
    <?php foreach ($nav as $group => $links): ?>
      <?php $visible = array_filter($links, fn($l) => can($l[2])); if (!$visible) continue; ?>
      <p class="sidebar__group"><?= e($group) ?></p>
      <?php foreach ($visible as $key => [$href, $label]): ?>
        <a href="<?= e(url($href)) ?>" class="<?= $section === $key ? 'is-active' : '' ?>"><?= e($label) ?><?php if ($key === 'messages' && $unread): ?> <span class="pill"><?= (int) $unread ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
</aside>
<div class="main">
  <header class="topbar">
    <button class="menu-btn" type="button" data-toggle-sidebar aria-controls="sidebar" aria-expanded="false">Menu</button>
    <div class="topbar__right">
      <a href="<?= e(url()) ?>" target="_blank" rel="noopener">Voir le site</a>
      <a href="<?= e(url('admin/account')) ?>" class="<?= $section === 'account' ? 'is-active' : '' ?>"><?= e($me['name']) ?> <span class="muted">(<?= e(ROLES[$me['role']] ?? '') ?>)</span></a>
      <form method="post" action="<?= e(url('admin/logout')) ?>"><?= csrf_field() ?><button type="submit" class="link-btn">Déconnexion</button></form>
    </div>
  </header>
  <main class="content">
    <?php foreach (flashes() as $f): ?>
      <div class="alert alert--<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
  </main>
</div>
<?php if ($section === 'account'): ?><script src="<?= e(asset('js/vendor/qrcode-generator.js')) ?>" defer></script><?php endif; ?>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
