<div class="page-head"><h1><?= e($message['subject']) ?></h1><a class="btn" href="<?= e(url('admin/messages')) ?>">Retour aux messages</a></div>
<section class="panel">
  <dl class="meta">
    <div><dt>De</dt><dd><?= e($message['name']) ?></dd></div>
    <div><dt>E-mail</dt><dd><a href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject']) ?>"><?= e($message['email']) ?></a></dd></div>
    <?php if ($message['phone']): ?><div><dt>Téléphone</dt><dd><?= e($message['phone']) ?></dd></div><?php endif; ?>
    <?php if ($message['organization']): ?><div><dt>Organisation</dt><dd><?= e($message['organization']) ?></dd></div><?php endif; ?>
    <div><dt>Reçu le</dt><dd><?= e(format_date($message['created_at'], true)) ?></dd></div>
  </dl>
  <div class="message-body"><?= nl2br(e($message['message'])) ?></div>
  <div class="form-actions">
    <a class="btn btn--primary" href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode('Re: ' . $message['subject']) ?>">Répondre par e-mail</a>
    <form method="post" action="<?= e(url('admin/messages/' . $message['id'] . '/delete')) ?>" data-confirm="Supprimer ce message ?"><?= csrf_field() ?><button class="btn btn--danger" type="submit">Supprimer</button></form>
  </div>
</section>
