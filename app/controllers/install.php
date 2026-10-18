<?php
declare(strict_types=1);

/*
 * Assistant d'installation dans le navigateur, pour les hébergements sans Terminal.
 * Il ne fonctionne qu'une fois : dès que le premier administrateur existe,
 * le fichier storage/installed.lock est créé et cette page renvoie une erreur 404.
 */

require_once APP . '/schema.php';
require_once APP . '/seed.php';

function install_page(string $path): void
{
    if (is_installed()) {
        abort(404);
    }
    if ($path !== 'install') {
        redirect('install');
    }
    session_boot();

    // Base déjà installée en ligne de commande, sans verrou : on pose le verrou.
    if (install_admin_exists()) {
        mark_installed();
        redirect('admin/login');
    }

    $checks = install_checks();
    $ready = !in_array(false, array_column($checks, 'ok'), true);
    $errors = [];
    $values = ['name' => '', 'email' => ''];

    if (is_post()) {
        csrf_check();
        $values = ['name' => input('name'), 'email' => mb_strtolower(input('email'))];
        $password = (string) ($_POST['password'] ?? '');
        if (mb_strlen($values['name']) < 2 || mb_strlen($values['name']) > 150) {
            $errors['name'] = 'Indiquez votre nom.';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse e-mail invalide.';
        }
        if ($err = validate_password($password)) {
            $errors['password'] = $err;
        } elseif ($password !== (string) ($_POST['confirm'] ?? '')) {
            $errors['confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }
        if (!$ready) {
            $errors['form'] = 'Corrigez d\'abord les points en rouge ci-dessous.';
        }

        if (!$errors) {
            try {
                foreach (schema_statements(db_driver()) as $sql) {
                    db()->exec($sql);
                }
                seed_database();
                if (install_admin_exists()) {
                    // Quelqu'un a terminé l'installation entre-temps.
                    abort(403, 'L\'installation a déjà été faite.');
                }
                db_insert('users', [
                    'name' => $values['name'], 'email' => $values['email'],
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'admin', 'active' => 1, 'created_at' => now(),
                ]);
                if (!is_file(APP . '/config.php') && is_writable(APP)) {
                    @copy(APP . '/config.sample.php', APP . '/config.php');
                }
                mark_installed();
                flash('success', 'Installation terminée. Connectez-vous, puis activez la double authentification.');
                redirect('admin/login');
            } catch (PDOException $e) {
                error_log((string) $e);
                $errors['form'] = 'Connexion à la base de données impossible : ' . $e->getMessage();
            }
        }
    }

    send_security_headers('admin');
    render('admin/install', ['title' => 'Installation', 'checks' => $checks, 'ready' => $ready, 'errors' => $errors, 'values' => $values], 'admin/layout_bare');
}

function install_admin_exists(): bool
{
    try {
        return (int) db_val('SELECT COUNT(*) FROM users') > 0;
    } catch (Throwable) {
        return false;
    }
}

function install_checks(): array
{
    $driver = db_driver();
    $checks = [
        ['label' => 'PHP 8.1 ou plus (version actuelle : ' . PHP_VERSION . ')', 'ok' => version_compare(PHP_VERSION, '8.1.0', '>=')],
        ['label' => 'Extension ' . ($driver === 'mysql' ? 'pdo_mysql' : 'pdo_sqlite') . ' (base de données)', 'ok' => extension_loaded($driver === 'mysql' ? 'pdo_mysql' : 'pdo_sqlite')],
        ['label' => 'Extension gd (traitement des photos)', 'ok' => extension_loaded('gd')],
        ['label' => 'Extension fileinfo (contrôle des PDF)', 'ok' => extension_loaded('fileinfo')],
        ['label' => 'Extension mbstring (textes accentués)', 'ok' => extension_loaded('mbstring')],
        ['label' => 'Dossier storage/ accessible en écriture', 'ok' => is_writable(STORAGE)],
        ['label' => 'Dossier public/uploads/ accessible en écriture', 'ok' => is_writable(UPLOADS)],
    ];
    if (!extension_loaded('zip')) {
        $checks[] = ['label' => 'Extension zip absente : le site fonctionne, mais les sauvegardes seront indisponibles', 'ok' => null];
    }
    return $checks;
}
