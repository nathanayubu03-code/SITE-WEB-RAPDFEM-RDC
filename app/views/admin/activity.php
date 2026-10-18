<div class="page-head"><h1>Journal d'activité</h1></div>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Élément</th><th>Adresse IP</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $a): ?>
      <tr>
        <td data-label="Date"><?= e(format_date($a['created_at'], true)) ?></td>
        <td data-label="Utilisateur"><?= e($a['user_name'] ?? 'Système') ?></td>
        <td data-label="Action"><?= e($a['action']) ?></td>
        <td data-label="Élément"><?= e(($a['entity'] ?? '') . ($a['entity_id'] ? ' #' . $a['entity_id'] : '')) ?></td>
        <td data-label="IP"><?= e($a['ip']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
