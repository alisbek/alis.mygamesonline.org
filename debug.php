<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<pre>";

$envFile = __DIR__ . '/.env';
echo "Path: $envFile\n";
echo "Exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";

if (file_exists($envFile)) {
    echo "Size: " . filesize($envFile) . " bytes\n";
    echo "Readable: " . (is_readable($envFile) ? 'YES' : 'NO') . "\n";
    echo "\n--- Raw contents ---\n";
    echo file_get_contents($envFile);
    echo "\n--- Hex dump first 200 bytes ---\n";
    echo bin2hex(substr(file_get_contents($envFile), 0, 200));
}

echo "\n\n--- getenv() results ---\n";
echo "DB_HOST: [" . getenv('DB_HOST') . "]\n";
echo "DB_PORT: [" . getenv('DB_PORT') . "]\n";
echo "DB_NAME: [" . getenv('DB_NAME') . "]\n";
echo "DB_USER: [" . getenv('DB_USER') . "]\n";
echo "DB_PASS: [" . (getenv('DB_PASS') ? 'SET (len=' . strlen(getenv('DB_PASS')) . ')' : 'EMPTY') . "]\n";
echo "</pre>";
