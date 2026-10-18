<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);
define('STORAGE', ROOT . '/storage');
define('PUBLIC_DIR', ROOT . '/public');
define('UPLOADS', PUBLIC_DIR . '/uploads');

$configFile = is_file(APP . '/config.php') ? APP . '/config.php' : APP . '/config.sample.php';
$GLOBALS['config'] = require $configFile;

require APP . '/lib/helpers.php';
require APP . '/lib/db.php';
require APP . '/lib/auth.php';
require APP . '/lib/totp.php';
require APP . '/lib/markup.php';
require APP . '/lib/uploads.php';
require APP . '/lib/entities.php';

error_reporting(E_ALL);
ini_set('display_errors', config('debug') ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set((string) config('timezone', 'Africa/Kinshasa'));
mb_internal_encoding('UTF-8');
