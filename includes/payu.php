<?php
/**
 * PayU Payment Gateway Helper Functions
 * 
 * Handles OAuth authentication, order creation, and notification verification
 * for PayU.pl payment integration.
 * 
 * API docs: https://developers.payu.com/en/restapi.html
 */

/**
 * Get OAuth access token from PayU.
 * Caches token in a temp file to avoid requesting a new one on every call.
 * Token expires in ~43199 seconds (~12 hours).
 *
 * @return string|false Access token or false on failure
 */
function payuGetAccessToken() {
    // Check cached token
    $cacheFile = sys_get_temp_dir() . '/payu_token_' . md5(PAYU_CLIENT_ID) . '.json';
    
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached && isset($cached['access_token'], $cached['expires_at'])) {
            // Use cached token if it has at least 60 seconds left
            if ($cached['expires_at'] > time() + 60) {
                return $cached['access_token'];
            }
        }
    }
    
    // Request new token
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
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        error_log("PayU OAuth failed: HTTP $httpCode, cURL error: $curlError, response: $response");
        return false;
    }
    
    $data = json_decode($response, true);
    if (!isset($data['access_token'])) {
        error_log("PayU OAuth: no access_token in response: $response");
        return false;
    }
    
    // Cache token
    $cacheData = [
        'access_token' => $data['access_token'],
        'expires_at' => time() + (int)($data['expires_in'] ?? 3600),
    ];
    file_put_contents($cacheFile, json_encode($cacheData));
    
    return $data['access_token'];
}

/**
 * Create a PayU order and get the redirect URI.
 *
 * @param array $orderData Expected keys:
 *   - orderId      (int)    Our database order ID
 *   - description  (string) Order description
 *   - totalAmount  (int)    Total in grosze (e.g., 36000 for 360 PLN)
 *   - customerIp   (string) Buyer's IP address
 *   - buyer        (array)  [email, phone, firstName, lastName, language]
 *   - products     (array)  [{name, unitPrice, quantity}, ...]
 *   - notifyUrl    (string) Webhook URL for payment notifications
 *   - continueUrl  (string) URL to redirect customer after payment
 *
 * @return array|false ['redirectUri' => '...', 'orderId' => '...'] or false
 */
function payuCreateOrder($orderData) {
    $token = payuGetAccessToken();
    if (!$token) {
        error_log("PayU createOrder: failed to get access token");
        return false;
    }
    
    $payload = [
        'notifyUrl' => $orderData['notifyUrl'],
        'continueUrl' => $orderData['continueUrl'],
        'customerIp' => $orderData['customerIp'],
        'merchantPosId' => PAYU_POS_ID,
        'description' => $orderData['description'],
        'currencyCode' => FELTEE_CURRENCY,
        'totalAmount' => (string)$orderData['totalAmount'],
        'extOrderId' => (string)$orderData['orderId'],
        'buyer' => $orderData['buyer'],
        'products' => $orderData['products'],
    ];
    
    $url = PAYU_BASE_URL . '/api/v2_1/orders';
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        // CRITICAL: Do NOT follow redirects — the 302 response contains our data
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // PayU returns HTTP 302 with JSON body containing redirectUri and orderId
    // It can also return 200 for transparent integration, or 4xx/5xx for errors
    if (!$response) {
        error_log("PayU createOrder: empty response, HTTP $httpCode, cURL error: $curlError");
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (!$data) {
        error_log("PayU createOrder: invalid JSON response: $response");
        return false;
    }
    
    // Check for successful order creation (302 redirect or 200 with COMPLETED)
    if (isset($data['redirectUri']) && isset($data['orderId'])) {
        return [
            'redirectUri' => $data['redirectUri'],
            'orderId' => $data['orderId'],
        ];
    }
    
    // Check for status field indicating success without redirect
    if (isset($data['status']['statusCode']) && $data['status']['statusCode'] === 'SUCCESS' && isset($data['orderId'])) {
        return [
            'redirectUri' => $data['redirectUri'] ?? '',
            'orderId' => $data['orderId'],
        ];
    }
    
    // Error
    $statusCode = $data['status']['statusCode'] ?? 'UNKNOWN';
    $statusDesc = $data['status']['statusDesc'] ?? ($data['error'] ?? 'Unknown error');
    error_log("PayU createOrder failed: $statusCode - $statusDesc. Full response: $response");
    return false;
}

/**
 * Verify PayU notification signature.
 *
 * The OpenPayu-Signature header contains key-value pairs separated by ';':
 *   signature=abc123;algorithm=MD5;sender=checkout
 *
 * Verification: md5(jsonBody + secondKey) must equal the signature value.
 *
 * @param string $jsonBody     Raw POST body (JSON)
 * @param string $signatureHeader  Value of OpenPayu-Signature header
 *
 * @return bool True if signature is valid
 */
function payuVerifySignature($jsonBody, $signatureHeader) {
    if (empty($signatureHeader) || empty($jsonBody)) {
        return false;
    }
    
    // Parse signature header: "signature=...;algorithm=MD5;sender=checkout"
    $parts = explode(';', $signatureHeader);
    $sigData = [];
    foreach ($parts as $part) {
        $kv = explode('=', trim($part), 2);
        if (count($kv) === 2) {
            $sigData[$kv[0]] = $kv[1];
        }
    }
    
    if (!isset($sigData['signature'])) {
        error_log("PayU signature verification: no 'signature' field in header");
        return false;
    }
    
    $expectedSignature = $sigData['signature'];
    $algorithm = strtoupper($sigData['algorithm'] ?? 'MD5');
    
    // Compute our signature
    $concatenated = $jsonBody . PAYU_MD5_KEY;
    
    switch ($algorithm) {
        case 'MD5':
            $computed = md5($concatenated);
            break;
        case 'SHA256':
        case 'SHA-256':
            $computed = hash('sha256', $concatenated);
            break;
        default:
            error_log("PayU signature verification: unsupported algorithm '$algorithm'");
            return false;
    }
    
    return hash_equals($expectedSignature, $computed);
}

/**
 * Map PayU order status to our internal payment_status.
 *
 * PayU statuses: NEW, PENDING, WAITING_FOR_CONFIRMATION, COMPLETED, CANCELED
 * Our statuses:  pending, paid, failed, refunded
 *
 * @param string $payuStatus PayU order status
 * @return string Our internal payment status
 */
function payuMapStatus($payuStatus) {
    switch (strtoupper($payuStatus)) {
        case 'COMPLETED':
            return 'paid';
        case 'CANCELED':
            return 'failed';
        case 'PENDING':
        case 'WAITING_FOR_CONFIRMATION':
        case 'NEW':
        default:
            return 'pending';
    }
}

/**
 * Get the customer's real IP address.
 *
 * @return string IP address
 */
function payuGetCustomerIp() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // May contain multiple IPs — take the first (client IP)
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
