<?php
/** @var string $fname @var array $f @var mixed $value @var ?string $error */
$type = $f['type'];
$id = 'f_' . $fname;
$req = !empty($f['required']);
?>
<div class="field<?= $error ? ' has-error' : '' ?><?= in_array($type, ['markup', 'textarea'], true) ? ' field--wide' : '' ?>">
  <?php if ($type === 'checkbox'): ?>
    <label class="check"><input type="checkbox" name="<?= e($fname) ?>" value="1"<?= (int) $value ? ' checked' : '' ?>> <?= e($f['label']) ?></label>
  <?php else: ?>
    <label for="<?= e($id) ?>"><?= e($f['label']) ?><?= $req ? ' <span class="req">*</span>' : '' ?></label>
    <?php switch ($type):
      case 'textarea':
      case 'markup': ?>
        <textarea id="<?= e($id) ?>" name="<?= e($fname) ?>" rows="<?= (int) ($f['rows'] ?? 5) ?>"<?= isset($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '' ?><?= $req ? ' required' : '' ?>><?= e($value) ?></textarea>
        <?php break;
      case 'select': ?>
        <select id="<?= e($id) ?>" name="<?= e($fname) ?>"<?= $req ? ' required' : '' ?>>
          <option value="">Choisir…</option>
          <?php foreach ($f['options'] as $k => $label): ?><option value="<?= e($k) ?>"<?= (string) $value === (string) $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <?php break;
      case 'relation':
        $opts = db_all("SELECT id, {$f['display']} AS label FROM {$f['table']} ORDER BY {$f['display']}"); ?>
        <select id="<?= e($id) ?>" name="<?= e($fname) ?>">
          <option value="">Aucun</option>
          <?php foreach ($opts as $o): ?><option value="<?= (int) $o['id'] ?>"<?= (int) $value === (int) $o['id'] ? ' selected' : '' ?>><?= e($o['label']) ?></option><?php endforeach; ?>
        </select>
        <?php break;
      case 'image':
      case 'pdf': ?>
        <?php if ($value && $type === 'image'): ?>
          <div class="current-file"><img src="<?= e(upload_url((string) $value, true)) ?>" alt=""></div>
        <?php elseif ($value && $type === 'pdf' && !empty($item)): ?>
          <div class="current-file"><a href="<?= e(url('admin/publications/' . $item['id'] . '/file')) ?>" target="_blank" rel="noopener">Voir le PDF actuel<?= !empty($item['file_size']) ? ' (' . e(human_size((int) $item['file_size'])) . ')' : '' ?></a></div>
        <?php endif; ?>
        <input id="<?= e($id) ?>" type="file" name="<?= e($fname) ?>" accept="<?= $type === 'image' ? 'image/jpeg,image/png,image/webp' : 'application/pdf' ?>"<?= $req && !$value ? ' required' : '' ?>>
        <?php if ($value && !$req): ?><label class="check check--small"><input type="checkbox" name="<?= e($fname) ?>__remove" value="1"> Retirer le fichier actuel</label><?php endif; ?>
        <?php if ($value): ?><small class="help">Choisir un nouveau fichier remplace l'actuel.</small><?php endif; ?>
        <?php break;
      default:
        $htmlType = match ($type) { 'url' => 'url', 'email' => 'email', 'number' => 'number', 'date' => 'date', 'datetime' => 'datetime-local', default => 'text' };
        if ($type === 'datetime' && $value) { $value = str_replace(' ', 'T', substr((string) $value, 0, 16)); } ?>
        <input id="<?= e($id) ?>" type="<?= $htmlType ?>" name="<?= e($fname) ?>" value="<?= e($value) ?>"<?= isset($f['max']) && $type !== 'number' ? ' maxlength="' . (int) $f['max'] . '"' : '' ?><?= $type === 'number' && isset($f['min']) ? ' min="' . (int) $f['min'] . '" max="' . (int) $f['max'] . '"' : '' ?><?= $req ? ' required' : '' ?>>
    <?php endswitch; ?>
  <?php endif; ?>
  <?php if (!empty($f['help'])): ?><small class="help"><?= e($f['help']) ?></small><?php endif; ?>
  <?php if ($error): ?><small class="error"><?= e($error) ?></small><?php endif; ?>
</div>
