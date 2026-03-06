<?php
require_once 'includes/header.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<section class="section">
    <div class="container">
        <div class="order-success">
            <h1 style="color:var(--color-success);"><?= __('order.success') ?></h1>
            <p><?= __('order.success_msg') ?></p>
            <?php if ($orderId): ?>
            <p style="font-size:1.25rem;font-weight:600;margin:24px 0;"><?= sprintf(__('order.number'), $orderId) ?></p>
            <?php endif; ?>
            <a href="<?= url() ?>" class="btn btn-primary btn-lg"><?= __('nav.home') ?></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>