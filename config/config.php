<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Europe/Warsaw');

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

if (!defined('SITE_NAME')) define('SITE_NAME', 'Feltee');
$siteUrl = getenv('SITE_URL');
if (!defined('SITE_URL')) define('SITE_URL', $siteUrl ? $siteUrl : 'https://alis.mygamesonline.org');
$dbHost = getenv('DB_HOST');
if (!defined('DB_HOST')) define('DB_HOST', $dbHost ? $dbHost : 'localhost');
$dbPort = getenv('DB_PORT');
if (!defined('DB_PORT')) define('DB_PORT', $dbPort ? $dbPort : 3306);
$dbName = getenv('DB_NAME');
if (!defined('DB_NAME')) define('DB_NAME', $dbName ? $dbName : '');
$dbUser = getenv('DB_USER');
if (!defined('DB_USER')) define('DB_USER', $dbUser ? $dbUser : '');
$dbPass = getenv('DB_PASS');
if (!defined('DB_PASS')) define('DB_PASS', $dbPass ? $dbPass : '');

if (!defined('LANGUAGES')) define('LANGUAGES', array('pl' => 'Polski', 'ru' => 'Русский', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français'));
if (!defined('DEFAULT_LANG')) define('DEFAULT_LANG', 'pl');

if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
if (!defined('MAX_UPLOAD_SIZE')) define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

if (!defined('CURRENCY')) define('CURRENCY', 'PLN');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', 'zł');
