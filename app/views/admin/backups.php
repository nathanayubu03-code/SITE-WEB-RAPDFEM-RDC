<div class="page-head">
  <h1>Sauvegardes</h1>
  <form method="post" action="<?= e(url('admin/backups/create')) ?>"><?= csrf_field() ?><button class="btn btn--primary" type="submit">Créer une sauvegarde maintenant</button></form>
</div>
<div class="panel">
  <p>Chaque archive contient la base de données, les photos et les PDF. Les <?= (int) config('backups_keep', 14) ?> plus récentes sont conservées sur le serveur.</p>
  <p class="muted">Sauvegarde automatique : planifiez la commande <code>php bin/admin.php backup</code> chaque nuit dans les tâches cron de l'hébergeur. Téléchargez régulièrement une copie hors du serveur (disque externe, Google Drive) : une sauvegarde stockée au même endroit que le site ne protège pas d'une panne de l'hébergeur.</p>
</div>
<?php if ($backups): ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Fichier</th><th>Date</th><th>Taille</th><th class="actions"></th></tr></thead>
  <tbody>
    <?php foreach ($backups as $b): ?>
      <tr>
        <td data-label="Fichier"><?= e($b['name']) ?></td>
        <td data-label="Date"><?= e(format_date($b['date'], true)) ?></td>
        <td data-label="Taille"><?= e(human_size((int) $b['size'])) ?></td>
        <td class="actions"><a href="<?= e(url('admin/backups/download/' . $b['name'])) ?>">Télécharger</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?><div class="panel empty">Aucune sauvegarde pour le moment.</div><?php endif; ?>
