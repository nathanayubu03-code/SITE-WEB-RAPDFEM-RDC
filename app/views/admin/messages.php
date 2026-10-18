<div class="page-head"><h1>Messages reçus</h1></div>
<?php if ($rows): ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><th>Objet</th><th>Expéditeur</th><th>Date</th><th class="actions">Actions</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $m): ?>
      <tr class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
        <td data-label="Objet"><a class="strong" href="<?= e(url('admin/messages/' . $m['id'])) ?>"><?= e($m['subject']) ?></a></td>
        <td data-label="Expéditeur"><?= e($m['name']) ?><br><span class="muted"><?= e($m['email']) ?></span></td>
        <td data-label="Date"><?= e(format_date($m['created_at'], true)) ?></td>
        <td class="actions">
          <form method="post" action="<?= e(url('admin/messages/' . $m['id'] . '/delete')) ?>" data-confirm="Supprimer ce message ?"><?= csrf_field() ?><button class="link-btn link-btn--danger" type="submit">Supprimer</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?= render_partial('admin/_pager', ['p' => $pager]) ?>
<?php else: ?><div class="panel empty">Aucun message reçu.</div><?php endif; ?>
