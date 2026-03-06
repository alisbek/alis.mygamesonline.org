<?php
/**
 * Cart API endpoint
 * Handles add, remove, and update cart actions via AJAX POST
 * Returns JSON responses
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $size = trim($_POST['size'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product']);
            exit;
        }
        
        // Verify product exists and has stock
        $stmt = $pdo->prepare("SELECT id, stock, price, image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit;
        }
        
        if ($product['stock'] <= 0) {
            echo json_encode(['success' => false, 'error' => 'Out of stock']);
            exit;
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if same product+size+color already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId && $item['size'] === $size && $item['color'] === $color) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        unset($item);
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $productId,
                'size' => $size,
                'color' => $color,
                'quantity' => $quantity,
                'price' => (float)$product['price'],
                'image' => $product['image']
            ];
        }
        
        $totalCount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalCount += $item['quantity'];
        }
        
        echo json_encode(['success' => true, 'cart_count' => $totalCount]);
        break;
        
    case 'remove':
        $index = (int)($_POST['index'] ?? -1);
        
        if (isset($_SESSION['cart'][$index])) {
            array_splice($_SESSION['cart'], $index, 1);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
        }
        break;
        
    case 'update':
        $index = (int)($_POST['index'] ?? -1);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        
        if (isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = $quantity;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
        }
        break;
        
    case 'count':
        $totalCount = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $totalCount += $item['quantity'];
            }
        }
        echo json_encode(['success' => true, 'cart_count' => $totalCount]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
