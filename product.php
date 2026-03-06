<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name_{$currentLang} as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . url('/products.php'));
    exit;
}

$sizes = json_decode($product['sizes'], true) ?: [];
$colors = json_decode($product['colors'], true) ?: [];
$gallery = json_decode($product['gallery'], true) ?: [];
$isOutOfStock = $product['stock'] <= 0;
$productName = htmlspecialchars($product['name_' . $currentLang]);
$productDesc = $product['description_' . $currentLang];
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <div class="container">
        <a href="<?= url() ?>"><?= __('nav.home') ?></a>
        <span class="breadcrumb-sep">/</span>
        <a href="<?= url('/products.php') ?>"><?= __('nav.products') ?></a>
        <?php if ($product['category_name']): ?>
        <span class="breadcrumb-sep">/</span>
        <a href="<?= url('/products.php?category=' . urlencode($product['category_slug'])) ?>"><?= htmlspecialchars($product['category_name']) ?></a>
        <?php endif; ?>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current"><?= $productName ?></span>
    </div>
</nav>

<section class="product-page">
    <div class="container">
        <div class="product-detail">
            <!-- Gallery -->
            <div class="product-gallery">
                <div class="product-gallery-main">
                    <?php if ($isOutOfStock): ?>
                    <span class="product-badge product-badge-sold"><?= __('product.out_of_stock') ?></span>
                    <?php endif; ?>
                    <?php if ($product['image']): ?>
                        <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= $productName ?>" 
                             id="main-image"
                             loading="eager">
                    <?php else: ?>
                        <div class="product-no-image"><?= __('product.no_image') ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($gallery || $product['image']): ?>
                <div class="product-thumbs-strip">
                    <?php if ($product['image']): ?>
                    <button class="product-thumb active" onclick="changeImage('<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>', this)" type="button">
                        <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="" loading="lazy">
                    </button>
                    <?php endif; ?>
                    <?php foreach ($gallery as $img): ?>
                    <button class="product-thumb" onclick="changeImage('<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($img) ?>', this)" type="button">
                        <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($img) ?>" alt="" loading="lazy">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="product-info-panel">
                <p class="product-brand"><?= __('product.brand') ?></p>
                <h1 class="product-title"><?= $productName ?></h1>
                <p class="product-price-large"><?= formatPrice($product['price']) ?></p>
                
                <?php if ($colors && count($colors) > 1): ?>
                <div class="product-option-group">
                    <label class="product-option-label"><?= __('product.color') ?></label>
                    <div class="color-options">
                        <?php foreach ($colors as $i => $color): ?>
                        <button type="button" 
                                class="color-option<?= $i === 0 ? ' selected' : '' ?>" 
                                style="background-color:<?= htmlspecialchars($color['code']) ?>" 
                                data-color="<?= htmlspecialchars($color['name']) ?>" 
                                title="<?= htmlspecialchars($color['name']) ?>" 
                                onclick="selectColor(this)">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <span class="color-name-display"><?= htmlspecialchars($colors[0]['name']) ?></span>
                </div>
                <?php elseif ($colors && count($colors) === 1): ?>
                <input type="hidden" id="selected-color" value="<?= htmlspecialchars($colors[0]['name']) ?>">
                <?php endif; ?>
                
                <?php if ($sizes): ?>
                <div class="product-option-group">
                    <label class="product-option-label"><?= __('product.size') ?></label>
                    <div class="size-options">
                        <?php foreach ($sizes as $i => $size): ?>
                        <button type="button" 
                                class="size-option<?= count($sizes) === 1 ? ' selected' : '' ?>" 
                                data-size="<?= $size ?>" 
                                onclick="selectSize(this)"><?= $size ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="product-option-group">
                    <label class="product-option-label"><?= __('product.quantity') ?></label>
                    <div class="quantity-selector">
                        <button type="button" class="qty-btn" onclick="changeQty(-1)" <?= $isOutOfStock ? 'disabled' : '' ?>>-</button>
                        <input type="number" id="qty-input" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                        <button type="button" class="qty-btn" onclick="changeQty(1)" <?= $isOutOfStock ? 'disabled' : '' ?>>+</button>
                    </div>
                    <?php if (!$isOutOfStock): ?>
                    <p class="stock-info"><?= sprintf(__('product.in_stock'), $product['stock']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="product-actions">
                    <?php if ($isOutOfStock): ?>
                    <button type="button" class="btn btn-primary btn-lg product-add-btn" disabled>
                        <?= __('product.out_of_stock') ?>
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-primary btn-lg product-add-btn" 
                            id="add-to-cart-btn"
                            data-product-id="<?= $product['id'] ?>"
                            data-product-name="<?= $productName ?>"
                            data-price="<?= $product['price'] ?>"
                            data-image="<?= htmlspecialchars($product['image']) ?>"
                            onclick="addToCartFromProduct()">
                        <?= __('product.add_to_cart') ?>
                    </button>
                    <?php endif; ?>
                </div>
                <div id="cart-notification" class="cart-notification" style="display:none;">
                    <?= __('product.added_to_cart') ?>
                </div>
            </div>
        </div>
        
        <!-- Tabs Section -->
        <div class="product-tabs-section">
            <div class="product-tabs-nav">
                <button class="tab-btn active" onclick="switchTab('description', this)" type="button"><?= __('product.description') ?></button>
                <button class="tab-btn" onclick="switchTab('details', this)" type="button"><?= __('product.details') ?></button>
                <button class="tab-btn" onclick="switchTab('shipping', this)" type="button"><?= __('product.shipping') ?></button>
            </div>
            
            <div class="product-tab-content">
                <!-- Description Tab -->
                <div class="tab-panel active" id="tab-description">
                    <?php if ($productDesc): ?>
                    <div class="product-desc-text">
                        <?= nl2br(htmlspecialchars($productDesc)) ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted"><?= __('product.no_image') ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Details Tab -->
                <div class="tab-panel" id="tab-details">
                    <table class="product-attributes">
                        <tr>
                            <th><?= __('product.material') ?></th>
                            <td><?= __('product.material_value') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('product.sole') ?></th>
                            <td><?= __('product.sole_value') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('product.properties') ?></th>
                            <td><?= __('product.properties_value') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('product.handmade') ?></th>
                            <td><?= __('product.handmade_value') ?></td>
                        </tr>
                        <?php if ($sizes): ?>
                        <tr>
                            <th><?= __('product.size') ?></th>
                            <td><?= implode(', ', $sizes) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($colors): ?>
                        <tr>
                            <th><?= __('product.color') ?></th>
                            <td><?= implode(', ', array_column($colors, 'name')) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- Shipping Tab -->
                <div class="tab-panel" id="tab-shipping">
                    <table class="product-attributes">
                        <tr>
                            <th><?= __('product.shipping_domestic') ?></th>
                            <td><?= __('product.shipping_domestic_value') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('product.shipping_international') ?></th>
                            <td><?= __('product.shipping_international_value') ?></td>
                        </tr>
                        <tr>
                            <th><?= __('product.returns') ?></th>
                            <td><?= __('product.returns_value') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function changeImage(src, thumb) {
    document.getElementById('main-image').src = src;
    document.querySelectorAll('.product-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}

function selectSize(el) {
    document.querySelectorAll('.size-option').forEach(function(o) { o.classList.remove('selected'); });
    el.classList.add('selected');
}

function selectColor(el) {
    document.querySelectorAll('.color-option').forEach(function(o) { o.classList.remove('selected'); });
    el.classList.add('selected');
    var display = document.querySelector('.color-name-display');
    if (display) display.textContent = el.getAttribute('data-color');
}

function changeQty(delta) {
    var input = document.getElementById('qty-input');
    var val = parseInt(input.value) + delta;
    var max = parseInt(input.max);
    if (val >= 1 && val <= max) input.value = val;
}

function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.getElementById('tab-' + tabId).classList.add('active');
    btn.classList.add('active');
}

function addToCartFromProduct() {
    var btn = document.getElementById('add-to-cart-btn');
    var sizeEl = document.querySelector('.size-option.selected');
    var colorEl = document.querySelector('.color-option.selected');
    var hiddenColor = document.getElementById('selected-color');
    
    var size = sizeEl ? sizeEl.getAttribute('data-size') : '';
    var color = colorEl ? colorEl.getAttribute('data-color') : (hiddenColor ? hiddenColor.value : '');
    var qty = parseInt(document.getElementById('qty-input').value);
    
    // Build form data and submit via AJAX to cart
    var formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', btn.getAttribute('data-product-id'));
    formData.append('size', size);
    formData.append('color', color);
    formData.append('quantity', qty);
    
    fetch('<?= SITE_URL ?>/cart-api.php', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            // Show notification
            var notif = document.getElementById('cart-notification');
            notif.style.display = 'block';
            setTimeout(function() { notif.style.display = 'none'; }, 3000);
            // Update cart count in header
            var countEl = document.querySelector('.cart-count');
            if (countEl) {
                countEl.textContent = data.cart_count;
            } else {
                var cartLink = document.querySelector('.cart-link');
                if (cartLink) {
                    var span = document.createElement('span');
                    span.className = 'cart-count';
                    span.textContent = data.cart_count;
                    cartLink.appendChild(span);
                }
            }
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
