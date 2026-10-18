<section class="section error-page">
  <div class="container narrow">
    <p class="error-page__code"><?= (int) $code ?></p>
    <h1><?= e($title) ?></h1>
    <p class="lead"><?= e($message ?: ($code === 404 ? 'La page demandée n\'existe pas ou a été déplacée.' : 'Une erreur est survenue.')) ?></p>
    <a class="btn btn--navy" href="<?= e(url()) ?>">Retour à l'accueil</a>
  </div>
</section>
