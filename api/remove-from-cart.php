<?php
/**
 * Remove from Cart API
 */
session_start();
require_once '../config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['cartKey'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$cart_key = sanitize($input['cartKey']);

if (isset($_SESSION['cart'][$cart_key])) {
    unset($_SESSION['cart'][$cart_key]);
    echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
}
?>
