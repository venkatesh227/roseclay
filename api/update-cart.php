<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    
    if (!isset($_SESSION['cart'][$product_id])) {
        echo json_encode(['success' => false, 'message' => 'Product not in cart']);
        exit;
    }
    
    if ($quantity <= 0) {
        // Remove from cart
        unset($_SESSION['cart'][$product_id]);
    } else {
        // Check stock
        if ($quantity > $_SESSION['cart'][$product_id]['stock']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Update quantity
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
    
    // Calculate cart totals
    $cart_count = 0;
    $cart_subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $cart_subtotal += $item['price'] * $item['quantity'];
    }
    
    $tax = $cart_subtotal * 0.10;
    $cart_total = $cart_subtotal + $tax;
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cart_count,
        'cart_subtotal' => $cart_subtotal,
        'tax' => $tax,
        'cart_total' => $cart_total
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
