<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$currentLang = getCurrentLang();
$lang = loadLang($currentLang);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$fixedUsername = 'asel';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$fixedUsername]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Admin account not found.';
        } elseif (!password_verify($currentPassword, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newHash, $user['id']]);
            $success = 'Password updated successfully.';
        }
    }
}

$menuItems = [
    'index.php' => __('admin.dashboard'),
    'products.php' => __('admin.products'),
    'orders.php' => __('admin.orders'),
    'categories.php' => __('admin.categories'),
    'settings.php' => 'Settings',
];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin - <?= SITE_NAME ?></title>
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
                <h1>Settings</h1>
                <div class="admin-user">
                    Login: <?= htmlspecialchars($fixedUsername) ?>
                </div>
            </header>

            <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:20px;"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:20px;"><?= $error ?></div>
            <?php endif; ?>

            <div class="admin-section">
                <h2>Change Password</h2>
                <form class="admin-form" method="post">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
