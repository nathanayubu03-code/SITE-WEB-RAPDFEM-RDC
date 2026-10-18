<div class="page-head"><h1>Paramètres du site</h1></div>
<form class="form" method="post" enctype="multipart/form-data" action="<?= e(url('admin/settings')) ?>">
  <?= csrf_field() ?>
  <?php foreach (settings_fields() as $group => $fields): ?>
    <section class="panel">
      <h2><?= e($group) ?></h2>
      <div class="form-grid">
        <?php foreach ($fields as $key => $f):
          $value = $posted !== null && $f['type'] !== 'image' ? (string) ($posted[$key] ?? '') : setting($key); ?>
          <?= render_partial('admin/_field', ['fname' => $key, 'f' => $f + ['help' => $f['type'] === 'markup' ? 'Mise en forme : ## Titre, - liste, **gras**, [lien](https://...).' : null], 'value' => $value, 'error' => $errors[$key] ?? null]) ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <div class="form-actions form-actions--sticky"><button class="btn btn--primary" type="submit">Enregistrer les paramètres</button></div>
</form>
