<?php
declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    // Derrière Cloudflare ou un proxy inverse.
    return ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function base_path(): string
{
    $configured = config('base_path');
    if (is_string($configured)) {
        return rtrim($configured, '/');
    }
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    if (!str_ends_with($script, '/index.php')) {
        return '';
    }
    $dir = str_replace('\\', '/', dirname($script));
    $dir = preg_replace('#/public$#', '', $dir);
    return rtrim((string) $dir, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function abs_url(string $path = ''): string
{
    $site = rtrim((string) config('site_url', ''), '/');
    if ($site === '') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $site = (is_https() ? 'https' : 'http') . '://' . $host;
        return $site . url($path);
    }
    return $site . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = PUBLIC_DIR . '/assets/' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('assets/' . $path) . '?v=' . $version;
}

function upload_url(?string $path, bool $thumb = false): string
{
    if (!$path) {
        return '';
    }
    return url('uploads/' . ($thumb ? thumb_path($path) : $path));
}

function current_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    return trim(rawurldecode($uri), '/');
}

function redirect(string $to, int $code = 302): never
{
    if (!preg_match('#^https?://#', $to)) {
        $to = url($to);
    }
    header('Location: ' . $to, true, $code);
    exit;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    if (function_exists('render_error_page')) {
        render_error_page($code, $message);
    } else {
        echo e($message ?: 'Erreur ' . $code);
    }
    exit;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function query(string $key, string $default = ''): string
{
    $value = $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function client_ip(): string
{
    // Cloudflare transmet l'IP réelle du visiteur dans cet en-tête.
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/* ---------- Session, CSRF, messages flash ---------- */

function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('rapdfem_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_path() . '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    session_boot();
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        abort(419, 'Session expirée. Rechargez la page et réessayez.');
    }
}

function flash(string $type, string $message): void
{
    session_boot();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE && !isset($_COOKIE['rapdfem_sid'])) {
        return [];
    }
    session_boot();
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------- Rendu ---------- */

function render_partial(string $__view, array $__data = []): string
{
    extract($__data, EXTR_SKIP);
    ob_start();
    require APP . '/views/' . $__view . '.php';
    return (string) ob_get_clean();
}

function render(string $view, array $data = [], ?string $layout = null): void
{
    $content = render_partial($view, $data);
    if ($layout === null) {
        echo $content;
        return;
    }
    $data['content'] = $content;
    echo render_partial($layout, $data);
}

/* ---------- Texte et dates ---------- */

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = strtr($text, [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'ç' => 'c',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'î' => 'i', 'ï' => 'i', 'í' => 'i',
        'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
        'ÿ' => 'y', 'ñ' => 'n', 'œ' => 'oe', 'æ' => 'ae',
    ]);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim(substr(trim($text, '-'), 0, 120), '-');
    return $text !== '' ? $text : 'element';
}

function excerpt(?string $text, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_markup((string) $text)) ?? '');
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $space = mb_strrpos($cut, ' ');
    return rtrim($space ? mb_substr($cut, 0, $space) : $cut, ' ,;:.') . '…';
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date || !($ts = strtotime($date))) {
        return '';
    }
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
        'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $out = date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    return $withTime ? $out . ' à ' . date('H:i', $ts) : $out;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function human_size(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
    }
    return max(1, (int) round($bytes / 1024)) . ' Ko';
}

function paginate(int $total, int $perPage, int $page): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $pages);
    return ['page' => $page, 'pages' => $pages, 'per' => $perPage, 'offset' => ($page - 1) * $perPage, 'total' => $total];
}

function page_url(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;
    if ($page <= 1) {
        unset($params['page']);
    }
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return $path . ($params ? '?' . http_build_query($params) : '');
}

function youtube_id(?string $url): ?string
{
    if (!$url) {
        return null;
    }
    $pattern = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~';
    return preg_match($pattern, $url, $m) ? $m[1] : null;
}

function safe_link(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (str_starts_with($url, '/')) {
        return url($url);
    }
    return preg_match('#^(https?://|mailto:|tel:)#i', $url) ? $url : '';
}

/* ---------- Paramètres du site ---------- */

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($key === '__reset') {
        $cache = null;
        return '';
    }
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db_all('SELECT skey, svalue FROM settings') as $row) {
                $cache[$row['skey']] = (string) $row['svalue'];
            }
        } catch (Throwable) {
            $cache = [];
        }
    }
    $value = $cache[$key] ?? '';
    return $value !== '' ? $value : $default;
}

function setting_lines(string $key): array
{
    $lines = [];
    foreach (preg_split('/\R/', setting($key)) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = preg_split('/\s*[|:]\s*/', $line, 2);
        $lines[] = ['title' => $parts[0], 'text' => $parts[1] ?? ''];
    }
    return $lines;
}

/* ---------- En-têtes de sécurité ---------- */

function send_security_headers(string $context = 'site', bool $recaptcha = false): void
{
    $script = ["'self'"];
    $style = ["'self'", 'https://fonts.googleapis.com'];
    $frame = ['https://www.youtube-nocookie.com'];
    $img = ["'self'", 'data:', 'https://img.youtube.com'];
    $connect = ["'self'"];

    if ($recaptcha) {
        $script[] = 'https://www.google.com/recaptcha/';
        $script[] = 'https://www.gstatic.com/recaptcha/';
        $frame[] = 'https://www.google.com/recaptcha/';
        $frame[] = 'https://recaptcha.google.com/recaptcha/';
        $style[] = "'unsafe-inline'";
        $connect[] = 'https://www.google.com/recaptcha/';
    }

    $csp = implode('; ', [
        "default-src 'self'",
        'script-src ' . implode(' ', $script),
        'style-src ' . implode(' ', $style),
        'font-src https://fonts.gstatic.com',
        'img-src ' . implode(' ', $img),
        'frame-src ' . implode(' ', $frame),
        'connect-src ' . implode(' ', $connect),
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ]);

    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    if ($context === 'admin') {
        header('Cache-Control: no-store');
        header('X-Robots-Tag: noindex, nofollow');
    }
}

/* ---------- Installation ---------- */

function install_lock_path(): string
{
    return (string) config('lock_file', STORAGE . '/installed.lock');
}

function is_installed(): bool
{
    return is_file(install_lock_path());
}

function mark_installed(): void
{
    file_put_contents(install_lock_path(), now() . "\n");
}

/** Version de démonstration en pages fixes (GitHub Pages) : formulaires et filtres masqués. */
function is_static_demo(): bool
{
    return (bool) config('static_demo', false);
}
