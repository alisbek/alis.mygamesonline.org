<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$fixedUsername = 'asel';

$currentLang = getCurrentLang();
$lang = loadLang($currentLang);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    // One-time migration: if legacy "admin" exists and "asel" doesn't, rename it.
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$fixedUsername]);
    $fixedUser = $stmt->fetch();
    if (!$fixedUser) {
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = 'admin'");
        $stmt->execute();
        $legacyAdmin = $stmt->fetch();
        if ($legacyAdmin) {
            $stmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
            $stmt->execute([$fixedUsername, $legacyAdmin['id']]);
        }
    }
    
    if (empty($password)) {
        $error = 'Please enter password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$fixedUsername]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $fixedUsername;
            $_SESSION['admin_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg-alt);
        }
        .login-form {
            background: var(--color-white);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
        }
        .login-form h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--color-primary);
        }
        .login-form .form-group {
            margin-bottom: 20px;
        }
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .login-form input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 1rem;
        }
        .login-form input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        .login-form .btn {
            width: 100%;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-page">
        <form class="login-form" method="post">
            <h1>Feltee Admin</h1>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="alert" style="margin-bottom:20px;background:#e8f5e9;color:#1b5e20;border:1px solid #c8e6c9;">
                Login: <strong><?= htmlspecialchars($fixedUsername) ?></strong>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">Login</button>
        </form>
    </div>
</body>
</html>