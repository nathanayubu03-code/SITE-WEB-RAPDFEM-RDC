<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = config('db');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if (($c['driver'] ?? 'sqlite') === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'] ?? 3306, $c['name']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $options);
    } else {
        $dir = dirname($c['path']);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $pdo = new PDO('sqlite:' . $c['path'], null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    return $pdo;
}

function db_driver(): string
{
    return config('db.driver', 'sqlite') === 'mysql' ? 'mysql' : 'sqlite';
}

function db_run(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_all(string $sql, array $params = []): array
{
    return db_run($sql, $params)->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_run($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_val(string $sql, array $params = []): mixed
{
    $value = db_run($sql, $params)->fetchColumn();
    return $value === false ? null : $value;
}

/** Les noms de table et de colonne viennent toujours des définitions internes, jamais de l'utilisateur. */
function db_insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(', ', $cols), implode(', ', array_fill(0, count($cols), '?')));
    db_run($sql, array_values($data));
    return (int) db()->lastInsertId();
}

function db_update(string $table, array $data, int $id): void
{
    $sets = implode(', ', array_map(fn($c) => "$c = ?", array_keys($data)));
    db_run("UPDATE $table SET $sets WHERE id = ?", [...array_values($data), $id]);
}

function db_delete(string $table, int $id): void
{
    db_run("DELETE FROM $table WHERE id = ?", [$id]);
}

function unique_slug(string $table, string $slug, ?int $exceptId = null): string
{
    $candidate = $slug;
    $i = 2;
    while (db_val("SELECT id FROM $table WHERE slug = ?" . ($exceptId ? ' AND id <> ?' : ''), $exceptId ? [$candidate, $exceptId] : [$candidate])) {
        $candidate = $slug . '-' . $i++;
    }
    return $candidate;
}
