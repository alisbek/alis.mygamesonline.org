<?php
/**
 * PayU Migration Script
 * 
 * Alters the orders table to add PayU-related columns.
 * Run this once on the live server, then DELETE this file.
 * 
 * Access: https://alis.mygamesonline.org/migrate-payu.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

echo "<h1>PayU Database Migration</h1>\n";
echo "<pre>\n";

$queries = [
    // 1. Extend payment_method ENUM to include 'payu'
    "ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash','bank_transfer','payu') NOT NULL",
    
    // 2. Add payment_status column
    "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending' AFTER payment_method",
    
    // 3. Add payu_order_id column
    "ALTER TABLE orders ADD COLUMN payu_order_id VARCHAR(255) DEFAULT NULL AFTER payment_status",
    
    // 4. Add transaction_id column
    "ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(255) DEFAULT NULL AFTER payu_order_id",
];

foreach ($queries as $i => $sql) {
    $num = $i + 1;
    echo "Query $num: $sql\n";
    try {
        $pdo->exec($sql);
        echo "  -> SUCCESS\n\n";
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Check if it's a "duplicate column" error (already migrated)
        if (strpos($msg, 'Duplicate column') !== false) {
            echo "  -> SKIPPED (column already exists)\n\n";
        } else {
            echo "  -> ERROR: $msg\n\n";
        }
    }
}

echo "\n=== Migration Complete ===\n";
echo "\nIMPORTANT: Delete this file from the server now!\n";
echo "</pre>\n";
