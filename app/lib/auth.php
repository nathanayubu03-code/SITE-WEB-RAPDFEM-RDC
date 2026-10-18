<?php
declare(strict_types=1);

const ROLES = [
    'admin' => 'Administrateur',
    'editor' => 'Éditeur',
    'contributor' => 'Contributeur',
];

/*
 * content  : créer et modifier les contenus
 * publish  : publier / dépublier
 * delete   : supprimer des contenus
 * messages : lire les messages du formulaire de contact
 * users, settings, backups : administration
 */
const PERMISSIONS = [
    'admin' => ['content', 'publish', 'delete', 'messages', 'users', 'settings', 'backups'],
    'editor' => ['content', 'publish', 'delete', 'messages'],
    'contributor' => ['content'],
];

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_WINDOW_SECONDS = 900;

function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    session_boot();
    $user = null;
    $id = (int) ($_SESSION['uid'] ?? 0);
    if ($id === 0) {
        return null;
    }
    $idle = (int) config('session_idle_minutes', 30) * 60;
    if (time() - (int) ($_SESSION['last_seen'] ?? 0) > $idle) {
        logout_user();
        flash('info', 'Votre session a expiré après une période d\'inactivité.');
        return null;
    }
    $row = db_one('SELECT * FROM users WHERE id = ? AND active = 1', [$id]);
    if (!$row || !hash_equals((string) ($_SESSION['pw_fingerprint'] ?? ''), password_fingerprint($row))) {
        // Compte désactivé ou mot de passe changé ailleurs : on coupe la session.
        logout_user();
        return null;
    }
    $_SESSION['last_seen'] = time();
    return $user = $row;
}

function password_fingerprint(array $user): string
{
    return hash('sha256', $user['password_hash'] . '|' . $user['id']);
}

function can(string $permission): bool
{
    $user = current_user();
    return $user !== null && in_array($permission, PERMISSIONS[$user['role']] ?? [], true);
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('admin/login');
    }
    $needs2fa = in_array($user['role'], (array) config('require_2fa_roles', []), true);
    $path = current_path();
    if ($needs2fa && !(int) $user['totp_enabled'] && !in_array($path, ['admin/account', 'admin/logout'], true)) {
        flash('warning', 'La double authentification est obligatoire pour votre rôle. Activez-la pour continuer.');
        redirect('admin/account');
    }
    return $user;
}

function require_perm(string $permission): void
{
    require_login();
    if (!can($permission)) {
        abort(403, 'Vous n\'avez pas les droits nécessaires pour cette action.');
    }
}

/* ---------- Limitation de débit (connexion, formulaire de contact) ---------- */

function throttle_key(string $value): string
{
    return hash('sha256', $value);
}

function throttle_count(string $action, string $key, int $windowSeconds): int
{
    $since = date('Y-m-d H:i:s', time() - $windowSeconds);
    return (int) db_val('SELECT COUNT(*) FROM throttle WHERE action = ? AND key_hash = ? AND created_at > ?', [$action, throttle_key($key), $since]);
}

function throttle_hit(string $action, string $key): void
{
    db_insert('throttle', ['action' => $action, 'key_hash' => throttle_key($key), 'created_at' => now()]);
    if (random_int(1, 50) === 1) {
        db_run('DELETE FROM throttle WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);
    }
}

function throttle_clear(string $action, string $key): void
{
    db_run('DELETE FROM throttle WHERE action = ? AND key_hash = ?', [$action, throttle_key($key)]);
}

/* ---------- Connexion ---------- */

/** @return array|string L'utilisateur en cas de succès, sinon un message d'erreur. */
function check_credentials(string $email, string $password): array|string
{
    $email = mb_strtolower(trim($email));
    $ipKey = client_ip();
    $userKey = $email;
    if (throttle_count('login_ip', $ipKey, LOGIN_WINDOW_SECONDS) >= LOGIN_MAX_ATTEMPTS * 4
        || throttle_count('login_user', $userKey, LOGIN_WINDOW_SECONDS) >= LOGIN_MAX_ATTEMPTS) {
        return 'Trop de tentatives. Réessayez dans 15 minutes.';
    }

    $user = db_one('SELECT * FROM users WHERE email = ?', [$email]);
    // Vérification factice pour ne pas révéler l'existence d'un compte par le temps de réponse.
    $hash = $user['password_hash'] ?? '$2y$12$7rQpm1D8Y1kW1Z6m3J9f9e2wqvC3iN0Pp7v0bq6hQnS1uY2xKpGq6';
    $valid = password_verify($password, $hash);

    if (!$user || !$valid || !(int) $user['active']) {
        throttle_hit('login_ip', $ipKey);
        throttle_hit('login_user', $userKey);
        return 'Identifiants incorrects.';
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        db_update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], (int) $user['id']);
        $user = db_one('SELECT * FROM users WHERE id = ?', [$user['id']]);
    }
    return $user;
}

function login_user(array $user): void
{
    session_boot();
    session_regenerate_id(true);
    unset($_SESSION['pending_uid'], $_SESSION['pending_at']);
    $_SESSION['uid'] = (int) $user['id'];
    $_SESSION['pw_fingerprint'] = password_fingerprint($user);
    $_SESSION['last_seen'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    throttle_clear('login_user', mb_strtolower($user['email']));
    db_update('users', ['last_login_at' => now()], (int) $user['id']);
    log_activity('connexion', 'users', (int) $user['id'], (int) $user['id']);
}

function logout_user(): void
{
    session_boot();
    $_SESSION = [];
    session_regenerate_id(true);
}

function validate_password(string $password): ?string
{
    if (mb_strlen($password) < 12) {
        return 'Le mot de passe doit contenir au moins 12 caractères.';
    }
    if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Le mot de passe doit contenir des lettres et des chiffres.';
    }
    return null;
}

function log_activity(string $action, ?string $entity = null, ?int $entityId = null, ?int $userId = null): void
{
    $userId ??= isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
    db_insert('activity_log', [
        'user_id' => $userId,
        'action' => $action,
        'entity' => $entity,
        'entity_id' => $entityId,
        'ip' => client_ip(),
        'created_at' => now(),
    ]);
}
