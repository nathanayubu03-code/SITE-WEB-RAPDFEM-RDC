<?php
declare(strict_types=1);

/** Schéma compatible SQLite et MySQL/MariaDB. */
function schema_statements(string $driver): array
{
    $pk = $driver === 'mysql' ? 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $int = $driver === 'mysql' ? 'INT UNSIGNED' : 'INTEGER';
    $text = $driver === 'mysql' ? 'MEDIUMTEXT' : 'TEXT';
    $opts = $driver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $dt = 'VARCHAR(19)';

    $tables = [
        'users' => "id $pk, name VARCHAR(150) NOT NULL, email VARCHAR(191) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'contributor', active SMALLINT NOT NULL DEFAULT 1,
            totp_secret VARCHAR(64) NULL, totp_enabled SMALLINT NOT NULL DEFAULT 0, totp_last_step $int NOT NULL DEFAULT 0,
            last_login_at $dt NULL, created_at $dt NOT NULL",
        'throttle' => "id $pk, action VARCHAR(30) NOT NULL, key_hash CHAR(64) NOT NULL, created_at $dt NOT NULL",
        'settings' => "skey VARCHAR(100) NOT NULL PRIMARY KEY, svalue $text NULL",
        'slides' => "id $pk, title VARCHAR(200) NOT NULL, subtitle TEXT NULL, image VARCHAR(255) NULL, cta_label VARCHAR(100) NULL,
            cta_url VARCHAR(300) NULL, position $int NOT NULL DEFAULT 0, active SMALLINT NOT NULL DEFAULT 1",
        'stats' => "id $pk, value VARCHAR(50) NOT NULL, label VARCHAR(150) NOT NULL, position $int NOT NULL DEFAULT 0, active SMALLINT NOT NULL DEFAULT 1",
        'domains' => "id $pk, title VARCHAR(150) NOT NULL, description TEXT NULL, position $int NOT NULL DEFAULT 0",
        'projects' => "id $pk, title VARCHAR(200) NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, summary TEXT NULL, body $text NULL,
            results $text NULL, zone VARCHAR(80) NULL, status VARCHAR(20) NOT NULL DEFAULT 'en_cours', domain VARCHAR(150) NULL,
            start_date VARCHAR(10) NULL, end_date VARCHAR(10) NULL, donors VARCHAR(300) NULL, beneficiaries VARCHAR(200) NULL,
            image VARCHAR(255) NULL, published SMALLINT NOT NULL DEFAULT 0, created_at $dt NOT NULL, updated_at $dt NOT NULL",
        'articles' => "id $pk, title VARCHAR(200) NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, type VARCHAR(20) NOT NULL DEFAULT 'actualite',
            excerpt TEXT NULL, body $text NULL, image VARCHAR(255) NULL, published_at $dt NOT NULL, published SMALLINT NOT NULL DEFAULT 0,
            author_id $int NULL, created_at $dt NOT NULL, updated_at $dt NOT NULL",
        'albums' => "id $pk, title VARCHAR(200) NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, description TEXT NULL, project_id $int NULL,
            cover VARCHAR(255) NULL, published SMALLINT NOT NULL DEFAULT 0, created_at $dt NOT NULL, updated_at $dt NOT NULL",
        'media' => "id $pk, album_id $int NOT NULL, type VARCHAR(10) NOT NULL DEFAULT 'photo', file VARCHAR(255) NULL,
            video_url VARCHAR(300) NULL, caption VARCHAR(300) NULL, position $int NOT NULL DEFAULT 0, created_at $dt NOT NULL",
        'publications' => "id $pk, title VARCHAR(250) NOT NULL, category VARCHAR(30) NOT NULL, year $int NOT NULL, description TEXT NULL,
            file VARCHAR(255) NULL, file_size $int NOT NULL DEFAULT 0, cover VARCHAR(255) NULL, downloads $int NOT NULL DEFAULT 0,
            published SMALLINT NOT NULL DEFAULT 0, created_at $dt NOT NULL, updated_at $dt NOT NULL",
        'partners' => "id $pk, name VARCHAR(200) NOT NULL, type VARCHAR(20) NOT NULL DEFAULT 'financier', logo VARCHAR(255) NULL,
            url VARCHAR(300) NULL, description TEXT NULL, position $int NOT NULL DEFAULT 0, active SMALLINT NOT NULL DEFAULT 1",
        'messages' => "id $pk, name VARCHAR(150) NOT NULL, email VARCHAR(191) NOT NULL, phone VARCHAR(50) NULL, organization VARCHAR(200) NULL,
            subject VARCHAR(200) NOT NULL, message TEXT NOT NULL, ip_hash CHAR(64) NULL, is_read SMALLINT NOT NULL DEFAULT 0, created_at $dt NOT NULL",
        'activity_log' => "id $pk, user_id $int NULL, action VARCHAR(60) NOT NULL, entity VARCHAR(40) NULL, entity_id $int NULL,
            ip VARCHAR(45) NULL, created_at $dt NOT NULL",
    ];

    $sql = [];
    foreach ($tables as $name => $cols) {
        $sql[] = "CREATE TABLE IF NOT EXISTS $name ($cols)$opts";
    }
    return $sql;
}
