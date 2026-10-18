<?php $publishField = entity_publish_field($def); ?>
<div class="page-head">
  <h1><?= e($title) ?></h1>
  <div class="page-head__actions">
    <?php if ($item && !empty($def['slug']) && $publishField && (int) $item[$publishField]):
      $publicPath = ['projects' => 'projets/', 'articles' => 'actualites/', 'albums' => 'galerie/'][$name] ?? null;
      if ($publicPath): ?><a class="btn" href="<?= e(url($publicPath . $item['slug'])) ?>" target="_blank" rel="noopener">Voir sur le site</a><?php endif; endif; ?>
    <a class="btn" href="<?= e(url('admin/' . $name)) ?>">Retour à la liste</a>
  </div>
</div>

<form class="panel form" method="post" enctype="multipart/form-data" action="<?= e(url('admin/' . $name . ($item ? '/' . $item['id'] . '/edit' : '/new'))) ?>">
  <?= csrf_field() ?>
  <div class="form-grid">
    <?php foreach ($def['fields'] as $fname => $f):
      if (!empty($f['publish']) && !can('publish')) continue; ?>
      <?= render_partial('admin/_field', ['fname' => $fname, 'f' => $f, 'value' => $values[$fname] ?? null, 'error' => $errors[$fname] ?? null, 'item' => $item]) ?>
      <?php if (!empty($def['slug']) && $fname === $def['slug']): ?>
        <?= render_partial('admin/_field', ['fname' => 'slug', 'f' => ['type' => 'text', 'label' => 'Adresse de la page (slug)', 'max' => 120, 'help' => 'Laisser vide pour la générer à partir du titre. Lettres minuscules, chiffres et tirets.'], 'value' => $values['slug'] ?? '', 'error' => null, 'item' => $item]) ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php if ($publishField && !can('publish')): ?>
    <p class="notice">Votre rôle permet de préparer des contenus. Un éditeur ou un administrateur les mettra en ligne.</p>
  <?php endif; ?>
  <div class="form-actions">
    <button class="btn btn--primary" type="submit">Enregistrer</button>
    <a href="<?= e(url('admin/' . $name)) ?>">Annuler</a>
  </div>
</form>

<?php if ($item && !empty($def['after_form'])): ?>
  <?= render_partial($def['after_form'], ['item' => $item, 'media' => $media ?? []]) ?>
<?php elseif (!$item && !empty($def['after_form'])): ?>
  <p class="notice">Enregistrez d'abord l'album pour pouvoir y ajouter des photos et des vidéos.</p>
<?php endif; ?>
