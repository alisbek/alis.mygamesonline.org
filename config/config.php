<?php
// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

define('SITE_NAME', 'Feltee');
define('SITE_URL', getenv('SITE_URL') ?: 'https://alis.mygamesonline.org');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: '');
define('DB_USER', getenv('DB_USER') ?: '');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('LANGUAGES', ['pl' => 'Polski', 'ru' => 'Русский', 'en' => 'English']);
define('DEFAULT_LANG', 'pl');

define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

define('CURRENCY', 'PLN');
define('CURRENCY_SYMBOL', 'zł');

session_start();
date_default_timezone_set('Asia/Bishkek');