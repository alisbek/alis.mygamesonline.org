<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$currentLang = getCurrentLang();
$lang = loadLang($currentLang);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'");
$newOrders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock > 0");
$totalProducts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'");
$totalRevenue = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();

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
    <title>Admin - <?= SITE_NAME ?></title>
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
                <h1><?= __('admin.dashboard') ?></h1>
                <div class="admin-user">
                    Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>
                </div>
            </header>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-value"><?= $newOrders ?></div>
                    <div class="stat-label">New Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $totalOrders ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $totalProducts ?></div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= formatPrice($totalRevenue) ?></div>
                    <div class="stat-label">Revenue</div>
                </div>
            </div>
            
            <div class="admin-section">
                <h2>Recent Orders</h2>
                <?php if ($recentOrders): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                            <td><?= formatPrice($order['total']) ?></td>
                            <td><span class="status-badge status-<?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td><a href="orders.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-secondary">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No orders yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>