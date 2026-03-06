<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/db.php';

echo "=== InPost Database Setup ===\n\n";

// Step 1: Update product dimensions
echo "Step 1: Updating product dimensions...\n";
$stmt = $pdo->prepare("UPDATE products SET weight_grams = ?, length_mm = ?, width_mm = ?, height_mm = ? WHERE id BETWEEN 1 AND 20");
$stmt->execute([300, 300, 200, 80]);
echo "Updated " . $stmt->rowCount() . " products.\n";

// Step 2: Add missing columns to products table
echo "\nStep 2: Checking products table columns...\n";
$columns = ['weight_grams', 'length_mm', 'width_mm', 'height_mm'];
foreach ($columns as $col) {
    try {
        $pdo->query("SELECT $col FROM products LIMIT 1");
        echo "  - $col already exists\n";
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE products ADD COLUMN $col INT NULL");
        echo "  - Added $col\n";
    }
}

// Step 3: Add missing columns to orders table
echo "\nStep 3: Checking orders table columns...\n";
$orderColumns = [
    'locker_id' => 'VARCHAR(32)',
    'locker_name' => 'VARCHAR(255)',
    'shipping_type' => 'VARCHAR(32)',
    'tracking_number' => 'VARCHAR(64)'
];
foreach ($orderColumns as $col => $type) {
    try {
        $pdo->query("SELECT $col FROM orders LIMIT 1");
        echo "  - $col already exists\n";
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN $col $type NULL");
        echo "  - Added $col\n";
    }
}

echo "\n=== Setup Complete ===\n";

// Verify
echo "\nVerifying products (IDs 1-5):\n";
$stmt = $pdo->query("SELECT id, name_en, weight_grams, length_mm, width_mm, height_mm FROM products WHERE id BETWEEN 1 AND 5");
while ($row = $stmt->fetch()) {
    echo "  ID {$row['id']}: {$row['name_en']} - {$row['weight_grams']}g, {$row['length_mm']}x{$row['width_mm']}x{$row['height_mm']}mm\n";
}
