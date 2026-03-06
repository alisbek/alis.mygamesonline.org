<?php
require_once 'includes/header.php';

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where = 'WHERE stock > 0';
$params = [];

if ($categoryId > 0) {
    $where .= ' AND category_id = ?';
    $params[] = $categoryId;
}

$orderBy = match($sort) {
    'price_low' => 'price ASC',
    'price_high' => 'price DESC',
    default => 'created_at DESC'
};

$stmt = $pdo->prepare("SELECT * FROM products $where ORDER BY $orderBy");
$stmt->execute($params);
$products = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name_" . $currentLang);
$categories = $stmt->fetchAll();
?>

<section class="section">
    <div class="container">
        <h1 class="section-title"><?= __('products.title') ?></h1>
        
        <form class="filters" method="get">
            <div class="filter-group">
                <label><?= __('products.filter.category') ?></label>
                <select name="category" id="filter-category">
                    <option value=""><?= __('products.filter.all') ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name_' . $currentLang]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><?= __('products.filter.price') ?></label>
                <select name="sort" id="filter-sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>
        </form>
        
        <?php if ($products): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <a href="<?= url('/product.php?id=' . $product['id']) ?>" class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="<?= SITE_URL ?>/uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name_' . $currentLang]) ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--color-text-light);">No image</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name_' . $currentLang]) ?></h3>
                        <p class="product-price"><?= formatPrice($product['price']) ?></p>
                        <?php
                        $sizes = json_decode($product['sizes'], true) ?: [];
                        if ($sizes):
                        ?>
                        <p class="product-sizes"><?= __('product.size') ?>: <?= implode(', ', $sizes) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="cart-empty">
                <p><?= __('products.no_products') ?></p>
                <a href="<?= url('/products.php') ?>" class="btn btn-primary"><?= __('cart.continue') ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>