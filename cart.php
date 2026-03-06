<?php
require_once 'includes/header.php';

$cartItems = [];
$cart = getCart();

if ($cart) {
    $productIds = array_column($cart, 'id');
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    foreach ($cart as $index => $item) {
        if (isset($products[$item['id']])) {
            $product = $products[$item['id']];
            $cartItems[] = [
                'index' => $index,
                'id' => $item['id'],
                'name' => $product['name_' . $currentLang],
                'price' => $product['price'],
                'size' => $item['size'] ?? '',
                'color' => $item['color'] ?? '',
                'quantity' => $item['quantity'],
                'image' => $product['image']
            ];
        }
    }
}

$total = 0;
?>

<section class="section cart-page">
    <div class="container">
        <h1 class="section-title"><?= __('cart.title') ?></h1>
        
        <?php if ($cartItems): ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <?php $total += $item['price'] * $item['quantity']; ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php if ($item['image']): ?>
                            <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($item['image']) ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-details">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="cart-item-meta">
                            <?= $item['size'] ? __('product.size') . ': ' . $item['size'] : '' ?>
                            <?= $item['color'] ? ' | ' . __('product.color') . ': ' . $item['color'] : '' ?>
                        </p>
                        <div class="quantity-input">
                            <button type="button" class="quantity-btn" data-action="decrease">-</button>
                            <input type="number" value="<?= $item['quantity'] ?>" min="1" readonly>
                            <button type="button" class="quantity-btn" data-action="increase">+</button>
                        </div>
                        <p class="cart-item-price"><?= formatPrice($item['price'] * $item['quantity']) ?></p>
                    </div>
                    <button type="button" class="cart-item-remove" data-index="<?= $item['index'] ?>">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h2><?= __('cart.title') ?></h2>
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-summary-row">
                    <span><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></span>
                    <span><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="cart-summary-row cart-summary-total">
                    <span><?= __('cart.total') ?></span>
                    <span><?= formatPrice($total) ?></span>
                </div>
                <a href="<?= url('/checkout.php') ?>" class="btn btn-primary checkout-btn"><?= __('cart.checkout') ?></a>
                <a href="<?= url('/products.php') ?>" class="btn btn-secondary" style="width:100%;margin-top:12px;"><?= __('cart.continue') ?></a>
            </div>
        </div>
        <?php else: ?>
        <div class="cart-empty">
            <p><?= __('cart.empty') ?></p>
            <a href="<?= url('/products.php') ?>" class="btn btn-primary"><?= __('cart.continue') ?></a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>