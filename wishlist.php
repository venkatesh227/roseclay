<?php
session_start();
require_once 'config.php';

// Require login
if (!isLoggedIn()) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch wishlist items
$query = "SELECT p.*, w.created_at as added_at 
          FROM wishlist w 
          JOIN products p ON w.product_id = p.id 
          WHERE w.user_id = ? AND p.status = 1 
          ORDER BY w.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_items = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>My Wishlist - RoseClay</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>My Wishlist</span>
        </div>
    </div>

    <section class="wishlist-section" style="padding: 4rem 0;">
        <div class="container">
            <div class="section-header">
                <h1>My Wishlist</h1>
                <p><?php echo $wishlist_items->num_rows; ?> items saved</p>
            </div>

            <?php if ($wishlist_items->num_rows > 0): ?>
            <div class="products-grid">
                <?php while($product = $wishlist_items->fetch_assoc()): ?>
                <div class="product-card">
                    <?php if ($product['sale_price']): ?>
                    <div class="product-badge sale">
                        -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                    </div>
                    <?php elseif ($product['is_new']): ?>
                    <div class="product-badge new">New</div>
                    <?php endif; ?>
                    
                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="product-link">
                        <div class="product-image">
                            <img src="uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <!-- Remove from wishlist button -->
                                <button class="btn-icon" onclick="event.preventDefault(); addToWishlist(<?php echo $product['id']; ?>); this.closest('.product-card').remove();" title="Remove from Wishlist" style="background: #e74c3c; color: #fff;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </a>
                    <div class="product-info">
                        <h3><a href="product-details.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></h3>
                        <div class="product-rating">
                            <?php
                            $rating = $product['rating'];
                            $full_stars = floor($rating);
                            $half_star = ($rating - $full_stars) >= 0.5;
                            
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<i class="fas fa-star"></i>';
                            }
                            if ($half_star) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                                $full_stars++;
                            }
                            for ($i = $full_stars; $i < 5; $i++) {
                                echo '<i class="far fa-star"></i>';
                            }
                            ?>
                            <span>(<?php echo number_format($rating, 1); ?>)</span>
                        </div>
                        <div class="product-price">
                            <?php if ($product['sale_price']): ?>
                            <span class="price-old" data-base-price="<?php echo $product['price']; ?>"><?php echo formatPrice($product['price']); ?></span>
                            <span class="price-new" data-base-price="<?php echo $product['sale_price']; ?>"><?php echo formatPrice($product['sale_price']); ?></span>
                            <?php else: ?>
                            <span class="price-new" data-base-price="<?php echo $product['price']; ?>"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state" style="text-align: center; padding: 4rem;">
                <i class="far fa-heart" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                <h3>Your wishlist is empty</h3>
                <p>Browse our products and save your favorites!</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">View Products</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
