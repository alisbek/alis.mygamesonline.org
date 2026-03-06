<?php
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set('Asia/Bishkek');

    // Load .env file
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (substr($line, 0, 1) === '#') continue;
            if (strpos($line, '=') === false) continue;
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    define('SITE_NAME', 'Feltee');
    $siteUrl = getenv('SITE_URL');
    define('SITE_URL', $siteUrl ? $siteUrl : 'https://alis.mygamesonline.org');
    $dbHost = getenv('DB_HOST');
    define('DB_HOST', $dbHost ? $dbHost : 'localhost');
    $dbPort = getenv('DB_PORT');
    define('DB_PORT', $dbPort ? $dbPort : 3306);
    $dbName = getenv('DB_NAME');
    define('DB_NAME', $dbName ? $dbName : '');
    $dbUser = getenv('DB_USER');
    define('DB_USER', $dbUser ? $dbUser : '');
    $dbPass = getenv('DB_PASS');
    define('DB_PASS', $dbPass ? $dbPass : '');

    define('LANGUAGES', array('pl' => 'Polski', 'ru' => 'Русский', 'en' => 'English'));
    define('DEFAULT_LANG', 'pl');

    define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
    define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

    define('CURRENCY', 'PLN');
    define('CURRENCY_SYMBOL', 'zł');
}
