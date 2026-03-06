<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$currentLang = getCurrentLang();
$lang = loadLang($currentLang);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $nameRu = trim($_POST['name_ru'] ?? '');
        $nameEn = trim($_POST['name_en'] ?? '');
        $namePl = trim($_POST['name_pl'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $descriptionRu = trim($_POST['description_ru'] ?? '');
        $descriptionEn = trim($_POST['description_en'] ?? '');
        $descriptionPl = trim($_POST['description_pl'] ?? '');
        $sizes = $_POST['sizes'] ?? [];
        $colors = $_POST['colors'] ?? [];
        
        if (empty($nameRu) || empty($nameEn) || empty($namePl) || $price <= 0) {
            $error = 'Please fill in all required fields.';
        } else {
            $image = $editProduct['image'] ?? '';
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $fileType = $_FILES['image']['type'];
                
                if (in_array($fileType, $allowedTypes)) {
                    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $image = uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/products/' . $image);
                }
            }
            
            $sizesJson = json_encode($sizes);
            $colorsJson = json_encode($colors);
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name_ru, name_en, name_pl, description_ru, description_en, description_pl, price, sizes, colors, image, stock, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $nameRu, $nameEn, $namePl, $descriptionRu, $descriptionEn, $descriptionPl, $price, $sizesJson, $colorsJson, $image, $stock, $featured]);
                $success = 'Product added successfully.';
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE products SET category_id=?, name_ru=?, name_en=?, name_pl=?, description_ru=?, description_en=?, description_pl=?, price=?, sizes=?, colors=?, image=?, stock=?, featured=? WHERE id=?");
                $stmt->execute([$categoryId, $nameRu, $nameEn, $namePl, $descriptionRu, $descriptionEn, $descriptionPl, $price, $sizesJson, $colorsJson, $image, $stock, $featured, $id]);
                $success = 'Product updated successfully.';
                header('Location: products.php');
                exit;
            }
        }
    }
    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Product deleted successfully.';
    }
}

$stmt = $pdo->query("SELECT p.*, c.name_en as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en");
$categories = $stmt->fetchAll();

$availableSizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
$availableColors = [
    ['name' => 'Natural', 'code' => '#F5E6D3'],
    ['name' => 'Grey', 'code' => '#808080'],
    ['name' => 'Brown', 'code' => '#8B4513'],
    ['name' => 'Beige', 'code' => '#D4B896'],
    ['name' => 'Blue', 'code' => '#4A6FA5'],
    ['name' => 'Green', 'code' => '#6B8E6B'],
    ['name' => 'Red', 'code' => '#B85450'],
];

$menuItems = [
    'index.php' => __('admin.dashboard'),
    'products.php' => __('admin.products'),
    'orders.php' => __('admin.orders'),
    'categories.php' => __('admin.categories'),
];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin - <?= SITE_NAME ?></title>
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
                <h1><?= __('admin.products') ?></h1>
                <div class="admin-actions">
                    <a href="?add=1" class="btn btn-primary" id="addProductBtn">Add Product</a>
                </div>
            </header>
            
            <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:20px;"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:20px;"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['add']) || $editProduct): ?>
            <div class="admin-section">
                <h2><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h2>
                
                <form class="admin-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?= $editProduct ? 'edit' : 'add' ?>">
                    <?php if ($editProduct): ?>
                    <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="admin-form-row">
                        <div class="form-group">
                            <label>Name (Russian) *</label>
                            <input type="text" name="name_ru" required value="<?= htmlspecialchars($editProduct['name_ru'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Name (English) *</label>
                            <input type="text" name="name_en" required value="<?= htmlspecialchars($editProduct['name_en'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Name (Polish) *</label>
                            <input type="text" name="name_pl" required value="<?= htmlspecialchars($editProduct['name_pl'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id">
                                <option value="">- Select -</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name_en']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (KGS) *</label>
                            <input type="number" name="price" step="0.01" min="0" required value="<?= $editProduct['price'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" min="0" value="<?= $editProduct['stock'] ?? 0 ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description (Russian)</label>
                        <textarea name="description_ru" rows="3"><?= htmlspecialchars($editProduct['description_ru'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Description (English)</label>
                        <textarea name="description_en" rows="3"><?= htmlspecialchars($editProduct['description_en'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Description (Polish)</label>
                        <textarea name="description_pl" rows="3"><?= htmlspecialchars($editProduct['description_pl'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Sizes</label>
                        <div class="checkbox-group">
                            <?php 
                            $selectedSizes = $editProduct ? json_decode($editProduct['sizes'], true) ?? [] : [];
                            foreach ($availableSizes as $size): 
                            ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="sizes[]" value="<?= $size ?>" <?= in_array($size, $selectedSizes) ? 'checked' : '' ?>>
                                <span><?= $size ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Colors</label>
                        <div class="checkbox-group">
                            <?php 
                            $selectedColors = $editProduct ? json_decode($editProduct['colors'], true) ?? [] : [];
                            $selectedColorNames = array_column($selectedColors, 'name');
                            foreach ($availableColors as $color): 
                            ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="colors[]" value="<?= htmlspecialchars(json_encode($color)) ?>" <?= in_array($color['name'], $selectedColorNames) ? 'checked' : '' ?>>
                                <span style="display:inline-block;width:16px;height:16px;background:<?= $color['code'] ?>;border-radius:50%;margin-right:4px;"></span>
                                <span><?= $color['name'] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                        <?php if ($editProduct && $editProduct['image']): ?>
                        <div class="image-preview">
                            <img src="../uploads/products/<?= htmlspecialchars($editProduct['image']) ?>" alt="">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" <?= ($editProduct['featured'] ?? 0) ? 'checked' : '' ?>>
                            <span>Featured product (show on homepage)</span>
                        </label>
                    </div>
                    
                    <div style="display:flex;gap:12px;">
                        <button type="submit" class="btn btn-primary"><?= $editProduct ? 'Update' : 'Add' ?> Product</button>
                        <a href="products.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product['image']): ?>
                                <img src="../uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                <?php else: ?>
                                <div style="width:50px;height:50px;background:var(--color-bg-alt);border-radius:4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name_en']) ?></td>
                            <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                            <td><?= formatPrice($product['price']) ?></td>
                            <td><?= $product['stock'] ?></td>
                            <td><?= $product['featured'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <a href="?edit=<?= $product['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm" style="background:var(--color-error);color:white;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>