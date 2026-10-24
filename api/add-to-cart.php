<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $variation_id = isset($_POST['variation_id']) ? (int)$_POST['variation_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $product = $result->fetch_assoc();
    
    $cart_key = $product_id;
    $price = $product['sale_price'] ?? $product['price'];
    $original_price = $product['price'];
    $stock = $product['stock'];
    $variation_name = null;

    // specific variation logic
    if ($variation_id > 0) {
        $var_stmt = $conn->prepare("SELECT * FROM product_variations WHERE id = ? AND product_id = ? AND status = 1");
        $var_stmt->bind_param("ii", $variation_id, $product_id);
        $var_stmt->execute();
        $var_result = $var_stmt->get_result();

        if ($var_result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Variation not found']);
            exit;
        }

        $variation = $var_result->fetch_assoc();
        $cart_key = $product_id . '_' . $variation_id;
        $price = $variation['sale_price'] ?? $variation['price'];
        $original_price = $variation['price'];
        $stock = $variation['stock'];
        $variation_name = $variation['variation_value'];
    }

    // Check stock
    if ($stock < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add or update cart
    if (isset($_SESSION['cart'][$cart_key])) {
        // Check if adding more would exceed stock
        if (($_SESSION['cart'][$cart_key]['quantity'] + $quantity) > $stock) {
             echo json_encode(['success' => false, 'message' => 'Cannot add more items than available in stock']);
             exit;
        }
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'id' => $product['id'],
            'variation_id' => $variation_id,
            'name' => $product['name'],
            'price' => $price, // Base price (INR)
            'original_price' => $original_price, // Base price (INR)
            'image' => $product['image'],
            'quantity' => $quantity,
            'stock' => $stock,
            'variation_name' => $variation_name
        ];
    }
    
    // Calculate cart totals
    $cart_count = 0;
    $cart_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        if (!is_array($item)) continue; // Safety check
        $cart_count += $item['quantity'];
        $cart_total += $item['price'] * $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => $cart_count,
        'cart_total' => $cart_total
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
