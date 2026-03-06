<?php
require_once 'config/db.php';

// Generate new admin password hash
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Update or insert admin user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
$stmt->execute();
$exists = $stmt->fetchColumn();

if ($exists) {
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    echo "Admin password updated successfully.\n";
} else {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email) VALUES ('admin', ?, 'admin@feltee.com')");
    $stmt->execute([$hash]);
    echo "Admin user created successfully.\n";
}

echo "Username: admin\n";
echo "Password: admin123\n";
echo "Hash: $hash\n";
echo "\nDELETE THIS FILE IMMEDIATELY!";
