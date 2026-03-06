<?php
require_once 'includes/header.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query order from database to show payment status
$order = null;
if ($orderId) {
    $stmt = $pdo->prepare("SELECT id, payment_method, payment_status, total FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
}
?>

<section class="section">
    <div class="container">
        <div class="order-success">
            <?php if ($order && $order['payment_method'] === 'payu'): ?>
                <?php if ($order['payment_status'] === 'paid'): ?>
                    <div class="payment-status-icon payment-status-paid">&#10003;</div>
                    <h1 style="color:var(--color-success);"><?= __('order.payment_completed') ?></h1>
                    <p><?= __('order.payment_completed_msg') ?></p>
                <?php elseif ($order['payment_status'] === 'failed'): ?>
                    <div class="payment-status-icon payment-status-failed">&#10007;</div>
                    <h1 style="color:var(--color-error);"><?= __('order.payment_failed') ?></h1>
                    <p><?= __('order.payment_failed_msg') ?></p>
                <?php else: ?>
                    <div class="payment-status-icon payment-status-pending">&#8987;</div>
                    <h1 style="color:var(--color-warning, #e6a800);"><?= __('order.payment_pending') ?></h1>
                    <p><?= __('order.payment_pending_msg') ?></p>
                <?php endif; ?>
            <?php else: ?>
                <h1 style="color:var(--color-success);"><?= __('order.success') ?></h1>
                <p><?= __('order.success_msg') ?></p>
            <?php endif; ?>
            
            <?php if ($orderId): ?>
            <p style="font-size:1.25rem;font-weight:600;margin:24px 0;"><?= sprintf(__('order.number'), $orderId) ?></p>
            <?php endif; ?>
            
            <?php if ($order && $order['payment_status'] === 'paid'): ?>
            <p style="margin-bottom:24px;color:var(--color-success);font-weight:500;">
                <?= __('order.amount_paid') ?>: <?= formatPrice($order['total']) ?>
            </p>
            <?php endif; ?>
            
            <a href="<?= url() ?>" class="btn btn-primary btn-lg"><?= __('nav.home') ?></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
