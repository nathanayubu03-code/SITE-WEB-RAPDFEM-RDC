<?php
/*
 * Configuration RAPDFEM.
 * Copier ce fichier en app/config.php (fait automatiquement par `php bin/admin.php install`)
 * puis adapter les valeurs. Ne jamais versionner app/config.php.
 */
return [
    // URL publique sans slash final. Vide = détection automatique.
    'site_url' => '',

    // Sous-dossier d'installation (ex. '/site'). Vide = détection automatique.
    'base_path' => null,

    'debug' => false,
    'timezone' => 'Africa/Kinshasa',

    // Redirige automatiquement HTTP vers HTTPS (laisser true en production).
    'force_https' => false,

    'db' => [
        // 'sqlite' (aucune configuration serveur) ou 'mysql'
        'driver' => 'sqlite',
        'path' => __DIR__ . '/../storage/database/rapdfem.sqlite',
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'rapdfem',
        'user' => 'rapdfem',
        'pass' => '',
    ],

    // reCAPTCHA v2 (case à cocher). Laisser vide pour n'utiliser que
    // le pot de miel et la limitation de débit.
    'recaptcha' => [
        'site_key' => '',
        'secret_key' => '',
    ],

    // Notification e-mail des messages du formulaire de contact (fonction mail() de PHP).
    'mail' => [
        'to' => '',
        'from' => '',
    ],

    // Rôles pour lesquels la double authentification est obligatoire.
    'require_2fa_roles' => ['admin'],

    // Déconnexion automatique après inactivité (minutes).
    'session_idle_minutes' => 30,

    // Nombre de sauvegardes conservées.
    'backups_keep' => 14,
];
