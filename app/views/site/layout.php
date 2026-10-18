<?php
$orgName = setting('org_name', 'RAPDFEM');
$fullName = setting('org_full_name');
$pageTitle = $meta_title ? $meta_title . ' | ' . $orgName : $orgName . ($fullName && !str_contains($fullName, '[') ? ' | ' . $fullName : '');
$description = $meta_description ?: setting('meta_description');
$canonical = abs_url(current_path());
$ogImage = $meta_image ? abs_url('uploads/' . $meta_image) : (setting('logo') ? abs_url('uploads/' . setting('logo')) : '');
$nav = [
    'home' => ['', 'Accueil'],
    'about' => ['qui-sommes-nous', 'Qui sommes-nous ?'],
    'projects' => ['projets', 'Nos projets'],
    'news' => ['actualites', 'Actualités'],
    'gallery' => ['galerie', 'Galerie'],
    'publications' => ['publications', 'Publications'],
    'partners' => ['partenaires', 'Nos partenaires'],
    'contact' => ['contact', 'Contact'],
];
$socials = social_links();
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'NGO',
    'name' => $orgName,
    'url' => abs_url(),
    'areaServed' => 'République Démocratique du Congo',
];
if (setting('email')) { $jsonLd['email'] = setting('email'); }
if (setting('phone')) { $jsonLd['telephone'] = setting('phone'); }
if (setting('logo')) { $jsonLd['logo'] = abs_url('uploads/' . setting('logo')); }
if ($socials) { $jsonLd['sameAs'] = array_values(array_column($socials, 'href')); }
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:site_name" content="<?= e($orgName) ?>">
<meta property="og:title" content="<?= e($meta_title ?: $orgName) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:type" content="<?= e($meta_type ?? 'website') ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="fr_FR">
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
<meta name="theme-color" content="#0F2043">
<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
</head>
<body>
<a class="skip-link" href="#contenu">Aller au contenu</a>

<div class="topbar">
  <div class="container topbar__inner">
    <span><?= e(setting('tagline')) ?></span>
    <span class="topbar__contact">
      <?php if (setting('phone')): ?><a href="tel:<?= e(preg_replace('/[^\d+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a><?php endif; ?>
      <?php if (setting('email')): ?><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a><?php endif; ?>
    </span>
  </div>
</div>

<header class="header" id="haut">
  <div class="container header__inner">
    <a class="brand" href="<?= e(url()) ?>" aria-label="<?= e($orgName) ?>, accueil">
      <?php if (setting('logo')): ?>
        <img src="<?= e(upload_url(setting('logo'))) ?>" alt="<?= e($orgName) ?>" class="brand__logo">
      <?php else: ?>
        <span class="brand__mark" aria-hidden="true"></span>
        <span class="brand__text"><?= e($orgName) ?><small>ONG &middot; RDC</small></span>
      <?php endif; ?>
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
      <span class="nav-toggle__bar"></span><span class="visually-hidden">Menu</span>
    </button>
    <nav class="nav" id="menu-principal" aria-label="Navigation principale">
      <ul>
        <?php foreach ($nav as $key => [$href, $label]): ?>
          <li><a href="<?= e(url($href)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?> class="<?= $key === 'contact' ? 'nav__cta' : '' ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>

<main id="contenu">
<?php foreach (flashes() as $f): ?>
  <div class="container"><div class="alert alert--<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div></div>
<?php endforeach; ?>
<?= $content ?>
</main>

<footer class="footer">
  <div class="container footer__grid">
    <div>
      <p class="footer__brand"><?= e($orgName) ?></p>
      <?php if ($fullName): ?><p class="footer__muted"><?= e($fullName) ?></p><?php endif; ?>
      <p class="footer__muted"><?= e(excerpt(setting('mission'), 180)) ?></p>
    </div>
    <div>
      <h2 class="footer__title">Navigation</h2>
      <ul class="footer__links">
        <?php foreach (array_slice($nav, 1) as [$href, $label]): ?>
          <li><a href="<?= e(url($href)) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h2 class="footer__title">Nous contacter</h2>
      <address class="footer__muted"><?= nl2br(e(setting('address'))) ?></address>
      <?php if (setting('phone')): ?><p><a href="tel:<?= e(preg_replace('/[^\d+]/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p><?php endif; ?>
      <?php if (setting('email')): ?><p><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p><?php endif; ?>
    </div>
    <div>
      <h2 class="footer__title">Suivez-nous</h2>
      <?php if ($socials): ?>
        <ul class="footer__social">
          <?php foreach ($socials as $s): ?>
            <li><a href="<?= e($s['href']) ?>" target="_blank" rel="noopener noreferrer"><?= e($s['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="footer__muted">Réseaux sociaux à renseigner dans l'administration.</p>
      <?php endif; ?>
      <a class="btn btn--green btn--small" href="<?= e(url('publications')) ?>">Nos rapports</a>
    </div>
  </div>
  <div class="footer__bottom">
    <div class="container footer__bottom-inner">
      <span>&copy; <?= date('Y') ?> <?= e($orgName) ?>. Tous droits réservés.</span>
      <a href="#haut">Haut de page</a>
    </div>
  </div>
</footer>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
