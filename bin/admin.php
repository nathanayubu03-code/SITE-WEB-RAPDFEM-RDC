<?php
declare(strict_types=1);

/*
 * Outil en ligne de commande RAPDFEM.
 *
 *   php bin/admin.php install                 Crée la configuration, la base et le premier administrateur
 *   php bin/admin.php create-user             Ajoute un utilisateur
 *   php bin/admin.php reset-password EMAIL    Définit un nouveau mot de passe
 *   php bin/admin.php reset-2fa EMAIL         Désactive la double authentification (téléphone perdu)
 *   php bin/admin.php backup                  Crée une sauvegarde (à planifier en tâche cron)
 */

if (PHP_SAPI !== 'cli') {
    exit("Ce script s'exécute uniquement en ligne de commande.\n");
}

$root = dirname(__DIR__);
$command = $argv[1] ?? 'help';

if ($command === 'install' && !is_file($root . '/app/config.php')) {
    copy($root . '/app/config.sample.php', $root . '/app/config.php');
    echo "Fichier app/config.php créé. Modifiez-le pour utiliser MySQL si besoin, puis relancez l'installation.\n";
}

require $root . '/app/bootstrap.php';
require APP . '/schema.php';
require APP . '/seed.php';
require APP . '/lib/backup.php';

function ask(string $question, bool $hidden = false): string
{
    echo $question;
    if ($hidden && DIRECTORY_SEPARATOR === '/' && stream_isatty(STDIN)) {
        shell_exec('stty -echo');
        $value = trim((string) fgets(STDIN));
        shell_exec('stty echo');
        echo "\n";
        return $value;
    }
    return trim((string) fgets(STDIN));
}

function ask_password(): string
{
    while (true) {
        $password = ask('Mot de passe (12 caractères minimum, lettres et chiffres) : ', true);
        if ($error = validate_password($password)) {
            echo $error . "\n";
            continue;
        }
        if (ask('Confirmez le mot de passe : ', true) !== $password) {
            echo "Les mots de passe ne correspondent pas.\n";
            continue;
        }
        return $password;
    }
}

function create_user_interactive(string $defaultRole = 'admin'): void
{
    $name = ask('Nom complet : ');
    do {
        $email = mb_strtolower(ask('E-mail : '));
    } while (!filter_var($email, FILTER_VALIDATE_EMAIL) && print("E-mail invalide.\n"));
    if (db_val('SELECT id FROM users WHERE email = ?', [$email])) {
        exit("Un utilisateur avec cet e-mail existe déjà.\n");
    }
    $role = ask('Rôle [admin/editor/contributor] (' . $defaultRole . ') : ') ?: $defaultRole;
    if (!isset(ROLES[$role])) {
        exit("Rôle inconnu.\n");
    }
    $password = ask_password();
    db_insert('users', [
        'name' => $name ?: $email, 'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role, 'active' => 1, 'created_at' => now(),
    ]);
    echo "Utilisateur créé : $email (" . ROLES[$role] . ").\n";
}

function find_user_or_exit(?string $email): array
{
    $user = $email ? db_one('SELECT * FROM users WHERE email = ?', [mb_strtolower($email)]) : null;
    if (!$user) {
        exit("Utilisateur introuvable.\n");
    }
    return $user;
}

switch ($command) {
    case 'install':
        foreach (schema_statements(db_driver()) as $sql) {
            db()->exec($sql);
        }
        seed_database();
        echo "Base de données prête (" . db_driver() . ").\n";
        if ((int) db_val('SELECT COUNT(*) FROM users') === 0) {
            echo "\nCréation du premier compte administrateur.\n";
            create_user_interactive('admin');
        }
        echo "\nInstallation terminée. Connectez-vous sur /admin.\n";
        break;

    case 'create-user':
        create_user_interactive('editor');
        break;

    case 'reset-password':
        $user = find_user_or_exit($argv[2] ?? null);
        db_update('users', ['password_hash' => password_hash(ask_password(), PASSWORD_DEFAULT)], (int) $user['id']);
        echo "Mot de passe modifié.\n";
        break;

    case 'reset-2fa':
        $user = find_user_or_exit($argv[2] ?? null);
        db_update('users', ['totp_enabled' => 0, 'totp_secret' => null, 'totp_last_step' => 0], (int) $user['id']);
        echo "Double authentification désactivée pour {$user['email']}. Elle devra être réactivée à la prochaine connexion.\n";
        break;

    case 'backup':
        echo 'Sauvegarde créée : storage/backups/' . backup_create() . "\n";
        break;

    default:
        echo "Commandes : install, create-user, reset-password EMAIL, reset-2fa EMAIL, backup\n";
}
