<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name_{$currentLang} as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . url('/products.php'));
    exit;
}

$sizes = json_decode($product['sizes'], true) ?: [];
$colors = json_decode($product['colors'], true) ?: [];
$gallery = json_decode($product['gallery'], true) ?: [];
?>

<section class="section">
    <div class="container">
        <div class="product-detail">
            <div class="product-gallery">
                <div class="product-main-image">
                    <?php if ($product['image']): ?>
                        <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name_' . $currentLang]) ?>" id="main-image">
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--color-text-light);"><?= __('product.no_image') ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($gallery): ?>
                <div class="product-thumbnails">
                    <?php if ($product['image']): ?>
                    <div class="product-thumb active" onclick="changeImage('<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>', this)">
                        <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="">
                    </div>
                    <?php endif; ?>
                    <?php foreach ($gallery as $img): ?>
                    <div class="product-thumb" onclick="changeImage('<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($img) ?>', this)">
                        <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($img) ?>" alt="">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="product-details">
                <?php if ($product['category_name']): ?>
                <p style="color:var(--color-primary);margin-bottom:8px;"><?= htmlspecialchars($product['category_name']) ?></p>
                <?php endif; ?>
                
                <h1><?= htmlspecialchars($product['name_' . $currentLang]) ?></h1>
                
                <p class="price"><?= formatPrice($product['price']) ?></p>
                
                <?php if ($sizes): ?>
                <div class="product-options">
                    <div class="option-group">
                        <label><?= __('product.size') ?></label>
                        <div class="size-options">
                            <?php foreach ($sizes as $size): ?>
                            <button type="button" class="size-option" data-size="<?= $size ?>" onclick="selectSize(this)"><?= $size ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($colors): ?>
                <div class="product-options">
                    <div class="option-group">
                        <label><?= __('product.color') ?></label>
                        <div class="color-options">
                            <?php foreach ($colors as $color): ?>
                            <button type="button" class="color-option" style="background-color:<?= htmlspecialchars($color['code']) ?>" data-color="<?= htmlspecialchars($color['name']) ?>" title="<?= htmlspecialchars($color['name']) ?>" onclick="selectColor(this)"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="option-group">
                    <label><?= __('product.quantity') ?></label>
                    <div class="quantity-input">
                        <button type="button" class="quantity-btn" data-action="decrease" onclick="this.nextElementSibling.stepDown()">-</button>
                        <input type="number" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                        <button type="button" class="quantity-btn" data-action="increase" onclick="this.previousElementSibling.stepUp()">+</button>
                    </div>
                    <p style="font-size:0.875rem;color:var(--color-text-light);margin-top:8px;">
                        <?= sprintf(__('product.in_stock'), $product['stock']) ?>
                    </p>
                </div>
                
                <div class="product-actions">
                    <button type="button" class="btn btn-primary btn-lg add-to-cart-btn" 
                            data-product-id="<?= $product['id'] ?>"
                            data-product-name="<?= htmlspecialchars($product['name_' . $currentLang]) ?>"
                            data-price="<?= $product['price'] ?>"
                            data-image="<?= $product['image'] ?>">
                        <?= __('product.add_to_cart') ?>
                    </button>
                </div>
                
                <?php if ($product['description_' . $currentLang]): ?>
                <div class="product-description">
                    <h2><?= __('product.description') ?></h2>
                    <p><?= nl2br(htmlspecialchars($product['description_' . $currentLang])) ?></p>
                </div>
                <?php endif; ?>
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
}
</script>

<?php require_once 'includes/footer.php'; ?>