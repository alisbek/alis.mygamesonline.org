<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/inpost.php';

$currentLang = getCurrentLang();
$lang = loadLang($currentLang);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$viewOrder = null;
if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $viewOrder = $stmt->fetch();
    
    if ($viewOrder) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $viewOrder['items'] = $stmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    
    header('Location: orders.php?id=' . $orderId . '&updated=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $orderId = (int)$_POST['order_id'];
    $paymentStatus = $_POST['payment_status'];
    $allowedPayStatuses = ['pending', 'paid', 'failed', 'refunded'];
    
    if (in_array($paymentStatus, $allowedPayStatuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->execute([$paymentStatus, $orderId]);
    }
    
    header('Location: orders.php?id=' . $orderId . '&updated=1');
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$allowedStatuses = ['new', 'processing', 'shipped', 'completed', 'cancelled'];

if ($filter !== 'all' && in_array($filter, $allowedStatuses)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$filter]);
} else {
    $filter = 'all';
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
}
$orders = $stmt->fetchAll();

$menuItems = [
    'index.php' => __('admin.dashboard'),
    'products.php' => __('admin.products'),
    'orders.php' => __('admin.orders'),
    'categories.php' => __('admin.categories'),
    'settings.php' => 'Settings',
];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body style="background:var(--color-bg-alt);">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <a href="index.php">Feltee Admin</a>
            </div>
            <nav class="admin-nav">
                <?php foreach ($menuItems as $url => $label): ?>
                <a href="<?= $url ?>" class="admin-nav-link <?= $currentPage === $url ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="admin-sidebar-footer">
                <a href="../index.php" target="_blank">View Site</a>
                <a href="logout.php">Logout</a>
            </div>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1><?= __('admin.orders') ?></h1>
            </header>
            
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success" style="margin-bottom:20px;">Order status updated.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['inpost_msg'])): ?>
            <div class="alert alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($_GET['inpost_msg']) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['inpost_error'])): ?>
            <div class="alert alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($_GET['inpost_error']) ?></div>
            <?php endif; ?>
            
            <?php if ($viewOrder): ?>
            <div class="admin-section">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h2>Order #<?= $viewOrder['id'] ?></h2>
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                </div>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;">
                    <div>
                        <h3 style="margin-bottom:16px;">Customer Information</h3>
                        <p><strong>Name:</strong> <?= htmlspecialchars($viewOrder['customer_name']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($viewOrder['phone']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($viewOrder['email'] ?: '-') ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($viewOrder['address'] ?: '-') ?></p>
                        <p><strong>City:</strong> <?= htmlspecialchars($viewOrder['city'] ?: '-') ?></p>
                        <p><strong>Postal Code:</strong> <?= htmlspecialchars($viewOrder['postal_code'] ?: '-') ?></p>
                        
                        <h3 style="margin:24px 0 16px;">Order Details</h3>
                        <p><strong>Delivery:</strong> <?= $viewOrder['delivery_method'] === 'inpost' ? 'InPost Paczkomat' : ucfirst($viewOrder['delivery_method']) ?></p>
                        <p><strong>Payment:</strong> <?= ucfirst(str_replace('_', ' ', $viewOrder['payment_method'])) ?></p>
                        <p><strong>Payment Status:</strong> <span class="status-badge status-<?= $viewOrder['payment_status'] ?? 'pending' ?>"><?= ucfirst($viewOrder['payment_status'] ?? 'pending') ?></span></p>
                        <?php if (!empty($viewOrder['payu_order_id'])): ?>
                        <p><strong>PayU Order ID:</strong> <?= htmlspecialchars($viewOrder['payu_order_id']) ?></p>
                        <?php endif; ?>
                        <p><strong>Total:</strong> <?= formatPrice($viewOrder['total']) ?></p>
                        <?php if (!empty($viewOrder['shipping_cost']) && $viewOrder['shipping_cost'] > 0): ?>
                        <p><strong>Shipping Cost:</strong> <?= formatPrice($viewOrder['shipping_cost']) ?></p>
                        <?php endif; ?>
                        <?php if ($viewOrder['notes']): ?>
                        <p><strong>Notes:</strong> <?= nl2br(htmlspecialchars($viewOrder['notes'])) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($viewOrder['delivery_method'] === 'inpost'): ?>
                        <div class="inpost-admin-section">
                            <h3>InPost Paczkomat Shipping</h3>
                            
                            <div class="inpost-admin-detail">
                                <strong>Paczkomat:</strong> <?= htmlspecialchars($viewOrder['inpost_point_id'] ?? '-') ?>
                            </div>
                            <?php if (!empty($viewOrder['inpost_point_name'])): ?>
                            <div class="inpost-admin-detail">
                                <strong>Point Name:</strong> <?= htmlspecialchars($viewOrder['inpost_point_name']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($viewOrder['inpost_shipment_id'])): ?>
                            <div class="inpost-admin-detail">
                                <strong>Shipment ID:</strong> <?= htmlspecialchars($viewOrder['inpost_shipment_id']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($viewOrder['inpost_tracking_number'])): ?>
                            <div class="inpost-admin-detail">
                                <strong>Tracking:</strong> <?= htmlspecialchars($viewOrder['inpost_tracking_number']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($viewOrder['inpost_status'])): ?>
                            <div class="inpost-admin-detail">
                                <strong>Status:</strong> 
                                <span class="inpost-status-badge inpost-status-<?= htmlspecialchars($viewOrder['inpost_status']) ?>">
                                    <?= inpostStatusLabel($viewOrder['inpost_status']) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="inpost-admin-actions">
                                <?php if (empty($viewOrder['inpost_shipment_id'])): ?>
                                <form method="post" action="inpost-actions.php" style="display:inline;">
                                    <input type="hidden" name="action" value="create-shipment">
                                    <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                                    <button type="submit" class="inpost-action-btn">Create Shipment</button>
                                </form>
                                <?php else: ?>
                                <form method="post" action="inpost-actions.php" style="display:inline;">
                                    <input type="hidden" name="action" value="get-label">
                                    <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                                    <button type="submit" class="inpost-action-btn">Download Label</button>
                                </form>
                                <form method="post" action="inpost-actions.php" style="display:inline;">
                                    <input type="hidden" name="action" value="create-dispatch">
                                    <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                                    <button type="submit" class="inpost-action-btn">Schedule Pickup</button>
                                </form>
                                <form method="post" action="inpost-actions.php" style="display:inline;">
                                    <input type="hidden" name="action" value="refresh-status">
                                    <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                                    <button type="submit" class="inpost-action-btn secondary">Refresh Status</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h3 style="margin-bottom:16px;">Items</h3>
                        <table class="admin-table" style="margin-bottom:24px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viewOrder['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= $item['size'] ?: '-' ?></td>
                                    <td><?= $item['color'] ?: '-' ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= formatPrice($item['price']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <h3 style="margin-bottom:16px;">Update Status</h3>
                        <form method="post">
                            <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                            <div class="form-group">
                                <select name="status" style="width:auto;">
                                    <option value="new" <?= $viewOrder['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                    <option value="processing" <?= $viewOrder['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $viewOrder['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="completed" <?= $viewOrder['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $viewOrder['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                        </form>
                        
                        <h3 style="margin:24px 0 16px;">Update Payment Status</h3>
                        <form method="post">
                            <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                            <div class="form-group">
                                <select name="payment_status" style="width:auto;">
                                    <option value="pending" <?= ($viewOrder['payment_status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= ($viewOrder['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="failed" <?= ($viewOrder['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="refunded" <?= ($viewOrder['payment_status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                </select>
                            </div>
                            <button type="submit" name="update_payment_status" class="btn btn-primary">Update Payment Status</button>
                        </form>
                        
                        <p style="margin-top:24px;color:var(--color-text-light);">
                            <strong>Created:</strong> <?= date('F j, Y g:i A', strtotime($viewOrder['created_at'])) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="admin-section" style="margin-bottom:20px;">
                <div style="display:flex;gap:12px;">
                    <a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                    <a href="?filter=new" class="btn <?= $filter === 'new' ? 'btn-primary' : 'btn-secondary' ?>">New</a>
                    <a href="?filter=processing" class="btn <?= $filter === 'processing' ? 'btn-primary' : 'btn-secondary' ?>">Processing</a>
                    <a href="?filter=shipped" class="btn <?= $filter === 'shipped' ? 'btn-primary' : 'btn-secondary' ?>">Shipped</a>
                    <a href="?filter=completed" class="btn <?= $filter === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                    <a href="?filter=cancelled" class="btn <?= $filter === 'cancelled' ? 'btn-primary' : 'btn-secondary' ?>">Cancelled</a>
                </div>
            </div>
            
            <div class="admin-section">
                <?php if ($orders): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total</th>
                            <th>Delivery</th>
                            <th>Payment</th>
                            <th>Pay Status</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td><?= $order['delivery_method'] === 'inpost' ? '<span style="color:#92400e;font-weight:600;">InPost</span>' : ucfirst($order['delivery_method']) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
                            <td><span class="status-badge status-<?= $order['payment_status'] ?? 'pending' ?>"><?= ucfirst($order['payment_status'] ?? 'pending') ?></span></td>
                            <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td><a href="?id=<?= $order['id'] ?>" class="btn btn-sm btn-secondary">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No orders found.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>