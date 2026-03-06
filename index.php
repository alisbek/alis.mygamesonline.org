<?php
require_once 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM products WHERE featured = 1 AND stock > 0 ORDER BY created_at DESC LIMIT 6");
$featuredProducts = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en");
$categories = $stmt->fetchAll();
?>

<section class="hero">
    <div class="container">
        <h1><?= __('hero.title') ?></h1>
        <p><?= __('hero.subtitle') ?></p>
        <a href="<?= url('/products.php') ?>" class="hero-cta"><?= __('hero.cta') ?></a>
    </div>
</section>

<?php if ($featuredProducts): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title"><?= __('nav.products') ?></h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
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
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="<?= url('/products.php') ?>" class="btn btn-outline btn-lg"><?= __('hero.cta') ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section" style="background:var(--color-white);">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>Our Story / Nasza Historia</h2>
                <p>The idea was born from a desire to combine centuries-old nomadic craftsmanship with modern design. We wanted to show the world the beauty of Kyrgyz felting art and create products that bring yurt warmth into contemporary homes.</p>
                <p>Our seamless felted slippers are our pride - they mold perfectly to the shape of the foot and have no seams, making them particularly comfortable. Natural wool breathes and remains pleasant when warm, provides warmth when cold.</p>
                <a href="<?= url('/about.php') ?>" class="btn btn-primary" style="margin-top:20px;"><?= __('nav.about') ?></a>
            </div>
            <div class="about-image" style="border-radius:var(--radius-lg);overflow:hidden;">
                <img src="https://media.pakamera.net/g/s12775599/1420x0/the-feltee-handcraft-studio_01.jpg" alt="Felting craft" style="width:100%;height:100%;object-fit:cover;">
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--color-bg-alt);">
    <div class="container">
        <h2 class="section-title">Why Choose Feltee?</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:30px;">
            <div style="background:var(--color-white);padding:30px;border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🧦</div>
                <h3 style="color:var(--color-primary);margin-bottom:12px;">Seamless Design</h3>
                <p style="color:var(--color-text-light);">No seams means ultimate comfort. Our slippers mold perfectly to your feet.</p>
            </div>
            <div style="background:var(--color-white);padding:30px;border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🌿</div>
                <h3 style="color:var(--color-primary);margin-bottom:12px;">100% Natural Wool</h3>
                <p style="color:var(--color-text-light);">Thermoregulatory, breathable, and durable. No plastics, no harmful chemicals.</p>
            </div>
            <div style="background:var(--color-white);padding:30px;border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">🏔️</div>
                <h3 style="color:var(--color-primary);margin-bottom:12px;">Kyrgyz Heritage</h3>
                <p style="color:var(--color-text-light);">Centuries of nomadic tradition in every product, handmade by skilled artisans.</p>
            </div>
            <div style="background:var(--color-white);padding:30px;border-radius:var(--radius-md);text-align:center;">
                <div style="font-size:2.5rem;margin-bottom:12px;">✋</div>
                <h3 style="color:var(--color-primary);margin-bottom:12px;">Handcrafted Quality</h3>
                <p style="color:var(--color-text-light);">Each product is carefully made by hand with attention to every detail.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>