<?php
// Temporary debug file - DELETE AFTER USE
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== ENV DEBUG ===\n";
$envFile = __DIR__ . '/.env';
echo "Checking .env at: " . $envFile . "\n";
echo ".env exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";

if (file_exists($envFile)) {
    echo "\n=== .env contents (masked) ===\n";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '=') === false) continue;
        $parts = explode('=', $line, 2);
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if (stripos($key, 'PASS') !== false) {
            echo $key . '=' . substr($value, 0, 3) . '***' . ' (length: ' . strlen($value) . ")\n";
        } else {
            echo $key . '=' . $value . "\n";
        }
        putenv($key . '=' . $value);
    }
}

echo "\n=== DB Connection Test ===\n";
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$name = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

echo "Host: " . $host . "\n";
echo "Port: " . $port . "\n";
echo "DB: " . $name . "\n";
echo "User: " . $user . "\n";
echo "Pass length: " . strlen($pass) . "\n";

try {
    $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $name . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "\nCONNECTION SUCCESS!\n";
} catch (PDOException $e) {
    echo "\nCONNECTION FAILED: " . $e->getMessage() . "\n";
}
