<?php
/**
 * Feltee Database Migration Script
 * 
 * Run once via browser to reset the database with the new 5-language schema
 * and 20 real products. Delete this file after running.
 * 
 * Usage: https://alis.mygamesonline.org/migrate.php?token=feltee2025migrate
 */

// Security: require a secret token
if (!isset($_GET['token']) || $_GET['token'] !== 'feltee2025migrate') {
    http_response_code(403);
    die('Access denied. Provide ?token=... parameter.');
}

require_once __DIR__ . '/config/config.php';

// Connect to database
try {
    $host = DB_HOST;
    $port = DB_PORT;
    $ip = gethostbyname($host);
    $dsn = "mysql:host=" . $ip . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ));
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Feltee Database Migration ===\n\n";

try {
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "[OK] Foreign key checks disabled\n";
    
    // Drop all existing tables
    $tables = array('order_items', 'orders', 'products', 'categories', 'settings', 'admin_users');
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "[OK] Dropped table: $table\n";
    }
    
    // Read and execute database.sql
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        die("\n[ERROR] database.sql not found!");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    // Remove comments first
    $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
    // Remove multi-line comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split by semicolons (but not inside quotes)
    $statements = array();
    $current = '';
    $inString = false;
    $stringChar = '';
    $escaped = false;
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        
        if ($escaped) {
            $current .= $char;
            $escaped = false;
            continue;
        }
        
        if ($char === '\\') {
            $current .= $char;
            $escaped = true;
            continue;
        }
        
        if ($inString) {
            $current .= $char;
            if ($char === $stringChar) {
                // Check for escaped quote (doubled)
                if ($i + 1 < strlen($sql) && $sql[$i + 1] === $stringChar) {
                    $current .= $sql[$i + 1];
                    $i++;
                } else {
                    $inString = false;
                }
            }
            continue;
        }
        
        if ($char === "'" || $char === '"') {
            $inString = true;
            $stringChar = $char;
            $current .= $char;
            continue;
        }
        
        if ($char === ';') {
            $stmt = trim($current);
            if (!empty($stmt)) {
                $statements[] = $stmt;
            }
            $current = '';
            continue;
        }
        
        $current .= $char;
    }
    
    // Don't forget the last statement if it doesn't end with ;
    $stmt = trim($current);
    if (!empty($stmt)) {
        $statements[] = $stmt;
    }
    
    echo "\n[INFO] Found " . count($statements) . " SQL statements to execute\n\n";
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $index => $stmt) {
        // Skip empty or whitespace-only statements
        if (empty(trim($stmt))) continue;
        
        try {
            $pdo->exec($stmt);
            $success++;
            
            // Show progress for important statements
            if (stripos($stmt, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`(\w+)`/i', $stmt, $m);
                echo "[OK] Created table: " . ($m[1] ?? 'unknown') . "\n";
            } elseif (stripos($stmt, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO\s+`(\w+)`/i', $stmt, $m);
                echo "[OK] Inserted into: " . ($m[1] ?? 'unknown') . "\n";
            } else {
                echo "[OK] Statement #" . ($index + 1) . " executed\n";
            }
        } catch (PDOException $e) {
            $errors++;
            echo "[ERROR] Statement #" . ($index + 1) . ": " . $e->getMessage() . "\n";
            echo "  SQL: " . substr($stmt, 0, 100) . "...\n";
        }
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n=== Migration Complete ===\n";
    echo "Successful: $success\n";
    echo "Errors: $errors\n";
    
    // Verify by counting records
    echo "\n=== Verification ===\n";
    $tables_check = array('categories', 'products', 'settings', 'admin_users');
    foreach ($tables_check as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "$table: $count rows\n";
        } catch (PDOException $e) {
            echo "$table: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n[IMPORTANT] Delete this file after verifying the migration!\n";
    echo "DELETE migrate.php from the server for security.\n";
    
} catch (Exception $e) {
    echo "\n[FATAL ERROR] " . $e->getMessage() . "\n";
}
