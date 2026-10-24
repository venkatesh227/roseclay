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
    $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
    echo json_encode(['success' => true, 'message' => 'Quantity updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
}
?>
