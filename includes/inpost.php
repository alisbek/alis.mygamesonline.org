<?php
/**
 * InPost ShipX API Helper Functions
 * 
 * Handles shipment creation, label generation, dispatch orders,
 * and parcel size calculation for InPost Paczkomat delivery.
 * 
 * API docs: https://docs.inpost24.com/display/PL/ShipX+API
 */

/**
 * Make a request to the InPost ShipX API.
 *
 * @param string $method  HTTP method (GET, POST, PUT, DELETE)
 * @param string $endpoint API endpoint (e.g., '/v1/organizations/6151/shipments')
 * @param array|null $data  POST/PUT data (will be JSON-encoded)
 * @return array|false Decoded JSON response or false on failure
 */
function inpostRequest($method, $endpoint, $data = null) {
    $url = INPOST_BASE_URL . $endpoint;
    
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . INPOST_API_TOKEN,
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $method = strtoupper($method);
    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            }
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'GET':
        default:
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        error_log("InPost: cURL error for $method $endpoint: $curlError");
        return false;
    }
    
    if ($httpCode >= 400) {
        error_log("InPost: HTTP $httpCode for $method $endpoint. Response: $response");
        return false;
    }
    
    if (empty($response)) {
        // Some endpoints return empty body with 2xx (e.g., 204)
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['http_code' => $httpCode];
        }
        return false;
    }
    
    $decoded = json_decode($response, true);
    if ($decoded === null) {
        error_log("InPost: invalid JSON for $method $endpoint. Response: $response");
        return false;
    }
    
    return $decoded;
}

/**
 * Download raw content from InPost API (e.g., PDF labels).
 *
 * @param string $endpoint API endpoint
 * @return string|false Raw response body or false on failure
 */
function inpostRequestRaw($endpoint) {
    $url = INPOST_BASE_URL . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . INPOST_API_TOKEN,
            'Accept: application/pdf',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError || $httpCode >= 400 || empty($response)) {
        error_log("InPost: raw request failed for $endpoint. HTTP $httpCode, error: $curlError");
        return false;
    }
    
    return $response;
}

/**
 * Calculate the appropriate InPost parcel size template based on cart items.
 *
 * InPost locker sizes:
 *   small  (A): max  80 x 380 x 640 mm, max 25 kg
 *   medium (B): max 190 x 380 x 640 mm, max 25 kg
 *   large  (C): max 410 x 380 x 640 mm, max 25 kg
 *
 * Strategy: sum up heights (stacking items on top of each other),
 * take max width and max length, sum weights.
 * Then pick the smallest template that fits.
 *
 * @param array $items Order items with product data (weight_grams, length_mm, width_mm, height_mm, quantity)
 * @return string|false 'small', 'medium', 'large', or false if doesn't fit
 */
function inpostCalculateParcelSize($items) {
    $totalWeight = 0; // grams
    $totalHeight = 0; // mm (stacking)
    $maxLength = 0;   // mm
    $maxWidth = 0;    // mm
    
    foreach ($items as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $w = (int)($item['weight_grams'] ?? 300);
        $l = (int)($item['length_mm'] ?? 200);
        $wd = (int)($item['width_mm'] ?? 150);
        $h = (int)($item['height_mm'] ?? 80);
        
        $totalWeight += $w * $qty;
        $totalHeight += $h * $qty;
        $maxLength = max($maxLength, $l);
        $maxWidth = max($maxWidth, $wd);
    }
    
    // Check weight limit (25 kg = 25000 g)
    if ($totalWeight > 25000) {
        error_log("InPost: parcel too heavy: {$totalWeight}g");
        return false;
    }
    
    // Normalize dimensions: ensure length >= width >= height for optimal fitting
    $dims = [$totalHeight, $maxWidth, $maxLength];
    sort($dims);
    $smallest = $dims[0]; // height (smallest dimension)
    $middle = $dims[1];   // width
    $largest = $dims[2];  // length
    
    // Templates (height x width x length in mm)
    $templates = [
        'small'  => ['height' => 80,  'width' => 380, 'length' => 640],
        'medium' => ['height' => 190, 'width' => 380, 'length' => 640],
        'large'  => ['height' => 410, 'width' => 380, 'length' => 640],
    ];
    
    foreach ($templates as $name => $tpl) {
        if ($smallest <= $tpl['height'] && $middle <= $tpl['width'] && $largest <= $tpl['length']) {
            return $name;
        }
    }
    
    error_log("InPost: parcel too large: {$smallest}x{$middle}x{$largest}mm");
    return false;
}

/**
 * Create an InPost shipment for an order.
 *
 * @param array $order  Order data from DB (must include customer_name, email, phone, inpost_point_id)
 * @param array $items  Order items with product dimensions
 * @return array|false  Shipment data or false on failure
 */
function inpostCreateShipment($order, $items) {
    $parcelSize = inpostCalculateParcelSize($items);
    if (!$parcelSize) {
        error_log("InPost: cannot create shipment for order #{$order['id']} — parcel size calculation failed");
        return false;
    }
    
    // Parse name into first/last
    $nameParts = explode(' ', $order['customer_name'], 2);
    $firstName = $nameParts[0];
    $lastName = $nameParts[1] ?? '';
    
    $payload = [
        'receiver' => [
            'name' => $order['customer_name'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $order['email'] ?: 'customer@feltee.com',
            'phone' => preg_replace('/[^0-9+]/', '', $order['phone']),
        ],
        'parcels' => [
            [
                'template' => $parcelSize,
            ],
        ],
        'service' => 'inpost_locker_standard',
        'reference' => 'Feltee Order #' . $order['id'],
        'custom_attributes' => [
            'target_point' => $order['inpost_point_id'],
            'sending_method' => 'dispatch_order',
        ],
    ];
    
    $endpoint = '/v1/organizations/' . INPOST_ORG_ID . '/shipments';
    $result = inpostRequest('POST', $endpoint, $payload);
    
    if (!$result || !isset($result['id'])) {
        error_log("InPost: createShipment failed for order #{$order['id']}. Response: " . json_encode($result));
        return false;
    }
    
    error_log("InPost: shipment created #{$result['id']} for order #{$order['id']}, size=$parcelSize, target={$order['inpost_point_id']}");
    return $result;
}

/**
 * Get shipment details from InPost.
 *
 * @param string $shipmentId InPost shipment ID
 * @return array|false Shipment data or false
 */
function inpostGetShipment($shipmentId) {
    return inpostRequest('GET', '/v1/shipments/' . $shipmentId);
}

/**
 * Get PDF shipping label for a shipment.
 * Label is only available after shipment status is 'confirmed' or later.
 *
 * @param string $shipmentId InPost shipment ID
 * @param string $format     Label format: 'pdf' (default)
 * @param string $type       Label size: 'A6' (default) or 'A4'
 * @return string|false PDF binary data or false
 */
function inpostGetLabel($shipmentId, $format = 'pdf', $type = 'A6') {
    $endpoint = '/v1/shipments/' . $shipmentId . '/label?format=' . $format . '&type=' . $type;
    return inpostRequestRaw($endpoint);
}

/**
 * Create a dispatch order (schedule courier pickup).
 * Shipments must be in 'confirmed' status.
 *
 * @param array $shipmentIds Array of InPost shipment IDs
 * @return array|false Dispatch order data or false
 */
function inpostCreateDispatch($shipmentIds) {
    $payload = [
        'shipments' => array_map(function($id) { return (int)$id; }, $shipmentIds),
    ];
    
    $endpoint = '/v1/organizations/' . INPOST_ORG_ID . '/dispatch_orders';
    $result = inpostRequest('POST', $endpoint, $payload);
    
    if (!$result || !isset($result['id'])) {
        error_log("InPost: createDispatch failed. Response: " . json_encode($result));
        return false;
    }
    
    error_log("InPost: dispatch order created #{$result['id']} for " . count($shipmentIds) . " shipments");
    return $result;
}

/**
 * Map InPost shipment status to a human-readable label.
 *
 * @param string $status InPost status string
 * @return string Human-readable status
 */
function inpostStatusLabel($status) {
    $labels = [
        'created' => 'Created',
        'offers_prepared' => 'Processing',
        'offer_selected' => 'Processing',
        'confirmed' => 'Confirmed',
        'dispatched' => 'Dispatched',
        'collected_from_sender' => 'Collected',
        'taken_by_courier' => 'In Transit',
        'adopted_at_source_branch' => 'In Transit',
        'sent_from_source_branch' => 'In Transit',
        'adopted_at_sorting_center' => 'Sorting',
        'sent_from_sorting_center' => 'Sorting',
        'adopted_at_target_branch' => 'Near Destination',
        'sent_from_target_branch' => 'Out for Delivery',
        'ready_to_pickup' => 'Ready for Pickup',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'pickup_reminder_sent' => 'Pickup Reminder',
        'returned_to_sender' => 'Returned',
        'avizo' => 'Awaiting Pickup',
        'claimed' => 'Claimed',
        'cancelled' => 'Cancelled',
    ];
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}
