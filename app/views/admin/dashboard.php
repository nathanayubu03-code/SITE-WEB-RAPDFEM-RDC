<div class="page-head"><h1>Tableau de bord</h1><p class="muted">Bonjour <?= e($me['name']) ?>. Voici l'état du site.</p></div>

<div class="tiles">
  <?php foreach ($counts as $table => $c): ?>
    <a class="tile" href="<?= e(url('admin/' . $table)) ?>">
      <span class="tile__value"><?= $c['total'] ?></span>
      <span class="tile__label"><?= e($c['label']) ?></span>
      <?php if ($c['draft']): ?><span class="tile__note"><?= $c['draft'] ?> hors ligne</span><?php endif; ?>
    </a>
  <?php endforeach; ?>
  <div class="tile tile--green"><span class="tile__value"><?= $downloads ?></span><span class="tile__label">Téléchargements de PDF</span></div>
</div>

<div class="quick">
  <a class="btn btn--primary" href="<?= e(url('admin/articles/new')) ?>">Publier une actualité</a>
  <a class="btn" href="<?= e(url('admin/projects/new')) ?>">Ajouter un projet</a>
  <a class="btn" href="<?= e(url('admin/albums/new')) ?>">Créer un album photo</a>
  <a class="btn" href="<?= e(url('admin/publications/new')) ?>">Ajouter un rapport PDF</a>
</div>

<div class="cols">
  <?php if (can('messages')): ?>
  <section class="panel">
    <h2>Derniers messages <?php if ($unread): ?><span class="pill"><?= (int) $unread ?> non lu(s)</span><?php endif; ?></h2>
    <?php if ($messages): ?>
      <ul class="list">
        <?php foreach ($messages as $m): ?>
          <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>"><a href="<?= e(url('admin/messages/' . $m['id'])) ?>"><?= e($m['subject']) ?></a><span class="muted"><?= e($m['name']) ?>, <?= e(format_date($m['created_at'], true)) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <a href="<?= e(url('admin/messages')) ?>">Tous les messages</a>
    <?php else: ?><p class="muted">Aucun message pour le moment.</p><?php endif; ?>
  </section>
  <?php endif; ?>
  <section class="panel">
    <h2>Activité récente</h2>
    <?php if ($activity): ?>
      <ul class="list">
        <?php foreach ($activity as $a): ?>
          <li><span><?= e($a['user_name'] ?? 'Système') ?> : <?= e($a['action']) ?><?= $a['entity'] ? ' (' . e($a['entity']) . ($a['entity_id'] ? ' #' . (int) $a['entity_id'] : '') . ')' : '' ?></span><span class="muted"><?= e(format_date($a['created_at'], true)) ?></span></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?><p class="muted">Aucune activité.</p><?php endif; ?>
  </section>
</div>
