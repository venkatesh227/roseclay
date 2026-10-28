<?php
/**
 * Update Cart Quantity API
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['cartKey']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$cart_key = sanitize($input['cartKey']);
$quantity = (int)$input['quantity'];

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
    exit;
}

if (isset($_SESSION['cart'][$cart_key])) {
    $item = &$_SESSION['cart'][$cart_key];
    if (isset($item['stock']) && $quantity > (int)$item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Cannot add more items than available in stock']);
        exit;
    }

    $item['quantity'] = $quantity;
    $item_total = (float)$item['price'] * $quantity;
    $cart_count = 0;
    $cart_subtotal = 0;
    foreach ($_SESSION['cart'] as $cart_item) {
        if (!is_array($cart_item) || !isset($cart_item['quantity'], $cart_item['price'])) {
            continue;
        }
        $cart_count += (int)$cart_item['quantity'];
        $cart_subtotal += (float)$cart_item['price'] * (int)$cart_item['quantity'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Quantity updated',
        'quantity' => $quantity,
        'item_total' => $item_total,
        'item_total_formatted' => formatVisitorPrice($item_total),
        'cart_count' => $cart_count,
        'cart_subtotal' => $cart_subtotal,
        'cart_subtotal_formatted' => formatVisitorPrice($cart_subtotal),
        'cart_total' => $cart_subtotal,
        'cart_total_formatted' => formatVisitorPrice($cart_subtotal)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
}
?>
