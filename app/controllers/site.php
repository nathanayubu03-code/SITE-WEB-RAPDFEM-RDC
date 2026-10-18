<?php
declare(strict_types=1);

function site_dispatch(string $path): void
{
    $routes = [
        '' => 'page_home',
        'qui-sommes-nous' => 'page_about',
        'projets' => 'page_projects',
        'projets/([a-z0-9-]+)' => 'page_project',
        'actualites' => 'page_news',
        'actualites/([a-z0-9-]+)' => 'page_article',
        'galerie' => 'page_gallery',
        'galerie/([a-z0-9-]+)' => 'page_album',
        'publications' => 'page_publications',
        'publications/telecharger/(\d+)' => 'page_publication_download',
        'partenaires' => 'page_partners',
        'contact' => 'page_contact',
        'sitemap.xml' => 'page_sitemap',
        'robots.txt' => 'page_robots',
    ];
    foreach ($routes as $pattern => $handler) {
        if (preg_match('#^' . $pattern . '$#', $path, $m)) {
            array_shift($m);
            $handler(...$m);
            return;
        }
    }
    abort(404);
}

function site_render(string $view, array $data = [], bool $recaptcha = false): void
{
    send_security_headers('site', $recaptcha);
    $data += ['meta_title' => null, 'meta_description' => null, 'meta_image' => null, 'active' => ''];
    render('site/' . $view, $data, 'site/layout');
}

function render_error_page(int $code, string $message): void
{
    $titles = [403 => 'Accès refusé', 404 => 'Page introuvable', 419 => 'Session expirée'];
    if (str_starts_with(current_path(), 'admin')) {
        send_security_headers('admin');
        render('admin/error', ['code' => $code, 'title' => $titles[$code] ?? 'Erreur', 'message' => $message], 'admin/layout_bare');
        return;
    }
    site_render('error', ['code' => $code, 'title' => $titles[$code] ?? 'Erreur', 'message' => $message, 'meta_title' => $titles[$code] ?? 'Erreur']);
}

/* ---------- Pages ---------- */

function page_home(): void
{
    site_render('home', [
        'slides' => db_all('SELECT * FROM slides WHERE active = 1 ORDER BY position, id'),
        'stats' => db_all('SELECT * FROM stats WHERE active = 1 ORDER BY position, id'),
        'domains' => db_all('SELECT * FROM domains ORDER BY position, id LIMIT 6'),
        'projects' => db_all("SELECT * FROM projects WHERE published = 1 ORDER BY CASE status WHEN 'en_cours' THEN 0 ELSE 1 END, created_at DESC LIMIT 3"),
        'articles' => db_all('SELECT * FROM articles WHERE published = 1 AND published_at <= ? ORDER BY published_at DESC LIMIT 3', [now()]),
        'partners' => db_all('SELECT * FROM partners WHERE active = 1 ORDER BY position, name LIMIT 12'),
        'active' => 'home',
    ]);
}

function page_about(): void
{
    site_render('about', [
        'domains' => db_all('SELECT * FROM domains ORDER BY position, id'),
        'stats' => db_all('SELECT * FROM stats WHERE active = 1 ORDER BY position, id'),
        'meta_title' => 'Qui sommes-nous ?',
        'meta_description' => excerpt(setting('about_intro'), 160),
        'active' => 'about',
    ]);
}

function page_projects(): void
{
    $zone = query('zone');
    $status = query('statut');
    $where = ['published = 1'];
    $params = [];
    if ($zone !== '' && isset(zones()[$zone])) {
        $where[] = 'zone = ?';
        $params[] = $zone;
    } else {
        $zone = '';
    }
    if ($status !== '' && isset(PROJECT_STATUSES[$status])) {
        $where[] = 'status = ?';
        $params[] = $status;
    } else {
        $status = '';
    }
    $sqlWhere = implode(' AND ', $where);
    $p = paginate((int) db_val("SELECT COUNT(*) FROM projects WHERE $sqlWhere", $params), 9, (int) query('page', '1'));
    $projects = db_all("SELECT * FROM projects WHERE $sqlWhere ORDER BY CASE status WHEN 'en_cours' THEN 0 WHEN 'planifie' THEN 1 ELSE 2 END, start_date DESC, created_at DESC LIMIT {$p['per']} OFFSET {$p['offset']}", $params);
    $usedZones = db_all('SELECT zone, COUNT(*) AS n FROM projects WHERE published = 1 GROUP BY zone ORDER BY zone');

    site_render('projects', [
        'projects' => $projects, 'pager' => $p, 'zone' => $zone, 'status' => $status, 'usedZones' => $usedZones,
        'meta_title' => 'Nos projets' . ($zone ? ' : ' . $zone : ''),
        'meta_description' => 'Projets réalisés et en cours de RAPDFEM en République Démocratique du Congo, par zone d\'intervention.',
        'active' => 'projects',
    ]);
}

function page_project(string $slug): void
{
    $project = db_one('SELECT * FROM projects WHERE slug = ? AND published = 1', [$slug]) ?? abort(404);
    $albums = db_all('SELECT * FROM albums WHERE project_id = ? AND published = 1 ORDER BY created_at DESC', [$project['id']]);
    $photos = db_all("SELECT m.* FROM media m JOIN albums a ON a.id = m.album_id WHERE a.project_id = ? AND a.published = 1 AND m.type = 'photo' ORDER BY m.position, m.id LIMIT 8", [$project['id']]);
    $related = db_all('SELECT * FROM projects WHERE published = 1 AND id <> ? AND (zone = ? OR domain = ?) ORDER BY created_at DESC LIMIT 3', [$project['id'], $project['zone'], $project['domain']]);
    site_render('project', [
        'project' => $project, 'albums' => $albums, 'photos' => $photos, 'related' => $related,
        'meta_title' => $project['title'],
        'meta_description' => excerpt($project['summary'], 160),
        'meta_image' => $project['image'],
        'active' => 'projects',
    ]);
}

function page_news(): void
{
    $type = query('type');
    $where = 'published = 1 AND published_at <= ?';
    $params = [now()];
    if ($type !== '' && isset(ARTICLE_TYPES[$type])) {
        $where .= ' AND type = ?';
        $params[] = $type;
    } else {
        $type = '';
    }
    $p = paginate((int) db_val("SELECT COUNT(*) FROM articles WHERE $where", $params), 9, (int) query('page', '1'));
    site_render('news', [
        'articles' => db_all("SELECT * FROM articles WHERE $where ORDER BY published_at DESC LIMIT {$p['per']} OFFSET {$p['offset']}", $params),
        'pager' => $p, 'type' => $type,
        'meta_title' => 'Actualités',
        'meta_description' => 'Actualités, communiqués officiels et brèves humanitaires de RAPDFEM en RDC.',
        'active' => 'news',
    ]);
}

function page_article(string $slug): void
{
    $article = db_one('SELECT * FROM articles WHERE slug = ? AND published = 1 AND published_at <= ?', [$slug, now()]) ?? abort(404);
    site_render('article', [
        'article' => $article,
        'recent' => db_all('SELECT * FROM articles WHERE published = 1 AND published_at <= ? AND id <> ? ORDER BY published_at DESC LIMIT 3', [now(), $article['id']]),
        'meta_title' => $article['title'],
        'meta_description' => excerpt($article['excerpt'] ?: $article['body'], 160),
        'meta_image' => $article['image'],
        'meta_type' => 'article',
        'active' => 'news',
    ]);
}

function page_gallery(): void
{
    $albums = db_all("SELECT a.*, p.title AS project_title,
            (SELECT COUNT(*) FROM media m WHERE m.album_id = a.id) AS media_count,
            (SELECT m.file FROM media m WHERE m.album_id = a.id AND m.type = 'photo' ORDER BY m.position, m.id LIMIT 1) AS first_photo,
            (SELECT m.video_url FROM media m WHERE m.album_id = a.id AND m.type = 'video' ORDER BY m.position, m.id LIMIT 1) AS first_video
        FROM albums a LEFT JOIN projects p ON p.id = a.project_id
        WHERE a.published = 1 ORDER BY a.created_at DESC");
    site_render('gallery', [
        'albums' => $albums,
        'meta_title' => 'Galerie',
        'meta_description' => 'Photos et vidéos de terrain des projets de RAPDFEM en RDC.',
        'active' => 'gallery',
    ]);
}

function page_album(string $slug): void
{
    $album = db_one('SELECT a.*, p.title AS project_title, p.slug AS project_slug, p.published AS project_published
        FROM albums a LEFT JOIN projects p ON p.id = a.project_id WHERE a.slug = ? AND a.published = 1', [$slug]) ?? abort(404);
    site_render('album', [
        'album' => $album,
        'media' => db_all('SELECT * FROM media WHERE album_id = ? ORDER BY position, id', [$album['id']]),
        'meta_title' => $album['title'],
        'meta_description' => excerpt($album['description'] ?: 'Album photo : ' . $album['title'], 160),
        'meta_image' => $album['cover'],
        'active' => 'gallery',
    ]);
}

function page_publications(): void
{
    $category = query('categorie');
    $year = (int) query('annee');
    $where = ['published = 1'];
    $params = [];
    if ($category !== '' && isset(PUBLICATION_CATEGORIES[$category])) {
        $where[] = 'category = ?';
        $params[] = $category;
    } else {
        $category = '';
    }
    if ($year > 0) {
        $where[] = 'year = ?';
        $params[] = $year;
    }
    $sqlWhere = implode(' AND ', $where);
    $p = paginate((int) db_val("SELECT COUNT(*) FROM publications WHERE $sqlWhere", $params), 12, (int) query('page', '1'));
    site_render('publications', [
        'publications' => db_all("SELECT * FROM publications WHERE $sqlWhere ORDER BY year DESC, created_at DESC LIMIT {$p['per']} OFFSET {$p['offset']}", $params),
        'years' => db_all('SELECT DISTINCT year FROM publications WHERE published = 1 ORDER BY year DESC'),
        'pager' => $p, 'category' => $category, 'year' => $year,
        'meta_title' => 'Publications',
        'meta_description' => 'Rapports annuels, rapports de projet et notes de RAPDFEM à télécharger.',
        'active' => 'publications',
    ]);
}

function page_publication_download(string $id): void
{
    $pub = db_one('SELECT * FROM publications WHERE id = ? AND published = 1', [(int) $id]) ?? abort(404);
    $path = doc_path((string) $pub['file']) ?? abort(404);
    db_run('UPDATE publications SET downloads = downloads + 1 WHERE id = ?', [$pub['id']]);
    send_pdf($path, $pub['title']);
}

function page_partners(): void
{
    $partners = db_all('SELECT * FROM partners WHERE active = 1 ORDER BY position, name');
    $grouped = [];
    foreach ($partners as $partner) {
        $grouped[$partner['type']][] = $partner;
    }
    site_render('partners', [
        'grouped' => $grouped,
        'meta_title' => 'Nos partenaires',
        'meta_description' => 'Les partenaires techniques et financiers qui soutiennent l\'action de RAPDFEM en RDC.',
        'active' => 'partners',
    ]);
}

function page_contact(): void
{
    if (is_static_demo()) {
        site_render('contact', ['values' => [], 'errors' => [], 'recaptcha' => false, 'meta_title' => 'Contact', 'active' => 'contact']);
        return;
    }
    $recaptcha = (string) config('recaptcha.site_key') !== '' && (string) config('recaptcha.secret_key') !== '';
    $errors = [];
    $values = ['name' => '', 'email' => '', 'phone' => '', 'organization' => '', 'subject' => '', 'message' => ''];

    if (is_post()) {
        csrf_check();
        foreach ($values as $k => $_) {
            $values[$k] = input($k);
        }
        $errors = contact_validate($values, $recaptcha);
        if (!$errors) {
            db_insert('messages', $values + ['ip_hash' => hash('sha256', client_ip()), 'is_read' => 0, 'created_at' => now()]);
            throttle_hit('contact', client_ip());
            contact_notify($values);
            flash('success', 'Merci, votre message a bien été envoyé. Notre équipe vous répondra dans les meilleurs délais.');
            redirect('contact');
        }
    }

    session_boot();
    if (!is_post()) {
        $_SESSION['contact_form_at'] = time();
    }
    site_render('contact', [
        'values' => $values, 'errors' => $errors, 'recaptcha' => $recaptcha,
        'meta_title' => 'Contact',
        'meta_description' => 'Contactez RAPDFEM en République Démocratique du Congo.',
        'active' => 'contact',
    ], $recaptcha);
}

function contact_validate(array $v, bool $recaptcha): array
{
    $errors = [];
    // Pot de miel : champ invisible que seuls les robots remplissent.
    if (input('website') !== '') {
        $errors['form'] = 'Envoi refusé.';
        return $errors;
    }
    if (time() - (int) ($_SESSION['contact_form_at'] ?? 0) < 3) {
        $errors['form'] = 'Formulaire envoyé trop rapidement. Merci de réessayer.';
        return $errors;
    }
    if (throttle_count('contact', client_ip(), 3600) >= 5) {
        $errors['form'] = 'Vous avez envoyé plusieurs messages récemment. Merci de réessayer dans une heure.';
        return $errors;
    }
    if (mb_strlen($v['name']) < 2 || mb_strlen($v['name']) > 150) {
        $errors['name'] = 'Indiquez votre nom.';
    }
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($v['email']) > 191) {
        $errors['email'] = 'Adresse e-mail invalide.';
    }
    if (mb_strlen($v['phone']) > 50) {
        $errors['phone'] = 'Numéro trop long.';
    }
    if (mb_strlen($v['organization']) > 200) {
        $errors['organization'] = 'Nom trop long.';
    }
    if (mb_strlen($v['subject']) < 3 || mb_strlen($v['subject']) > 200) {
        $errors['subject'] = 'Indiquez l\'objet de votre message.';
    }
    if (mb_strlen($v['message']) < 10 || mb_strlen($v['message']) > 5000) {
        $errors['message'] = 'Votre message doit contenir entre 10 et 5 000 caractères.';
    }
    if (!$errors && $recaptcha && !recaptcha_verify(input('g-recaptcha-response'))) {
        $errors['form'] = 'Merci de cocher la case « Je ne suis pas un robot ».';
    }
    return $errors;
}

function recaptcha_verify(string $token): bool
{
    if ($token === '') {
        return false;
    }
    $context = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query(['secret' => config('recaptcha.secret_key'), 'response' => $token, 'remoteip' => client_ip()]),
        'timeout' => 8,
    ]]);
    $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $data = $response ? json_decode($response, true) : null;
    return is_array($data) && !empty($data['success']);
}

function contact_notify(array $v): void
{
    $to = (string) config('mail.to');
    if ($to === '') {
        return;
    }
    $from = (string) config('mail.from') ?: $to;
    // Les retours à la ligne sont retirés des valeurs placées dans les en-têtes (injection d'en-têtes).
    $clean = fn(string $s) => str_replace(["\r", "\n"], ' ', $s);
    $subject = '=?UTF-8?B?' . base64_encode('[Site RAPDFEM] ' . $clean($v['subject'])) . '?=';
    $body = "Nouveau message reçu depuis le formulaire de contact.\n\n"
        . "Nom : {$v['name']}\nE-mail : {$v['email']}\nTéléphone : {$v['phone']}\nOrganisation : {$v['organization']}\n\n"
        . $v['message'] . "\n\n---\nÀ consulter aussi dans l'administration : " . abs_url('admin/messages');
    $headers = "From: $from\r\nReply-To: " . $clean($v['email']) . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    @mail($to, $subject, $body, $headers);
}

function page_sitemap(): void
{
    $urls = [['', null], ['qui-sommes-nous', null], ['projets', null], ['actualites', null], ['galerie', null], ['publications', null], ['partenaires', null], ['contact', null]];
    foreach (db_all('SELECT slug, updated_at FROM projects WHERE published = 1') as $r) {
        $urls[] = ['projets/' . $r['slug'], $r['updated_at']];
    }
    foreach (db_all('SELECT slug, updated_at FROM articles WHERE published = 1 AND published_at <= ?', [now()]) as $r) {
        $urls[] = ['actualites/' . $r['slug'], $r['updated_at']];
    }
    foreach (db_all('SELECT slug, updated_at FROM albums WHERE published = 1') as $r) {
        $urls[] = ['galerie/' . $r['slug'], $r['updated_at']];
    }
    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as [$path, $updated]) {
        echo '  <url><loc>' . e(abs_url($path)) . '</loc>' . ($updated ? '<lastmod>' . substr($updated, 0, 10) . '</lastmod>' : '') . "</url>\n";
    }
    echo '</urlset>';
}

function page_robots(): void
{
    header('Content-Type: text/plain; charset=UTF-8');
    echo "User-agent: *\nDisallow: /admin\nSitemap: " . abs_url('sitemap.xml') . "\n";
}

/* ---------- Petits composants de vue ---------- */

/** Image de contenu, ou visuel neutre aux couleurs de l'organisation si aucune photo n'a été fournie. */
function visual(?string $image, string $alt, bool $thumb = true, string $class = ''): string
{
    if ($image) {
        return '<img class="' . e(trim('visual ' . $class)) . '" src="' . e(upload_url($image, $thumb)) . '" alt="' . e($alt) . '" loading="lazy" decoding="async">';
    }
    return '<div class="' . e(trim('visual visual--placeholder ' . $class)) . '" role="img" aria-label="' . e($alt) . '"><span>' . e(setting('org_name', 'RAPDFEM')) . '</span></div>';
}

function pager(array $p): string
{
    if ($p['pages'] <= 1 || is_static_demo()) {
        return '';
    }
    $html = '<nav class="pager" aria-label="Pagination">';
    if ($p['page'] > 1) {
        $html .= '<a href="' . e(page_url($p['page'] - 1)) . '" rel="prev">Précédent</a>';
    }
    for ($i = 1; $i <= $p['pages']; $i++) {
        $html .= $i === $p['page']
            ? '<span aria-current="page">' . $i . '</span>'
            : '<a href="' . e(page_url($i)) . '">' . $i . '</a>';
    }
    if ($p['page'] < $p['pages']) {
        $html .= '<a href="' . e(page_url($p['page'] + 1)) . '" rel="next">Suivant</a>';
    }
    return $html . '</nav>';
}

function project_period(array $project): string
{
    $start = $project['start_date'] ? substr($project['start_date'], 0, 4) : '';
    $end = $project['end_date'] ? substr($project['end_date'], 0, 4) : '';
    if ($start && $end) {
        return $start === $end ? $start : $start . ' à ' . $end;
    }
    return $start ? 'Depuis ' . $start : '';
}

function social_links(): array
{
    $labels = ['facebook' => 'Facebook', 'x' => 'X', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'instagram' => 'Instagram'];
    $links = [];
    foreach ($labels as $key => $label) {
        $href = safe_link(setting($key));
        if ($href !== '') {
            $links[$key] = ['label' => $label, 'href' => $href];
        }
    }
    return $links;
}
