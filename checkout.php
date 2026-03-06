<?php
require_once 'includes/header.php';
require_once 'includes/payu.php';

$cart = getCart();

if (!$cart) {
    header('Location: ' . url('/cart.php'));
    exit;
}

$productIds = array_column($cart, 'id');
$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$productsRaw = $stmt->fetchAll();
$products = [];
foreach ($productsRaw as $row) {
    $products[$row['id']] = $row;
}

$cartItems = [];
$total = 0;

foreach ($cart as $item) {
    if (isset($products[$item['id']])) {
        $product = $products[$item['id']];
        $cartItems[] = [
            'id' => $item['id'],
            'name' => $product['name_' . $currentLang],
            'price' => $product['price'],
            'size' => $item['size'] ?? '',
            'color' => $item['color'] ?? '',
            'quantity' => $item['quantity']
        ];
        $total += $product['price'] * $item['quantity'];
    }
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $postal = sanitize($_POST['postal'] ?? '');
    $delivery = sanitize($_POST['delivery'] ?? '');
    $payment = sanitize($_POST['payment'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($phone) || empty($delivery) || empty($payment)) {
        $error = __('checkout.error_required');
    } elseif (!in_array($payment, ['cash', 'bank_transfer', 'payu'])) {
        $error = __('checkout.error_required');
    } else {
        try {
            // Wrap order creation in a transaction
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, email, address, city, postal_code, delivery_method, payment_method, payment_status, total, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, 'new')");
            $stmt->execute([$name, $phone, $email, $address, $city, $postal, $delivery, $payment, $total, $notes]);
            
            $orderId = $pdo->lastInsertId();
            
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, color, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($cartItems as $item) {
                $stmtItem->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['size'],
                    $item['color'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $pdo->commit();
            
            // If PayU, create PayU order and redirect
            if ($payment === 'payu') {
                // Build PayU products array (prices in grosze)
                $payuProducts = [];
                foreach ($cartItems as $item) {
                    $payuProducts[] = [
                        'name' => $item['name'],
                        'unitPrice' => (string)((int)($item['price'] * 100)),
                        'quantity' => (string)$item['quantity'],
                    ];
                }
                
                // Parse buyer name into first/last
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';
                
                // Map current language to PayU language codes
                $payuLangMap = ['pl' => 'pl', 'en' => 'en', 'ru' => 'en', 'de' => 'de', 'fr' => 'fr'];
                $payuLang = $payuLangMap[$currentLang] ?? 'en';
                
                $orderData = [
                    'orderId' => $orderId,
                    'description' => 'Feltee Order #' . $orderId,
                    'totalAmount' => (int)($total * 100), // Convert PLN to grosze
                    'customerIp' => payuGetCustomerIp(),
                    'buyer' => [
                        'email' => $email ?: 'customer@feltee.com',
                        'phone' => $phone,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'language' => $payuLang,
                    ],
                    'products' => $payuProducts,
                    'notifyUrl' => SITE_URL . '/payu-notify.php',
                    'continueUrl' => url('/order-success.php?id=' . $orderId),
                ];
                
                $result = payuCreateOrder($orderData);
                
                if ($result && !empty($result['redirectUri'])) {
                    // Save PayU order ID to our database
                    $stmt = $pdo->prepare("UPDATE orders SET payu_order_id = ? WHERE id = ?");
                    $stmt->execute([$result['orderId'], $orderId]);
                    
                    // Clear cart
                    unset($_SESSION['cart']);
                    
                    // Redirect to PayU payment page
                    header('Location: ' . $result['redirectUri']);
                    exit;
                } else {
                    // PayU API failed — mark order as failed, show error
                    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
                    $stmt->execute([$orderId]);
                    $error = __('checkout.error_payu');
                }
            } else {
                // Offline payment (cash / bank_transfer) — standard flow
                // Send notification email
                $subject = "New Order #$orderId - Feltee";
                $body = "New order received!\n\n";
                $body .= "Order #: $orderId\n";
                $body .= "Customer: $name\n";
                $body .= "Phone: $phone\n";
                $body .= "Email: $email\n";
                $body .= "Address: $address, $city $postal\n";
                $body .= "Delivery: $delivery\n";
                $body .= "Payment: $payment\n";
                $body .= "Total: " . formatPrice($total) . "\n\n";
                $body .= "Items:\n";
                
                foreach ($cartItems as $item) {
                    $body .= "- {$item['name']} (Size: {$item['size']}, Color: {$item['color']}) x{$item['quantity']} = " . formatPrice($item['price'] * $item['quantity']) . "\n";
                }
                
                if ($notes) {
                    $body .= "\nNotes: $notes\n";
                }
                
                $headers = "From: noreply@alis.mygamesonline.org\r\n";
                $headers .= "Reply-To: $email\r\n";
                
                @mail('support@feltee.kg', $subject, $body, $headers);
                
                unset($_SESSION['cart']);
                
                header('Location: ' . url('/order-success.php?id=' . $orderId));
                exit;
            }
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Checkout error: " . $e->getMessage());
            $error = __('checkout.error_generic');
        }
    }
}
?>

<section class="section checkout-page">
    <div class="container">
        <h1 class="section-title"><?= __('checkout.title') ?></h1>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="checkout-content">
            <form class="checkout-form" method="post">
                <?= csrfField() ?>
                
                <h2><?= __('contact.title') ?></h2>
                
                <div class="form-group">
                    <label for="name"><?= __('checkout.name') ?> *</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone"><?= __('checkout.phone') ?> *</label>
                        <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><?= __('checkout.email') ?></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                
                <h2 style="margin-top:32px;"><?= __('checkout.delivery') ?></h2>
                
                <div class="form-group">
                    <label><?= __('checkout.delivery') ?> *</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="delivery" value="pickup" required <?= ($_POST['delivery'] ?? '') === 'pickup' ? 'checked' : '' ?>>
                            <?= __('checkout.delivery.pickup') ?> - <?= __('checkout.free') ?>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="delivery" value="courier" <?= ($_POST['delivery'] ?? '') === 'courier' ? 'checked' : '' ?>>
                            <?= __('checkout.delivery.courier') ?>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="delivery" value="post" <?= ($_POST['delivery'] ?? '') === 'post' ? 'checked' : '' ?>>
                            <?= __('checkout.delivery.post') ?>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address"><?= __('checkout.address') ?></label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city"><?= __('checkout.city') ?></label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="postal"><?= __('checkout.postal') ?></label>
                        <input type="text" id="postal" name="postal" value="<?= htmlspecialchars($_POST['postal'] ?? '') ?>">
                    </div>
                </div>
                
                <h2 style="margin-top:32px;"><?= __('checkout.payment') ?></h2>
                
                <div class="form-group">
                    <label><?= __('checkout.payment') ?> *</label>
                    <div class="radio-group">
                        <label class="radio-label payment-option">
                            <input type="radio" name="payment" value="payu" required <?= ($_POST['payment'] ?? '') === 'payu' ? 'checked' : '' ?>>
                            <span class="payment-label">
                                <?= __('checkout.payment.payu') ?>
                                <small class="payment-desc"><?= __('checkout.payment.payu_desc') ?></small>
                            </span>
                        </label>
                        <label class="radio-label payment-option">
                            <input type="radio" name="payment" value="bank_transfer" <?= ($_POST['payment'] ?? '') === 'bank_transfer' ? 'checked' : '' ?>>
                            <span class="payment-label"><?= __('checkout.payment.bank') ?></span>
                        </label>
                        <label class="radio-label payment-option">
                            <input type="radio" name="payment" value="cash" <?= ($_POST['payment'] ?? '') === 'cash' ? 'checked' : '' ?>>
                            <span class="payment-label"><?= __('checkout.payment.cash') ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes"><?= __('checkout.notes') ?></label>
                    <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;margin-top:24px;"><?= __('checkout.submit') ?></button>
            </form>
            
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
                
                <a href="<?= url('/cart.php') ?>" style="display:block;text-align:center;margin-top:16px;"><?= __('checkout.edit_cart') ?></a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
