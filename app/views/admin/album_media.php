<section class="panel" id="medias">
  <h2>Photos et vidéos de l'album</h2>
  <div class="cols">
    <form method="post" enctype="multipart/form-data" action="<?= e(url('admin/albums/' . $item['id'] . '/media/upload')) ?>" class="stack">
      <?= csrf_field() ?>
      <h3>Ajouter des photos</h3>
      <label class="field"><span>Photos (JPG, PNG ou WebP, plusieurs possibles)</span><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
      <label class="field"><span>Légende commune (facultatif)</span><input type="text" name="caption" maxlength="300"></label>
      <small class="help">Les photos sont redimensionnées automatiquement et leurs métadonnées (dont la position GPS) sont supprimées.</small>
      <button class="btn btn--primary" type="submit">Téléverser</button>
    </form>
    <form method="post" action="<?= e(url('admin/albums/' . $item['id'] . '/media/video')) ?>" class="stack">
      <?= csrf_field() ?>
      <h3>Ajouter une vidéo YouTube</h3>
      <label class="field"><span>Lien de la vidéo</span><input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." required></label>
      <label class="field"><span>Légende</span><input type="text" name="caption" maxlength="300"></label>
      <button class="btn" type="submit">Ajouter la vidéo</button>
    </form>
  </div>

  <?php if ($media): ?>
    <form method="post" action="<?= e(url('admin/albums/' . $item['id'] . '/media/save')) ?>" id="media-form">
      <?= csrf_field() ?>
    </form>
    <div class="media-grid">
      <?php foreach ($media as $m): $yt = youtube_id($m['video_url']); ?>
        <div class="media-item">
          <?php if ($m['type'] === 'photo'): ?>
            <img src="<?= e(upload_url($m['file'], true)) ?>" alt="">
          <?php elseif ($yt): ?>
            <img src="https://img.youtube.com/vi/<?= e($yt) ?>/mqdefault.jpg" alt=""><span class="media-item__tag">Vidéo</span>
          <?php endif; ?>
          <input type="text" form="media-form" name="captions[<?= (int) $m['id'] ?>]" value="<?= e($m['caption']) ?>" placeholder="Légende" maxlength="300" aria-label="Légende">
          <div class="media-item__row">
            <label>Ordre <input type="number" form="media-form" name="positions[<?= (int) $m['id'] ?>]" value="<?= (int) $m['position'] ?>" min="0" max="9999"></label>
            <?php if (can('delete')): ?>
              <form method="post" action="<?= e(url('admin/media/' . $m['id'] . '/delete')) ?>" data-confirm="Retirer cet élément de l'album ?">
                <?= csrf_field() ?><button type="submit" class="link-btn link-btn--danger">Retirer</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn--primary" type="submit" form="media-form">Enregistrer légendes et ordre</button>
  <?php else: ?>
    <p class="muted">L'album ne contient encore aucun élément.</p>
  <?php endif; ?>
</section>
