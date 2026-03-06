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

$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $nameRu = trim($_POST['name_ru'] ?? '');
        $nameEn = trim($_POST['name_en'] ?? '');
        $namePl = trim($_POST['name_pl'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        
        if (empty($nameRu) || empty($nameEn) || empty($namePl) || empty($slug)) {
            $error = 'Please fill in all fields.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO categories (name_ru, name_en, name_pl, slug) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nameRu, $nameEn, $namePl, $slug]);
                $success = 'Category added successfully.';
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE categories SET name_ru=?, name_en=?, name_pl=?, slug=? WHERE id=?");
                $stmt->execute([$nameRu, $nameEn, $namePl, $slug, $id]);
                $success = 'Category updated successfully.';
                header('Location: categories.php');
                exit;
            }
        }
    }
    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Category deleted successfully.';
    }
}

$stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name_en");
$categories = $stmt->fetchAll();

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
    <title>Categories - Admin - <?= SITE_NAME ?></title>
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
                <h1><?= __('admin.categories') ?></h1>
                <div class="admin-actions">
                    <a href="?add=1" class="btn btn-primary">Add Category</a>
                </div>
            </header>
            
            <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:20px;"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:20px;"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['add']) || $editCategory): ?>
            <div class="admin-section">
                <h2><?= $editCategory ? 'Edit Category' : 'Add New Category' ?></h2>
                
                <form class="admin-form" method="post">
                    <input type="hidden" name="action" value="<?= $editCategory ? 'edit' : 'add' ?>">
                    <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="admin-form-row">
                        <div class="form-group">
                            <label>Name (Russian) *</label>
                            <input type="text" name="name_ru" required value="<?= htmlspecialchars($editCategory['name_ru'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Name (English) *</label>
                            <input type="text" name="name_en" required value="<?= htmlspecialchars($editCategory['name_en'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Name (Polish) *</label>
                            <input type="text" name="name_pl" required value="<?= htmlspecialchars($editCategory['name_pl'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Slug (URL) *</label>
                        <input type="text" name="slug" required value="<?= htmlspecialchars($editCategory['slug'] ?? '') ?>" placeholder="e.g., women, men, children">
                        <small style="color:var(--color-text-light);">Used in URLs. Lowercase, no spaces.</small>
                    </div>
                    
                    <div style="display:flex;gap:12px;">
                        <button type="submit" class="btn btn-primary"><?= $editCategory ? 'Update' : 'Add' ?> Category</button>
                        <a href="categories.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name (EN)</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td><?= htmlspecialchars($category['name_en']) ?></td>
                            <td><?= htmlspecialchars($category['slug']) ?></td>
                            <td><?= $category['product_count'] ?></td>
                            <td>
                                <a href="?edit=<?= $category['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <?php if ($category['product_count'] == 0): ?>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this category?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                    <button type="submit" class="btn btn-sm" style="background:var(--color-error);color:white;">Delete</button>
                                </form>
                                <?php endif; ?>
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