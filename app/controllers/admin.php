<?php
declare(strict_types=1);

require_once APP . '/lib/backup.php';

function admin_dispatch(string $path): void
{
    session_boot();
    $sub = trim(substr($path, 5), '/');

    // Requête plus lourde que post_max_size : PHP vide $_POST et $_FILES.
    if (is_post() && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('error', 'Les fichiers envoyés dépassent la taille maximale autorisée par le serveur. Envoyez moins de fichiers à la fois.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'admin');
    }
    if (is_post()) {
        csrf_check();
    }

    $routes = [
        '' => 'admin_dashboard',
        'login' => 'admin_login',
        'login/2fa' => 'admin_login_2fa',
        'logout' => 'admin_logout',
        'account' => 'admin_account',
        'messages' => 'admin_messages',
        'messages/(\d+)' => 'admin_message',
        'messages/(\d+)/delete' => 'admin_message_delete',
        'users' => 'admin_users',
        'users/new' => 'admin_user_form',
        'users/(\d+)/edit' => 'admin_user_form',
        'users/(\d+)/delete' => 'admin_user_delete',
        'users/(\d+)/reset-2fa' => 'admin_user_reset_2fa',
        'settings' => 'admin_settings',
        'backups' => 'admin_backups',
        'backups/create' => 'admin_backup_create',
        'backups/download/([\w.-]+)' => 'admin_backup_download',
        'activity' => 'admin_activity',
        'publications/(\d+)/file' => 'admin_publication_file',
        'albums/(\d+)/media/upload' => 'admin_media_upload',
        'albums/(\d+)/media/video' => 'admin_media_video',
        'albums/(\d+)/media/save' => 'admin_media_save',
        'media/(\d+)/delete' => 'admin_media_delete',
        '([a-z]+)' => 'admin_entity_list',
        '([a-z]+)/new' => 'admin_entity_form',
        '([a-z]+)/(\d+)/edit' => 'admin_entity_form',
        '([a-z]+)/(\d+)/delete' => 'admin_entity_delete',
    ];
    foreach ($routes as $pattern => $handler) {
        if (preg_match('#^' . $pattern . '$#', $sub, $m)) {
            array_shift($m);
            $handler(...$m);
            return;
        }
    }
    abort(404);
}

function admin_render(string $view, array $data = []): void
{
    send_security_headers('admin');
    $data['me'] = current_user();
    $data['unread'] = can('messages') ? (int) db_val('SELECT COUNT(*) FROM messages WHERE is_read = 0') : 0;
    $data += ['title' => 'Administration', 'section' => ''];
    render('admin/' . $view, $data, 'admin/layout');
}

function admin_render_bare(string $view, array $data = []): void
{
    send_security_headers('admin');
    render('admin/' . $view, $data + ['title' => 'Connexion'], 'admin/layout_bare');
}

function require_post(): void
{
    if (!is_post()) {
        abort(405, 'Méthode non autorisée.');
    }
}

/* ---------- Connexion ---------- */

function admin_login(): void
{
    if (current_user()) {
        redirect('admin');
    }
    $error = null;
    $email = '';
    if (is_post()) {
        $email = input('email');
        $result = check_credentials($email, (string) ($_POST['password'] ?? ''));
        if (is_string($result)) {
            $error = $result;
        } elseif ((int) $result['totp_enabled']) {
            session_regenerate_id(true);
            $_SESSION['pending_uid'] = (int) $result['id'];
            $_SESSION['pending_at'] = time();
            redirect('admin/login/2fa');
        } else {
            login_user($result);
            redirect('admin');
        }
    }
    admin_render_bare('login', ['error' => $error, 'email' => $email]);
}

function admin_login_2fa(): void
{
    $uid = (int) ($_SESSION['pending_uid'] ?? 0);
    if ($uid === 0 || time() - (int) ($_SESSION['pending_at'] ?? 0) > 300) {
        unset($_SESSION['pending_uid'], $_SESSION['pending_at']);
        flash('info', 'Merci de vous reconnecter.');
        redirect('admin/login');
    }
    $error = null;
    if (is_post()) {
        $user = db_one('SELECT * FROM users WHERE id = ? AND active = 1', [$uid]);
        $key = 'uid:' . $uid;
        if (!$user) {
            redirect('admin/login');
        }
        if (throttle_count('totp', $key, LOGIN_WINDOW_SECONDS) >= LOGIN_MAX_ATTEMPTS) {
            $error = 'Trop de codes erronés. Réessayez dans 15 minutes.';
        } elseif ($step = totp_verify((string) $user['totp_secret'], input('code'), (int) $user['totp_last_step'])) {
            db_update('users', ['totp_last_step' => $step], $uid);
            throttle_clear('totp', $key);
            login_user($user);
            redirect('admin');
        } else {
            throttle_hit('totp', $key);
            $error = 'Code incorrect ou expiré.';
        }
    }
    admin_render_bare('login_2fa', ['error' => $error]);
}

function admin_logout(): void
{
    require_post();
    if (current_user()) {
        log_activity('déconnexion');
    }
    logout_user();
    flash('success', 'Vous êtes déconnecté.');
    redirect('admin/login');
}

/* ---------- Tableau de bord ---------- */

function admin_dashboard(): void
{
    require_login();
    $counts = [];
    foreach (['projects' => 'Projets', 'articles' => 'Actualités', 'albums' => 'Albums', 'publications' => 'Publications', 'partners' => 'Partenaires'] as $table => $label) {
        $counts[$table] = [
            'label' => $label,
            'total' => (int) db_val("SELECT COUNT(*) FROM $table"),
            'draft' => (int) db_val("SELECT COUNT(*) FROM $table WHERE " . ($table === 'partners' ? 'active' : 'published') . ' = 0'),
        ];
    }
    admin_render('dashboard', [
        'title' => 'Tableau de bord',
        'counts' => $counts,
        'messages' => can('messages') ? db_all('SELECT * FROM messages ORDER BY created_at DESC LIMIT 5') : [],
        'activity' => db_all('SELECT l.*, u.name AS user_name FROM activity_log l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT 10'),
        'downloads' => (int) db_val('SELECT COALESCE(SUM(downloads), 0) FROM publications'),
    ]);
}

/* ---------- Contenus génériques ---------- */

function admin_entity_or_404(string $name): array
{
    return entity_def($name) ?? abort(404);
}

function admin_entity_list(string $name): void
{
    require_perm('content');
    $def = admin_entity_or_404($name);
    $q = query('q');
    $where = '1 = 1';
    $params = [];
    if ($q !== '' && !empty($def['search'])) {
        $where = '(' . implode(' OR ', array_map(fn($c) => "$c LIKE ?", $def['search'])) . ')';
        $params = array_fill(0, count($def['search']), '%' . $q . '%');
    }
    $p = paginate((int) db_val("SELECT COUNT(*) FROM {$def['table']} WHERE $where", $params), 25, (int) query('page', '1'));
    $rows = db_all("SELECT * FROM {$def['table']} WHERE $where ORDER BY {$def['order']} LIMIT {$p['per']} OFFSET {$p['offset']}", $params);

    $relations = [];
    foreach ($def['list'] as $col => $_) {
        $f = $def['fields'][$col] ?? null;
        if (($f['type'] ?? '') === 'relation') {
            $relations[$col] = array_column(db_all("SELECT id, {$f['display']} AS label FROM {$f['table']}"), 'label', 'id');
        }
    }
    admin_render('entity_list', [
        'title' => $def['label'], 'section' => $name, 'name' => $name, 'def' => $def,
        'rows' => $rows, 'pager' => $p, 'q' => $q, 'relations' => $relations,
    ]);
}

function admin_entity_form(string $name, ?string $id = null): void
{
    require_perm('content');
    $def = admin_entity_or_404($name);
    $item = null;
    if ($id !== null) {
        $item = db_one("SELECT * FROM {$def['table']} WHERE id = ?", [(int) $id]) ?? abort(404);
    }
    $publishField = entity_publish_field($def);
    if ($item && $publishField && (int) $item[$publishField] === 1 && !can('publish')) {
        flash('error', 'Ce contenu est en ligne : seuls les éditeurs et administrateurs peuvent le modifier.');
        redirect('admin/' . $name);
    }

    $values = $item ?? entity_defaults($def);
    $errors = [];

    if (is_post()) {
        $cleanup = ['new' => [], 'old' => []];
        $data = entity_collect($def, $item, $errors, $cleanup);
        if (!empty($def['slug'])) {
            $base = slugify(input('slug') ?: (string) ($data[$def['slug']] ?? $item[$def['slug']] ?? ''));
            $data['slug'] = unique_slug($def['table'], $base, $item ? (int) $item['id'] : null);
        }
        if ($errors) {
            foreach ($cleanup['new'] as [$kind, $file]) {
                $kind === 'pdf' ? delete_doc($file) : delete_image($file);
            }
            $values = array_merge($values, array_filter($data, fn($v, $k) => !in_array($def['fields'][$k]['type'] ?? '', ['image', 'pdf'], true), ARRAY_FILTER_USE_BOTH));
            flash('error', 'Le formulaire contient des erreurs. Vérifiez les champs signalés.');
        } else {
            if (!empty($def['timestamps'])) {
                $data['updated_at'] = now();
            }
            if ($item) {
                db_update($def['table'], $data, (int) $item['id']);
                $newId = (int) $item['id'];
                log_activity('modification', $name, $newId);
            } else {
                if (!empty($def['timestamps'])) {
                    $data['created_at'] = now();
                }
                if (!empty($def['author'])) {
                    $data['author_id'] = (int) current_user()['id'];
                }
                $newId = db_insert($def['table'], $data);
                log_activity('création', $name, $newId);
            }
            foreach ($cleanup['old'] as [$kind, $file]) {
                $kind === 'pdf' ? delete_doc($file) : delete_image($file);
            }
            flash('success', 'Enregistrement effectué.' . ($publishField && !can('publish') ? ' Un éditeur doit maintenant le valider pour le mettre en ligne.' : ''));
            redirect('admin/' . $name . '/' . $newId . '/edit');
        }
    }

    $extra = [];
    if ($item && $name === 'albums') {
        $extra['media'] = db_all('SELECT * FROM media WHERE album_id = ? ORDER BY position, id', [$item['id']]);
    }
    admin_render('entity_form', [
        'title' => ($item ? 'Modifier ' : 'Ajouter ') . $def['singular'], 'section' => $name,
        'name' => $name, 'def' => $def, 'item' => $item, 'values' => $values, 'errors' => $errors,
    ] + $extra);
}

function entity_defaults(array $def): array
{
    $values = [];
    foreach ($def['fields'] as $name => $f) {
        $values[$name] = match ($f['default'] ?? null) {
            'now' => now(),
            'year' => date('Y'),
            null => null,
            default => $f['default'],
        };
    }
    $values['slug'] = '';
    return $values;
}

function entity_collect(array $def, ?array $item, array &$errors, array &$cleanup): array
{
    $data = [];
    $canPublish = can('publish');
    foreach ($def['fields'] as $name => $f) {
        $type = $f['type'];
        $required = !empty($f['required']);

        if (!empty($f['publish']) && !$canPublish) {
            if ($item === null) {
                $data[$name] = 0;
            }
            continue;
        }

        if ($type === 'checkbox') {
            $data[$name] = isset($_POST[$name]) ? 1 : 0;
            continue;
        }

        if ($type === 'image' || $type === 'pdf') {
            $file = $_FILES[$name] ?? null;
            $current = $item[$name] ?? null;
            if (upload_present($file)) {
                try {
                    if ($type === 'image') {
                        $data[$name] = process_image_upload($file, (int) ($f['max_width'] ?? 1920));
                    } else {
                        $doc = process_pdf_upload($file);
                        $data[$name] = $doc['file'];
                        $data['file_size'] = $doc['size'];
                    }
                    $cleanup['new'][] = [$type, $data[$name]];
                    if ($current) {
                        $cleanup['old'][] = [$type, $current];
                    }
                } catch (RuntimeException $e) {
                    $errors[$name] = $e->getMessage();
                }
            } elseif (!$required && $current && isset($_POST[$name . '__remove'])) {
                $data[$name] = null;
                $cleanup['old'][] = [$type, $current];
            } elseif ($required && !$current) {
                $errors[$name] = 'Ce fichier est obligatoire.';
            }
            continue;
        }

        if ($type === 'relation') {
            $v = (int) input($name);
            $data[$name] = $v > 0 && db_val("SELECT id FROM {$f['table']} WHERE id = ?", [$v]) ? $v : null;
            continue;
        }

        $v = input($name);
        if ($v === '') {
            if ($required) {
                $errors[$name] = 'Ce champ est obligatoire.';
            }
            $data[$name] = match ($type) {
                'number' => $required ? null : 0,
                'date', 'datetime' => null,
                default => '',
            };
            continue;
        }

        switch ($type) {
            case 'number':
                if (!preg_match('/^-?\d+$/', $v) || (isset($f['min']) && (int) $v < $f['min']) || (isset($f['max']) && (int) $v > $f['max'])) {
                    $errors[$name] = 'Nombre invalide' . (isset($f['min'], $f['max']) ? " (entre {$f['min']} et {$f['max']})" : '') . '.';
                }
                $v = (int) $v;
                break;
            case 'date':
                $d = DateTime::createFromFormat('!Y-m-d', $v);
                if (!$d || $d->format('Y-m-d') !== $v) {
                    $errors[$name] = 'Date invalide.';
                }
                break;
            case 'datetime':
                $d = DateTime::createFromFormat('Y-m-d\TH:i', $v) ?: DateTime::createFromFormat('Y-m-d H:i:s', $v);
                if (!$d) {
                    $errors[$name] = 'Date invalide.';
                } else {
                    $v = $d->format('Y-m-d H:i:s');
                }
                break;
            case 'select':
                if (!array_key_exists($v, $f['options'])) {
                    $errors[$name] = 'Valeur non autorisée.';
                }
                break;
            case 'url':
                if (!filter_var($v, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $v)) {
                    $errors[$name] = 'Adresse web invalide (elle doit commencer par https://).';
                }
                break;
            case 'email':
                if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $errors[$name] = 'Adresse e-mail invalide.';
                }
                break;
        }
        if (is_string($v) && isset($f['max']) && $type !== 'number' && mb_strlen($v) > $f['max']) {
            $errors[$name] = "Texte trop long ({$f['max']} caractères maximum).";
        }
        $data[$name] = $v;
    }
    return $data;
}

function admin_entity_delete(string $name, string $id): void
{
    require_post();
    require_perm('delete');
    $def = admin_entity_or_404($name);
    $item = db_one("SELECT * FROM {$def['table']} WHERE id = ?", [(int) $id]) ?? abort(404);

    foreach ($def['fields'] as $field => $f) {
        if ($f['type'] === 'image') {
            delete_image($item[$field] ?? null);
        } elseif ($f['type'] === 'pdf') {
            delete_doc($item[$field] ?? null);
        }
    }
    if ($name === 'albums') {
        foreach (db_all('SELECT file FROM media WHERE album_id = ?', [$item['id']]) as $m) {
            delete_image($m['file']);
        }
        db_run('DELETE FROM media WHERE album_id = ?', [$item['id']]);
    }
    if ($name === 'projects') {
        db_run('UPDATE albums SET project_id = NULL WHERE project_id = ?', [$item['id']]);
    }
    db_delete($def['table'], (int) $item['id']);
    log_activity('suppression', $name, (int) $item['id']);
    flash('success', 'Élément supprimé.');
    redirect('admin/' . $name);
}

function admin_publication_file(string $id): void
{
    require_perm('content');
    $pub = db_one('SELECT * FROM publications WHERE id = ?', [(int) $id]) ?? abort(404);
    send_pdf(doc_path((string) $pub['file']) ?? abort(404), $pub['title']);
}

/* ---------- Médias des albums ---------- */

function admin_album_or_404(string $id): array
{
    require_perm('content');
    $album = db_one('SELECT * FROM albums WHERE id = ?', [(int) $id]) ?? abort(404);
    if ((int) $album['published'] === 1 && !can('publish')) {
        abort(403, 'Cet album est en ligne : seuls les éditeurs et administrateurs peuvent le modifier.');
    }
    return $album;
}

function admin_media_upload(string $id): void
{
    require_post();
    $album = admin_album_or_404($id);
    $files = $_FILES['photos'] ?? null;
    $caption = mb_substr(input('caption'), 0, 300);
    $position = (int) db_val('SELECT COALESCE(MAX(position), 0) FROM media WHERE album_id = ?', [$album['id']]);
    $ok = 0;
    $failed = [];
    if (is_array($files) && is_array($files['name'])) {
        foreach (array_keys($files['name']) as $i) {
            $file = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
            if (!upload_present($file)) {
                continue;
            }
            try {
                $path = process_image_upload($file);
                db_insert('media', ['album_id' => $album['id'], 'type' => 'photo', 'file' => $path, 'caption' => $caption, 'position' => ++$position, 'created_at' => now()]);
                $ok++;
            } catch (RuntimeException $e) {
                $failed[] = $file['name'] . ' : ' . $e->getMessage();
            }
        }
    }
    if ($ok) {
        log_activity('ajout de ' . $ok . ' photo(s)', 'albums', (int) $album['id']);
        flash('success', $ok . ' photo(s) ajoutée(s).');
    }
    foreach ($failed as $f) {
        flash('error', $f);
    }
    if (!$ok && !$failed) {
        flash('error', 'Aucune photo sélectionnée.');
    }
    redirect('admin/albums/' . $album['id'] . '/edit#medias');
}

function admin_media_video(string $id): void
{
    require_post();
    $album = admin_album_or_404($id);
    $url = input('video_url');
    if (!youtube_id($url)) {
        flash('error', 'Lien YouTube non reconnu. Copiez l\'adresse de la vidéo depuis YouTube (bouton Partager).');
    } else {
        $position = (int) db_val('SELECT COALESCE(MAX(position), 0) FROM media WHERE album_id = ?', [$album['id']]) + 1;
        db_insert('media', ['album_id' => $album['id'], 'type' => 'video', 'video_url' => $url, 'caption' => mb_substr(input('caption'), 0, 300), 'position' => $position, 'created_at' => now()]);
        log_activity('ajout vidéo', 'albums', (int) $album['id']);
        flash('success', 'Vidéo ajoutée.');
    }
    redirect('admin/albums/' . $album['id'] . '/edit#medias');
}

function admin_media_save(string $id): void
{
    require_post();
    $album = admin_album_or_404($id);
    $captions = is_array($_POST['captions'] ?? null) ? $_POST['captions'] : [];
    $positions = is_array($_POST['positions'] ?? null) ? $_POST['positions'] : [];
    foreach (db_all('SELECT id FROM media WHERE album_id = ?', [$album['id']]) as $m) {
        $mid = (int) $m['id'];
        db_update('media', [
            'caption' => mb_substr(trim((string) ($captions[$mid] ?? '')), 0, 300),
            'position' => max(0, min(9999, (int) ($positions[$mid] ?? 0))),
        ], $mid);
    }
    flash('success', 'Légendes et ordre enregistrés.');
    redirect('admin/albums/' . $album['id'] . '/edit#medias');
}

function admin_media_delete(string $id): void
{
    require_post();
    require_perm('delete');
    $media = db_one('SELECT * FROM media WHERE id = ?', [(int) $id]) ?? abort(404);
    delete_image($media['file']);
    db_delete('media', (int) $media['id']);
    log_activity('suppression média', 'albums', (int) $media['album_id']);
    flash('success', 'Élément retiré de l\'album.');
    redirect('admin/albums/' . $media['album_id'] . '/edit#medias');
}

/* ---------- Messages ---------- */

function admin_messages(): void
{
    require_perm('messages');
    $p = paginate((int) db_val('SELECT COUNT(*) FROM messages'), 30, (int) query('page', '1'));
    admin_render('messages', [
        'title' => 'Messages reçus', 'section' => 'messages',
        'rows' => db_all("SELECT * FROM messages ORDER BY created_at DESC LIMIT {$p['per']} OFFSET {$p['offset']}"),
        'pager' => $p,
    ]);
}

function admin_message(string $id): void
{
    require_perm('messages');
    $message = db_one('SELECT * FROM messages WHERE id = ?', [(int) $id]) ?? abort(404);
    if (!(int) $message['is_read']) {
        db_update('messages', ['is_read' => 1], (int) $message['id']);
    }
    admin_render('message', ['title' => 'Message', 'section' => 'messages', 'message' => $message]);
}

function admin_message_delete(string $id): void
{
    require_post();
    require_perm('messages');
    db_delete('messages', (int) $id);
    log_activity('suppression', 'messages', (int) $id);
    flash('success', 'Message supprimé.');
    redirect('admin/messages');
}

/* ---------- Utilisateurs ---------- */

function admin_users(): void
{
    require_perm('users');
    admin_render('users', ['title' => 'Utilisateurs', 'section' => 'users', 'rows' => db_all('SELECT * FROM users ORDER BY name')]);
}

function admin_user_form(?string $id = null): void
{
    require_perm('users');
    $me = current_user();
    $user = $id ? (db_one('SELECT * FROM users WHERE id = ?', [(int) $id]) ?? abort(404)) : null;
    $values = $user ?? ['name' => '', 'email' => '', 'role' => 'contributor', 'active' => 1];
    $errors = [];

    if (is_post()) {
        $values = ['name' => input('name'), 'email' => mb_strtolower(input('email')), 'role' => input('role'), 'active' => isset($_POST['active']) ? 1 : 0];
        $password = (string) ($_POST['password'] ?? '');
        $isSelf = $user && (int) $user['id'] === (int) $me['id'];

        if (mb_strlen($values['name']) < 2 || mb_strlen($values['name']) > 150) {
            $errors['name'] = 'Nom invalide.';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail invalide.';
        } elseif (db_val('SELECT id FROM users WHERE email = ? AND id <> ?', [$values['email'], $user['id'] ?? 0])) {
            $errors['email'] = 'Cet e-mail est déjà utilisé.';
        }
        if (!isset(ROLES[$values['role']])) {
            $errors['role'] = 'Rôle invalide.';
        }
        if ($isSelf && ($values['role'] !== 'admin' || !$values['active'])) {
            $errors['role'] = 'Vous ne pouvez pas retirer vos propres droits d\'administrateur ni désactiver votre compte.';
        }
        if (!$user || $password !== '') {
            if ($err = validate_password($password)) {
                $errors['password'] = $err;
            }
        }
        if (!$errors) {
            $data = $values;
            if ($password !== '') {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            if ($user) {
                db_update('users', $data, (int) $user['id']);
                if ($isSelf && $password !== '') {
                    $_SESSION['pw_fingerprint'] = password_fingerprint(db_one('SELECT * FROM users WHERE id = ?', [$user['id']]));
                }
                log_activity('modification', 'users', (int) $user['id']);
            } else {
                $data['created_at'] = now();
                $newId = db_insert('users', $data);
                log_activity('création', 'users', $newId);
            }
            flash('success', 'Utilisateur enregistré.');
            redirect('admin/users');
        }
    }
    admin_render('user_form', ['title' => $user ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur', 'section' => 'users', 'user' => $user, 'values' => $values, 'errors' => $errors]);
}

function admin_user_delete(string $id): void
{
    require_post();
    require_perm('users');
    if ((int) $id === (int) current_user()['id']) {
        flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        redirect('admin/users');
    }
    db_run('UPDATE articles SET author_id = NULL WHERE author_id = ?', [(int) $id]);
    db_delete('users', (int) $id);
    log_activity('suppression', 'users', (int) $id);
    flash('success', 'Utilisateur supprimé.');
    redirect('admin/users');
}

function admin_user_reset_2fa(string $id): void
{
    require_post();
    require_perm('users');
    db_update('users', ['totp_enabled' => 0, 'totp_secret' => null, 'totp_last_step' => 0], (int) $id);
    log_activity('réinitialisation 2FA', 'users', (int) $id);
    flash('success', 'Double authentification réinitialisée. L\'utilisateur devra la configurer à nouveau.');
    redirect('admin/users');
}

/* ---------- Mon compte : mot de passe et double authentification ---------- */

function admin_account(): void
{
    $me = require_login();
    $errors = [];
    $action = input('action');
    $mandatory = in_array($me['role'], (array) config('require_2fa_roles', []), true);

    if (is_post() && $action === 'password') {
        $new = (string) ($_POST['new_password'] ?? '');
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $me['password_hash'])) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        } elseif ($err = validate_password($new)) {
            $errors['new_password'] = $err;
        } elseif ($new !== (string) ($_POST['confirm_password'] ?? '')) {
            $errors['confirm_password'] = 'Les deux mots de passe ne correspondent pas.';
        } else {
            db_update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], (int) $me['id']);
            $_SESSION['pw_fingerprint'] = password_fingerprint(db_one('SELECT * FROM users WHERE id = ?', [$me['id']]));
            log_activity('changement de mot de passe', 'users', (int) $me['id']);
            flash('success', 'Mot de passe modifié. Vos autres sessions ont été déconnectées.');
            redirect('admin/account');
        }
    }

    if (is_post() && $action === 'enable_2fa' && !(int) $me['totp_enabled']) {
        $secret = (string) ($_SESSION['totp_setup'] ?? '');
        $step = $secret !== '' ? totp_verify($secret, input('code')) : null;
        if ($step) {
            db_update('users', ['totp_secret' => $secret, 'totp_enabled' => 1, 'totp_last_step' => $step], (int) $me['id']);
            unset($_SESSION['totp_setup']);
            log_activity('activation 2FA', 'users', (int) $me['id']);
            flash('success', 'Double authentification activée. Le code vous sera demandé à chaque connexion.');
            redirect('admin');
        }
        $errors['code'] = 'Code incorrect. Vérifiez que l\'heure de votre téléphone est exacte.';
    }

    if (is_post() && $action === 'disable_2fa' && (int) $me['totp_enabled']) {
        if ($mandatory) {
            $errors['disable'] = 'La double authentification est obligatoire pour votre rôle.';
        } elseif (!password_verify((string) ($_POST['password'] ?? ''), $me['password_hash'])) {
            $errors['disable'] = 'Mot de passe incorrect.';
        } else {
            db_update('users', ['totp_secret' => null, 'totp_enabled' => 0, 'totp_last_step' => 0], (int) $me['id']);
            log_activity('désactivation 2FA', 'users', (int) $me['id']);
            flash('success', 'Double authentification désactivée.');
            redirect('admin/account');
        }
    }

    $setupSecret = null;
    if (!(int) $me['totp_enabled']) {
        $_SESSION['totp_setup'] ??= totp_generate_secret();
        $setupSecret = $_SESSION['totp_setup'];
    }
    admin_render('account', [
        'title' => 'Mon compte', 'section' => 'account', 'errors' => $errors, 'mandatory' => $mandatory,
        'setupSecret' => $setupSecret,
        'otpUri' => $setupSecret ? totp_uri($setupSecret, $me['email'], setting('org_name', 'RAPDFEM')) : null,
    ]);
}

/* ---------- Paramètres ---------- */

function admin_settings(): void
{
    require_perm('settings');
    $errors = [];
    if (is_post()) {
        $toSave = [];
        $oldImages = [];
        foreach (settings_fields() as $fields) {
            foreach ($fields as $key => $f) {
                if ($f['type'] === 'image') {
                    $file = $_FILES[$key] ?? null;
                    if (upload_present($file)) {
                        try {
                            $toSave[$key] = process_image_upload($file, (int) ($f['max_width'] ?? 1920));
                            $oldImages[] = setting($key);
                        } catch (RuntimeException $e) {
                            $errors[$key] = $e->getMessage();
                        }
                    } elseif (isset($_POST[$key . '__remove'])) {
                        $oldImages[] = setting($key);
                        $toSave[$key] = '';
                    }
                    continue;
                }
                $v = input($key);
                if ($v !== '' && $f['type'] === 'url' && (!filter_var($v, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $v))) {
                    $errors[$key] = 'Adresse web invalide.';
                } elseif ($v !== '' && $f['type'] === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = 'Adresse e-mail invalide.';
                } elseif (mb_strlen($v) > (int) ($f['max'] ?? 20000)) {
                    $errors[$key] = 'Texte trop long.';
                }
                $toSave[$key] = $v;
            }
        }
        if ($errors) {
            foreach ($toSave as $key => $v) {
                if (($key === 'logo') && $v !== '') {
                    delete_image($v);
                }
            }
            flash('error', 'Certains champs sont invalides.');
        } else {
            foreach ($toSave as $key => $v) {
                if (db_val('SELECT 1 FROM settings WHERE skey = ?', [$key])) {
                    db_run('UPDATE settings SET svalue = ? WHERE skey = ?', [$v, $key]);
                } else {
                    db_insert('settings', ['skey' => $key, 'svalue' => $v]);
                }
            }
            foreach (array_filter($oldImages) as $old) {
                delete_image($old);
            }
            log_activity('modification des paramètres');
            flash('success', 'Paramètres enregistrés.');
            redirect('admin/settings');
        }
    }
    setting('__reset');
    admin_render('settings', ['title' => 'Paramètres du site', 'section' => 'settings', 'errors' => $errors, 'posted' => is_post() ? $_POST : null]);
}

/* ---------- Sauvegardes et journal ---------- */

function admin_backups(): void
{
    require_perm('backups');
    admin_render('backups', ['title' => 'Sauvegardes', 'section' => 'backups', 'backups' => backup_list()]);
}

function admin_backup_create(): void
{
    require_post();
    require_perm('backups');
    try {
        $name = backup_create();
        log_activity('sauvegarde manuelle');
        flash('success', 'Sauvegarde créée : ' . $name);
    } catch (Throwable $e) {
        error_log((string) $e);
        flash('error', 'La sauvegarde a échoué : ' . $e->getMessage());
    }
    redirect('admin/backups');
}

function admin_backup_download(string $name): void
{
    require_perm('backups');
    $path = backup_path($name) ?? abort(404);
    log_activity('téléchargement sauvegarde');
    header('Content-Type: application/zip');
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Cache-Control: no-store');
    readfile($path);
    exit;
}

function admin_activity(): void
{
    require_perm('users');
    admin_render('activity', [
        'title' => 'Journal d\'activité', 'section' => 'activity',
        'rows' => db_all('SELECT l.*, u.name AS user_name FROM activity_log l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT 300'),
    ]);
}

function render_error_page(int $code, string $message): void
{
    $titles = [403 => 'Accès refusé', 404 => 'Page introuvable', 405 => 'Méthode non autorisée', 419 => 'Session expirée'];
    send_security_headers('admin');
    render('admin/error', ['code' => $code, 'title' => $titles[$code] ?? 'Erreur', 'message' => $message], 'admin/layout_bare');
}
