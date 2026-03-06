<?php
/**
 * InPost Admin Actions Handler
 * 
 * Handles admin actions for InPost shipping management:
 * - create-shipment: Create InPost shipment for an order
 * - get-label: Download PDF shipping label
 * - create-dispatch: Schedule courier pickup
 * - refresh-status: Refresh shipment status from InPost API
 */

require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/inpost.php';

// Auth check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$orderId = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);

if (!$orderId) {
    header('Location: orders.php');
    exit;
}

// Fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

switch ($action) {
    case 'create-shipment':
        createShipmentAction($pdo, $order);
        break;
    case 'get-label':
        getLabelAction($order);
        break;
    case 'create-dispatch':
        createDispatchAction($pdo, $order);
        break;
    case 'refresh-status':
        refreshStatusAction($pdo, $order);
        break;
    default:
        header('Location: orders.php?id=' . $orderId);
        exit;
}

/**
 * Create an InPost shipment for the given order.
 */
function createShipmentAction($pdo, $order) {
    if ($order['delivery_method'] !== 'inpost') {
        header('Location: orders.php?id=' . $order['id'] . '&error=not_inpost');
        exit;
    }
    
    if (!empty($order['inpost_shipment_id'])) {
        header('Location: orders.php?id=' . $order['id'] . '&error=already_created');
        exit;
    }
    
    if (empty($order['inpost_point_id'])) {
        header('Location: orders.php?id=' . $order['id'] . '&error=no_point');
        exit;
    }
    
    // Fetch order items with product dimensions
    $stmt = $pdo->prepare("
        SELECT oi.*, p.weight_grams, p.length_mm, p.width_mm, p.height_mm 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $items = $stmt->fetchAll();
    
    $result = inpostCreateShipment($order, $items);
    
    if ($result && isset($result['id'])) {
        $trackingNumber = $result['tracking_number'] ?? '';
        $status = $result['status'] ?? 'created';
        
        $stmt = $pdo->prepare("UPDATE orders SET inpost_shipment_id = ?, inpost_tracking_number = ?, inpost_status = ? WHERE id = ?");
        $stmt->execute([$result['id'], $trackingNumber, $status, $order['id']]);
        
        header('Location: orders.php?id=' . $order['id'] . '&inpost_success=shipment_created');
    } else {
        header('Location: orders.php?id=' . $order['id'] . '&error=shipment_failed');
    }
    exit;
}

/**
 * Download PDF label for an InPost shipment.
 */
function getLabelAction($order) {
    if (empty($order['inpost_shipment_id'])) {
        header('Location: orders.php?id=' . $order['id'] . '&error=no_shipment');
        exit;
    }
    
    $pdf = inpostGetLabel($order['inpost_shipment_id']);
    
    if ($pdf) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="inpost-label-order-' . $order['id'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    } else {
        header('Location: orders.php?id=' . $order['id'] . '&error=label_failed');
    }
    exit;
}

/**
 * Create a dispatch order (schedule courier pickup) for an InPost shipment.
 */
function createDispatchAction($pdo, $order) {
    if (empty($order['inpost_shipment_id'])) {
        header('Location: orders.php?id=' . $order['id'] . '&error=no_shipment');
        exit;
    }
    
    $result = inpostCreateDispatch([$order['inpost_shipment_id']]);
    
    if ($result) {
        // Refresh status after dispatch
        $shipment = inpostGetShipment($order['inpost_shipment_id']);
        if ($shipment && isset($shipment['status'])) {
            $stmt = $pdo->prepare("UPDATE orders SET inpost_status = ? WHERE id = ?");
            $stmt->execute([$shipment['status'], $order['id']]);
        }
        
        header('Location: orders.php?id=' . $order['id'] . '&inpost_success=dispatch_created');
    } else {
        header('Location: orders.php?id=' . $order['id'] . '&error=dispatch_failed');
    }
    exit;
}

/**
 * Refresh shipment status from InPost API.
 */
function refreshStatusAction($pdo, $order) {
    if (empty($order['inpost_shipment_id'])) {
        header('Location: orders.php?id=' . $order['id'] . '&error=no_shipment');
        exit;
    }
    
    $shipment = inpostGetShipment($order['inpost_shipment_id']);
    
    if ($shipment) {
        $status = $shipment['status'] ?? $order['inpost_status'];
        $trackingNumber = $shipment['tracking_number'] ?? $order['inpost_tracking_number'];
        
        $stmt = $pdo->prepare("UPDATE orders SET inpost_status = ?, inpost_tracking_number = ? WHERE id = ?");
        $stmt->execute([$status, $trackingNumber, $order['id']]);
        
        header('Location: orders.php?id=' . $order['id'] . '&inpost_success=status_refreshed');
    } else {
        header('Location: orders.php?id=' . $order['id'] . '&error=refresh_failed');
    }
    exit;
}
