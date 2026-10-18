<?php
declare(strict_types=1);

function backup_dir(): string
{
    $dir = STORAGE . '/backups';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    return $dir;
}

/**
 * Crée une archive ZIP contenant la base de données et les fichiers téléversés
 * (images et PDF). Retourne le nom du fichier créé.
 */
function backup_create(): string
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('L\'extension PHP zip est requise pour les sauvegardes.');
    }
    $name = 'sauvegarde-' . date('Y-m-d-His') . '.zip';
    $path = backup_dir() . '/' . $name;
    $tmpSql = null;

    $zip = new ZipArchive();
    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
        throw new RuntimeException('Impossible de créer l\'archive.');
    }

    if (db_driver() === 'sqlite') {
        $tmpSql = backup_dir() . '/tmp-' . bin2hex(random_bytes(6)) . '.sqlite';
        db()->exec('VACUUM INTO ' . db()->quote($tmpSql));
        $zip->addFile($tmpSql, 'database/rapdfem.sqlite');
    } else {
        $zip->addFromString('database/rapdfem.sql', backup_mysql_dump());
    }

    foreach ([UPLOADS => 'uploads', STORAGE . '/docs' => 'docs'] as $dir => $prefix) {
        if (!is_dir($dir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->isFile() && !str_starts_with($file->getFilename(), '.')) {
                $zip->addFile($file->getPathname(), $prefix . '/' . substr($file->getPathname(), strlen($dir) + 1));
            }
        }
    }
    $zip->close();
    if ($tmpSql) {
        @unlink($tmpSql);
    }
    backup_rotate();
    return $name;
}

function backup_mysql_dump(): string
{
    $pdo = db();
    $out = "-- Sauvegarde RAPDFEM du " . now() . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $out .= "DROP TABLE IF EXISTS `$table`;\n$create;\n";
        $rows = $pdo->query("SELECT * FROM `$table`");
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row);
            $out .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
        }
        $out .= "\n";
    }
    return $out . "SET FOREIGN_KEY_CHECKS=1;\n";
}

function backup_list(): array
{
    $files = glob(backup_dir() . '/sauvegarde-*.zip') ?: [];
    rsort($files);
    return array_map(fn($f) => ['name' => basename($f), 'size' => filesize($f), 'date' => date('Y-m-d H:i:s', filemtime($f))], $files);
}

function backup_rotate(): void
{
    $keep = max(1, (int) config('backups_keep', 14));
    foreach (array_slice(backup_list(), $keep) as $old) {
        @unlink(backup_dir() . '/' . $old['name']);
    }
}

function backup_path(string $name): ?string
{
    if (!preg_match('/^sauvegarde-\d{4}-\d{2}-\d{2}-\d{6}\.zip$/', $name)) {
        return null;
    }
    $path = backup_dir() . '/' . $name;
    return is_file($path) ? $path : null;
}
