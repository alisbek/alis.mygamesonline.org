<?php
/**
 * PayU connection diagnostic (temporary - delete after testing)
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain');

echo "=== PayU Diagnostic ===\n\n";

// Check constants
echo "PAYU_POS_ID: " . (defined('PAYU_POS_ID') ? PAYU_POS_ID : 'NOT DEFINED') . "\n";
echo "PAYU_CLIENT_ID: " . (defined('PAYU_CLIENT_ID') ? PAYU_CLIENT_ID : 'NOT DEFINED') . "\n";
echo "PAYU_BASE_URL: " . (defined('PAYU_BASE_URL') ? PAYU_BASE_URL : 'NOT DEFINED') . "\n";
echo "PAYU_MD5_KEY: " . (defined('PAYU_MD5_KEY') ? substr(PAYU_MD5_KEY, 0, 8) . '...' : 'NOT DEFINED') . "\n";
echo "PAYU_CLIENT_SECRET: " . (defined('PAYU_CLIENT_SECRET') ? substr(PAYU_CLIENT_SECRET, 0, 8) . '...' : 'NOT DEFINED') . "\n";
echo "FELTEE_CURRENCY: " . (defined('FELTEE_CURRENCY') ? FELTEE_CURRENCY : 'NOT DEFINED') . "\n";
echo "\n";

// Check cURL
echo "cURL available: " . (function_exists('curl_init') ? 'YES' : 'NO') . "\n";
echo "cURL version: " . (function_exists('curl_version') ? curl_version()['version'] : 'N/A') . "\n";
echo "SSL version: " . (function_exists('curl_version') ? curl_version()['ssl_version'] : 'N/A') . "\n";
echo "\n";

// Test DNS resolution
$host = parse_url(PAYU_BASE_URL, PHP_URL_HOST);
echo "PayU host: $host\n";
$ip = gethostbyname($host);
echo "Resolved IP: $ip\n";
echo "\n";

// Test OAuth
echo "=== Testing OAuth ===\n";
$url = PAYU_BASE_URL . '/pl/standard/user/oauth/authorize';
$postData = http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => PAYU_CLIENT_ID,
    'client_secret' => PAYU_CLIENT_SECRET,
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_VERBOSE => true,
]);

// Capture verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
curl_close($ch);

// Get verbose log
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

echo "HTTP Code: $httpCode\n";
echo "cURL errno: $curlErrno\n";
echo "cURL error: $curlError\n";
echo "Response: " . substr($response, 0, 500) . "\n";
echo "\nVerbose log:\n$verboseLog\n";

// If OAuth failed with SSL, retry without verification
if ($curlErrno > 0) {
    echo "\n=== Retrying without SSL verification ===\n";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    
    $response2 = curl_exec($ch);
    $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError2 = curl_error($ch);
    $curlErrno2 = curl_errno($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode2\n";
    echo "cURL errno: $curlErrno2\n";
    echo "cURL error: $curlError2\n";
    echo "Response: " . substr($response2, 0, 500) . "\n";
}
