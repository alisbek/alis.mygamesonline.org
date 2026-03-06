<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<pre>";

// Load .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '=') === false) continue;
        $parts = explode('=', $line, 2);
        putenv(trim($parts[0]) . '=' . trim($parts[1]));
    }
}

$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');

echo "DB_NAME: $dbName\n";
echo "DB_USER: $dbUser\n";
echo "DB_PASS length: " . strlen($dbPass) . "\n\n";

// Try multiple possible hosts
$hosts = array(
    'fdb1031.runhosting.com:3306',
    'localhost:3306',
    '127.0.0.1:3306',
    'fdb1031.runhosting.com:3307',
    'mysql.runhosting.com:3306',
    'sql1031.runhosting.com:3306',
    'fdb1031:3306'
);

foreach ($hosts as $hostPort) {
    $parts = explode(':', $hostPort);
    $host = $parts[0];
    $port = $parts[1];
    echo "Trying $host:$port ... ";
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, array(
            PDO::ATTR_TIMEOUT => 5
        ));
        echo "SUCCESS!\n";
        $pdo = null;
    } catch (PDOException $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\n--- DNS resolve ---\n";
echo "fdb1031.runhosting.com => " . gethostbyname('fdb1031.runhosting.com') . "\n";
echo "\n--- Server info ---\n";
echo "SERVER_ADDR: " . (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A') . "\n";
echo "SERVER_NAME: " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'N/A') . "\n";
echo "</pre>";
