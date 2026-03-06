<?php
/**
 * Network connectivity diagnostic (temporary)
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain');

echo "=== Network Connectivity Test ===\n\n";

// Test various outbound connections
$tests = [
    ['https://secure.snd.payu.com/pl/standard/user/oauth/authorize', 'PayU Sandbox (HTTPS 443)'],
    ['http://secure.snd.payu.com/', 'PayU Sandbox (HTTP 80)'],
    ['https://www.google.com/', 'Google (HTTPS 443)'],
    ['http://www.google.com/', 'Google (HTTP 80)'],
    ['https://api.github.com/', 'GitHub API (HTTPS 443)'],
    ['https://secure.payu.com/', 'PayU Production (HTTPS 443)'],
];

foreach ($tests as [$url, $label]) {
    echo "Testing: $label\n";
    echo "  URL: $url\n";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_NOBODY => true,
    ]);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    echo "  HTTP: $httpCode, errno: $curlErrno, error: $curlError\n\n";
}

// Test file_get_contents with allow_url_fopen
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF') . "\n\n";

// Test fsockopen
echo "=== Testing fsockopen ===\n";
$targets = [
    ['secure.snd.payu.com', 443],
    ['secure.snd.payu.com', 80],
    ['www.google.com', 443],
    ['www.google.com', 80],
];

foreach ($targets as [$host, $port]) {
    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($fp) {
        echo "$host:$port - CONNECTED\n";
        fclose($fp);
    } else {
        echo "$host:$port - FAILED ($errno: $errstr)\n";
    }
}
