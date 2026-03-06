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

if (!defined('FELTEE_CURRENCY')) define('FELTEE_CURRENCY', 'PLN');
if (!defined('FELTEE_CURRENCY_SYMBOL')) define('FELTEE_CURRENCY_SYMBOL', 'zł');

// PayU Payment Gateway
$payuPosId = getenv('PAYU_POS_ID');
if (!defined('PAYU_POS_ID')) define('PAYU_POS_ID', $payuPosId ? $payuPosId : '');
$payuMd5Key = getenv('PAYU_MD5_KEY');
if (!defined('PAYU_MD5_KEY')) define('PAYU_MD5_KEY', $payuMd5Key ? $payuMd5Key : '');
$payuClientId = getenv('PAYU_CLIENT_ID');
if (!defined('PAYU_CLIENT_ID')) define('PAYU_CLIENT_ID', $payuClientId ? $payuClientId : '');
$payuClientSecret = getenv('PAYU_CLIENT_SECRET');
if (!defined('PAYU_CLIENT_SECRET')) define('PAYU_CLIENT_SECRET', $payuClientSecret ? $payuClientSecret : '');
$payuBaseUrl = getenv('PAYU_BASE_URL');
if (!defined('PAYU_BASE_URL')) define('PAYU_BASE_URL', $payuBaseUrl ? $payuBaseUrl : 'https://secure.snd.payu.com');
