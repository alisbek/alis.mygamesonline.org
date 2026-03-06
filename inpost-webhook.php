<?php
/**
 * InPost Webhook Endpoint
 * 
 * InPost sends POST requests to this URL when shipment status changes.
 * This is a machine-to-machine endpoint — no session, no language, no HTML.
 * 
 * Must respond with HTTP 200 to GET requests (health check).
 * Must respond with HTTP 200 to POST requests to acknowledge receipt.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

// Health check — InPost sends GET to verify endpoint is alive
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);
    echo 'OK';
    exit;
}

// Only accept POST requests beyond this point
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Read raw POST body
$rawBody = file_get_contents('php://input');

if (empty($rawBody)) {
    error_log("InPost webhook: empty body");
    http_response_code(400);
    exit('Empty body');
}

// Parse JSON
$data = json_decode($rawBody, true);

if (!$data) {
    error_log("InPost webhook: invalid JSON. Body: $rawBody");
    http_response_code(400);
    exit('Invalid JSON');
}

// Log the full notification for debugging
error_log("InPost webhook: received: " . substr($rawBody, 0, 500));

// InPost sends different payload structures depending on the event
// Common structure: { "id": ..., "status": "...", "tracking_number": "...", ... }
$shipmentId = $data['id'] ?? null;
$status = $data['status'] ?? null;
$trackingNumber = $data['tracking_number'] ?? null;

if (empty($shipmentId) || empty($status)) {
    error_log("InPost webhook: missing id or status in notification");
    // Return 200 anyway to prevent retries for malformed data
    http_response_code(200);
    exit('OK');
}

error_log("InPost webhook: shipmentId=$shipmentId, status=$status, tracking=$trackingNumber");

try {
    // Find order by InPost shipment ID
    $stmt = $pdo->prepare("SELECT id, inpost_status FROM orders WHERE inpost_shipment_id = ?");
    $stmt->execute([(string)$shipmentId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        error_log("InPost webhook: no order found for shipmentId=$shipmentId");
        http_response_code(200);
        exit('OK');
    }
    
    // Update order with new status and tracking number
    $updateFields = ['inpost_status = ?'];
    $updateValues = [$status];
    
    if (!empty($trackingNumber)) {
        $updateFields[] = 'inpost_tracking_number = ?';
        $updateValues[] = $trackingNumber;
    }
    
    // Auto-update order status based on InPost status
    if (in_array($status, ['dispatched', 'collected_from_sender', 'taken_by_courier', 'adopted_at_source_branch', 'sent_from_source_branch'])) {
        $updateFields[] = 'status = ?';
        $updateValues[] = 'shipped';
    } elseif (in_array($status, ['delivered', 'ready_to_pickup'])) {
        $updateFields[] = 'status = ?';
        $updateValues[] = 'completed';
    }
    
    $updateValues[] = $order['id'];
    $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($updateValues);
    
    error_log("InPost webhook: updated order #{$order['id']} inpost_status to $status");
    
} catch (PDOException $e) {
    error_log("InPost webhook DB error: " . $e->getMessage());
    http_response_code(500);
    exit('Database error');
}

// Return 200 OK to acknowledge receipt
http_response_code(200);
echo 'OK';
