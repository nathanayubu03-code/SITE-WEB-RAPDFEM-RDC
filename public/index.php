<?php
declare(strict_types=1);

// Serveur de développement PHP : laisser passer les fichiers statiques.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !str_ends_with($file, '.php')) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

if (config('force_https') && !is_https()) {
    header('Location: https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

$path = current_path();

try {
    if ($path === 'admin' || str_starts_with($path, 'admin/')) {
        require APP . '/controllers/admin.php';
        admin_dispatch($path);
    } else {
        require APP . '/controllers/site.php';
        site_dispatch($path);
    }
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    if (config('debug')) {
        echo '<pre>' . e((string) $e) . '</pre>';
    } else {
        echo '<!doctype html><meta charset="utf-8"><title>Erreur</title><p>Une erreur est survenue. Merci de réessayer plus tard.</p>';
    }
}
