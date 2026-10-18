<?php $publishField = entity_publish_field($def); ?>
<div class="page-head">
  <h1><?= e($def['label']) ?></h1>
  <a class="btn btn--primary" href="<?= e(url('admin/' . $name . '/new')) ?>">Ajouter <?= e($def['singular']) ?></a>
</div>

<?php if (!empty($def['search'])): ?>
<form class="search" method="get" action="<?= e(url('admin/' . $name)) ?>">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Rechercher…" aria-label="Rechercher">
  <button class="btn" type="submit">Rechercher</button>
  <?php if ($q !== ''): ?><a href="<?= e(url('admin/' . $name)) ?>">Effacer</a><?php endif; ?>
</form>
<?php endif; ?>

<?php if ($rows): ?>
<div class="table-wrap">
<table class="table">
  <thead><tr><?php foreach ($def['list'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?><th class="actions">Actions</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $row): ?>
    <tr>
      <?php $first = true; foreach ($def['list'] as $col => $label): $f = $def['fields'][$col] ?? ['type' => 'text']; $v = $row[$col] ?? ''; ?>
        <td data-label="<?= e($label) ?>">
          <?php
          $out = match ($f['type']) {
              'checkbox' => (int) $v ? '<span class="status status--on">Oui</span>' : '<span class="status status--off">Non</span>',
              'select' => e($f['options'][$v] ?? $v),
              'relation' => e($relations[$col][$v] ?? ''),
              'datetime' => e(format_date((string) $v)),
              default => e(mb_strimwidth((string) $v, 0, 80, '…')),
          };
          if ($first): ?><a class="strong" href="<?= e(url('admin/' . $name . '/' . $row['id'] . '/edit')) ?>"><?= $out ?></a><?php else: echo $out; endif; $first = false; ?>
        </td>
      <?php endforeach; ?>
      <td class="actions">
        <a href="<?= e(url('admin/' . $name . '/' . $row['id'] . '/edit')) ?>">Modifier</a>
        <?php if (can('delete')): ?>
          <form method="post" action="<?= e(url('admin/' . $name . '/' . $row['id'] . '/delete')) ?>" data-confirm="Supprimer définitivement cet élément ?">
            <?= csrf_field() ?><button type="submit" class="link-btn link-btn--danger">Supprimer</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?= render_partial('admin/_pager', ['p' => $pager]) ?>
<?php else: ?>
  <div class="panel empty">Aucun élément<?= $q !== '' ? ' pour cette recherche' : '' ?>. <a href="<?= e(url('admin/' . $name . '/new')) ?>">Ajouter <?= e($def['singular']) ?></a></div>
<?php endif; ?>
