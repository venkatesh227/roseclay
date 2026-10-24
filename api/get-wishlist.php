<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'wishlist' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$wishlist_ids = [];
while ($row = $result->fetch_assoc()) {
    $wishlist_ids[] = (int)$row['product_id'];
}

echo json_encode(['success' => true, 'wishlist' => $wishlist_ids]);
?>
