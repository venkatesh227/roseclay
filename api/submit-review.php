<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
$comment = isset($_POST['comment']) ? sanitize($_POST['comment']) : '';

if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($title) || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please provide all required fields and a valid rating (1-5)']);
    exit;
}

// Check if user has already reviewed this product
$check_stmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $product_id, $user_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
    exit;
}

// Insert review
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, title, comment, status) VALUES (?, ?, ?, ?, ?, 1)");
$stmt->bind_param("iiiss", $product_id, $user_id, $rating, $title, $comment);

if ($stmt->execute()) {
    // Update product average rating
    $avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = $product_id AND status = 1";
    $avg_result = $conn->query($avg_query)->fetch_assoc();
    
    $new_rating = round($avg_result['avg_rating'], 1);
    $new_count = $avg_result['count'];
    
    $update_product = $conn->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
    $update_product->bind_param("dii", $new_rating, $new_count, $product_id);
    $update_product->execute();
    
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting review']);
}
?>
