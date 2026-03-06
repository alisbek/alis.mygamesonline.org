<?php
/**
 * PayU Notification Webhook Endpoint
 * 
 * PayU POSTs JSON to this URL when payment status changes.
 * This is a machine-to-machine endpoint — no session, no language, no HTML.
 * 
 * Must return HTTP 200 to acknowledge receipt, otherwise PayU retries
 * up to 20 times over 72 hours.
 */

// Load config and database (no session needed)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/payu.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Read raw POST body
$rawBody = file_get_contents('php://input');

if (empty($rawBody)) {
    error_log("PayU notify: empty body");
    http_response_code(400);
    exit('Empty body');
}

// Get signature header
$signatureHeader = $_SERVER['HTTP_OPENPAYU_SIGNATURE'] ?? '';

// Verify signature
if (!payuVerifySignature($rawBody, $signatureHeader)) {
    error_log("PayU notify: invalid signature. Header: $signatureHeader");
    http_response_code(401);
    exit('Invalid signature');
}

// Parse JSON
$data = json_decode($rawBody, true);

if (!$data || !isset($data['order'])) {
    error_log("PayU notify: invalid JSON or missing 'order' key. Body: $rawBody");
    http_response_code(400);
    exit('Invalid payload');
}

$payuOrder = $data['order'];
$payuOrderId = $payuOrder['orderId'] ?? '';
$payuStatus = $payuOrder['status'] ?? '';
$extOrderId = $payuOrder['extOrderId'] ?? '';

if (empty($payuOrderId) || empty($payuStatus)) {
    error_log("PayU notify: missing orderId or status in notification");
    http_response_code(400);
    exit('Missing required fields');
}

// Map PayU status to our internal payment status
$paymentStatus = payuMapStatus($payuStatus);

// Log the notification for debugging
error_log("PayU notify: orderId=$payuOrderId, extOrderId=$extOrderId, status=$payuStatus -> $paymentStatus");

try {
    // Find order by extOrderId (our order ID) or by payu_order_id
    if (!empty($extOrderId)) {
        $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE id = ? AND payment_method = 'payu'");
        $stmt->execute([(int)$extOrderId]);
    } else {
        $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE payu_order_id = ?");
        $stmt->execute([$payuOrderId]);
    }
    
    $order = $stmt->fetch();
    
    if (!$order) {
        error_log("PayU notify: order not found. extOrderId=$extOrderId, payuOrderId=$payuOrderId");
        // Return 200 anyway to stop PayU from retrying for an order we don't have
        http_response_code(200);
        exit('OK');
    }
    
    // Don't downgrade payment status (e.g., don't go from 'paid' back to 'pending')
    $statusPriority = ['pending' => 1, 'failed' => 2, 'paid' => 3, 'refunded' => 4];
    $currentPriority = $statusPriority[$order['payment_status']] ?? 0;
    $newPriority = $statusPriority[$paymentStatus] ?? 0;
    
    if ($newPriority > $currentPriority) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, payu_order_id = ? WHERE id = ?");
        $stmt->execute([$paymentStatus, $payuOrderId, $order['id']]);
        
        error_log("PayU notify: updated order #{$order['id']} payment_status to $paymentStatus");
        
        // If payment completed, send confirmation email
        if ($paymentStatus === 'paid') {
            payuSendPaymentConfirmation($pdo, $order['id']);
        }
    } else {
        error_log("PayU notify: skipping status update for order #{$order['id']} (current: {$order['payment_status']}, new: $paymentStatus)");
    }
    
} catch (PDOException $e) {
    error_log("PayU notify DB error: " . $e->getMessage());
    // Return 500 so PayU will retry
    http_response_code(500);
    exit('Database error');
}

// Return 200 OK to acknowledge receipt
http_response_code(200);
echo 'OK';

/**
 * Send payment confirmation email when PayU payment is completed.
 */
function payuSendPaymentConfirmation($pdo, $orderId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) return;
        
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        
        $subject = "Payment Confirmed - Order #$orderId - Feltee";
        $body = "Payment has been confirmed for order #$orderId!\n\n";
        $body .= "Customer: {$order['customer_name']}\n";
        $body .= "Phone: {$order['phone']}\n";
        $body .= "Email: {$order['email']}\n";
        $body .= "Total: {$order['total']} PLN\n";
        $body .= "Payment: PayU (Online)\n";
        $body .= "PayU Order ID: {$order['payu_order_id']}\n\n";
        $body .= "Items:\n";
        
        foreach ($items as $item) {
            $body .= "- {$item['product_name']} (Size: {$item['size']}, Color: {$item['color']}) x{$item['quantity']} = {$item['price']} PLN\n";
        }
        
        $headers = "From: noreply@alis.mygamesonline.org\r\n";
        if ($order['email']) {
            $headers .= "Reply-To: {$order['email']}\r\n";
        }
        
        @mail('support@feltee.kg', $subject, $body, $headers);
        
    } catch (Exception $e) {
        error_log("PayU payment confirmation email failed: " . $e->getMessage());
    }
}
