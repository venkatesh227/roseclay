<?php
session_start();
require_once '../config.php';
// Check admin login
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

$page_title = 'Manage Reviews';

// Handle Actions
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            setMessage('Review deleted successfully', 'success');
        } else {
            setMessage('Error deleting review', 'danger');
        }
    } elseif ($_POST['action'] == 'toggle_status') {
        $id = (int)$_POST['id'];
        $current_status = (int)$_POST['current_status'];
        $new_status = $current_status == 1 ? 0 : 1;
        
        $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        if ($stmt->execute()) {
            // Update product average rating
            $product_id = (int)$_POST['product_id'];
            $avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = $product_id AND status = 1";
            $avg_result = $conn->query($avg_query)->fetch_assoc();
            
            $new_rating = round($avg_result['avg_rating'] ?? 0, 1);
            $new_count = $avg_result['count'] ?? 0;
            
            $update_product = $conn->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
            $update_product->bind_param("dii", $new_rating, $new_count, $product_id);
            $update_product->execute();

            setMessage('Review status updated successfully', 'success');
        } else {
            setMessage('Error updating status', 'danger');
        }
    }
}

// Fetch Reviews
$reviews_query = "SELECT r.*, p.name as product_name, p.image as product_image, u.first_name, u.last_name, u.email 
                  FROM reviews r 
                  JOIN products p ON r.product_id = p.id 
                  LEFT JOIN users u ON r.user_id = u.id 
                  ORDER BY r.created_at DESC";
$reviews = $conn->query($reviews_query);

include 'includes/header.php';
?>

<div class="admin-card">
    <div class="card-header">
        <h3>All Reviews</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reviews->num_rows > 0): ?>
                        <?php while($review = $reviews->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $review['id']; ?></td>
                            <td>
                                <div class="product-cell">
                                    <img src="../uploads/products/<?php echo $review['product_image']; ?>" alt="Product" class="product-thumb">
                                    <span><?php echo $review['product_name']; ?></span>
                                </div>
                            </td>
                            <td>
                                <div><?php echo $review['first_name'] . ' ' . $review['last_name']; ?></div>
                                <small class="text-muted"><?php echo $review['email']; ?></small>
                            </td>
                            <td>
                                <div class="star-rating" style="color: #ffc107;">
                                    <?php 
                                    for($i=1; $i<=5; $i++) {
                                        if($i <= $review['rating']) echo '<i class="fas fa-star"></i>';
                                        else echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($review['title']); ?></strong>
                                <p><?php echo nl2br(htmlspecialchars(substr($review['comment'], 0, 100))) . (strlen($review['comment']) > 100 ? '...' : ''); ?></p>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $review['status'] == 1 ? 'success' : 'danger'; ?>">
                                    <?php echo $review['status'] == 1 ? 'Visible' : 'Hidden'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $review['status']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $review['product_id']; ?>">
                                        <button type="submit" class="btn-icon btn-sm" title="<?php echo $review['status'] == 1 ? 'Hide' : 'Show'; ?>">
                                            <i class="fas fa-<?php echo $review['status'] == 1 ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" class="btn-icon btn-sm btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No reviews found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
