<?php
/**
 * Cart Sync API
 * Syncs localStorage cart data to PHP session
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Get JSON data from request
$input = file_get_contents('php://input');
$cart_data = json_decode($input, true);

if (!$cart_data || !is_array($cart_data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
    exit;
}

// Initialize session cart
$_SESSION['cart'] = [];

foreach ($cart_data as $item) {
    if (!isset($item['productId']) || !isset($item['variationId'])) {
        continue; // Skip invalid items
    }
    
    $product_id = (int)$item['productId'];
    $variation_id = (int)$item['variationId'];
    $quantity = (int)($item['quantity'] ?? 1);
    
    // Fetch product details
    $product_query = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
    $product_query->bind_param("i", $product_id);
    $product_query->execute();
    $product = $product_query->get_result()->fetch_assoc();
    
    if (!$product) {
        continue; // Skip if product not found
    }
    
    // Fetch variation details
    $variation_query = $conn->prepare("SELECT * FROM product_variations WHERE id = ? AND product_id = ? AND status = 1");
    $variation_query->bind_param("ii", $variation_id, $product_id);
    $variation_query->execute();
    $variation = $variation_query->get_result()->fetch_assoc();
    
    if (!$variation) {
        continue; // Skip if variation not found
    }
    
    // Create unique cart key (product_id + variation_id)
    $cart_key = $product_id . '_' . $variation_id;
    
    // Add to session cart
    $_SESSION['cart'][$cart_key] = [
        'product_id' => $product_id,
        'variation_id' => $variation_id,
        'name' => $product['name'],
        'variation_name' => $variation['variation_name'],
        'price' => $variation['sale_price'] ?? $variation['price'],
        'original_price' => $variation['price'],
        'image' => $product['image'],
        'quantity' => $quantity,
        'stock' => $variation['stock']
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Cart synced successfully',
    'cart_count' => count($_SESSION['cart'])
]);
?>
