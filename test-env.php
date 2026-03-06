<?php
// Standalone debug - does NOT include any other files
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "=== .env check ===\n";
$envFile = __DIR__ . '/.env';
echo "Path: $envFile\n";
echo "Exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n";

if (file_exists($envFile)) {
    echo "Size: " . filesize($envFile) . " bytes\n";
    echo "\n--- Contents ---\n";
    $raw = file_get_contents($envFile);
    // Mask password
    echo preg_replace('/DB_PASS=(.{3})(.*)/', 'DB_PASS=$1***', $raw);
} else {
    echo "\n.env NOT FOUND!\n";
    echo "\n--- Files in root ---\n";
    $files = scandir(__DIR__);
    foreach ($files as $f) {
        echo $f . "\n";
    }
}
