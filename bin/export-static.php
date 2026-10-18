<?php
declare(strict_types=1);

/*
 * Génère la version de démonstration en pages fixes, publiée sur GitHub Pages.
 *
 *   php bin/export-static.php DOSSIER_SORTIE BASE_PATH URL_PUBLIQUE
 *   php bin/export-static.php _site /SITE-WEB-RAPDFEM-RDC https://nathanayubu03-code.github.io/SITE-WEB-RAPDFEM-RDC
 *
 * Le site réel est lancé avec une base temporaire remplie du contenu de démarrage,
 * puis chaque page publique est enregistrée en HTML. Rien n'est écrit dans storage/.
 */

if (PHP_SAPI !== 'cli') {
    exit("Ce script s'exécute uniquement en ligne de commande.\n");
}

$root = dirname(__DIR__);
$out = rtrim($argv[1] ?? '_site', '/');
$base = rtrim($argv[2] ?? '', '/');
$siteUrl = rtrim($argv[3] ?? '', '/');

$tmp = sys_get_temp_dir() . '/rapdfem-export-' . bin2hex(random_bytes(4));
mkdir($tmp, 0700, true);
$config = require $root . '/app/config.sample.php';
$config['base_path'] = $base;
$config['site_url'] = $siteUrl;
$config['static_demo'] = true;
$config['debug'] = false;
$config['db'] = ['driver' => 'sqlite', 'path' => $tmp . '/demo.sqlite'];
$config['lock_file'] = $tmp . '/installed.lock';
$configFile = $tmp . '/config.php';
file_put_contents($configFile, '<?php return ' . var_export($config, true) . ';');
putenv('RAPDFEM_CONFIG=' . $configFile);

require $root . '/app/bootstrap.php';
require APP . '/schema.php';
require APP . '/seed.php';

foreach (schema_statements('sqlite') as $sql) {
    db()->exec($sql);
}
seed_database();
mark_installed();

$routes = ['', 'qui-sommes-nous', 'projets', 'actualites', 'galerie', 'publications', 'partenaires', 'contact'];
foreach (db_all('SELECT slug FROM projects WHERE published = 1') as $r) {
    $routes[] = 'projets/' . $r['slug'];
}
foreach (db_all('SELECT slug FROM articles WHERE published = 1 AND published_at <= ?', [now()]) as $r) {
    $routes[] = 'actualites/' . $r['slug'];
}
foreach (db_all('SELECT slug FROM albums WHERE published = 1') as $r) {
    $routes[] = 'galerie/' . $r['slug'];
}

// Serveur PHP local qui sert le vrai site avec la configuration de démonstration.
$port = random_int(20000, 40000);
$server = proc_open(
    [PHP_BINARY, '-S', "127.0.0.1:$port", '-t', PUBLIC_DIR, PUBLIC_DIR . '/index.php'],
    [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
    $pipes,
    null,
    ['RAPDFEM_CONFIG' => $configFile] + getenv()
);
for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', $port); $i++) {
    usleep(100000);
}

function fetch_page(int $port, string $base, string $route): array
{
    $body = @file_get_contents("http://127.0.0.1:$port$base/$route", false, stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 20]]));
    preg_match('#HTTP/\S+ (\d{3})#', $http_response_header[0] ?? '', $m);
    return [(int) ($m[1] ?? 0), (string) $body];
}

function write_file(string $path, string $content): void
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $content);
}

$failed = 0;
foreach ($routes as $route) {
    [$status, $html] = fetch_page($port, $base, $route);
    if ($status !== 200) {
        fwrite(STDERR, "Échec ($status) : /$route\n");
        $failed++;
        continue;
    }
    write_file($out . '/' . ($route === '' ? '' : $route . '/') . 'index.html', $html);
    echo "/$route\n";
}
[$status, $html] = fetch_page($port, $base, 'page-introuvable-demo');
write_file($out . '/404.html', $html);
[$status, $xml] = fetch_page($port, $base, 'sitemap.xml');
write_file($out . '/sitemap.xml', $xml);

proc_terminate($server);
proc_close($server);

// Fichiers statiques : CSS, JS, images, photos éventuelles.
$copy = function (string $from, string $to) use (&$copy): void {
    foreach (scandir($from) ?: [] as $name) {
        if ($name === '.' || $name === '..' || str_starts_with($name, '.')) {
            continue;
        }
        is_dir("$from/$name") ? $copy("$from/$name", "$to/$name") : write_file("$to/$name", (string) file_get_contents("$from/$name"));
    }
};
$copy(PUBLIC_DIR . '/assets', $out . '/assets');
$copy(PUBLIC_DIR . '/uploads', $out . '/uploads');
write_file($out . '/.nojekyll', '');

array_map('unlink', glob($tmp . '/*') ?: []);
@rmdir($tmp);

if ($failed) {
    exit(1);
}
echo count($routes) . " pages générées dans $out/\n";
