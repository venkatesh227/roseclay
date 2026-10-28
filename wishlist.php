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
                    <?php while ($product = $wishlist_items->fetch_assoc()): ?>
                        <div class="product-card" data-wishlist-product-id="<?php echo (int)$product['id']; ?>">
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
                                        <button class="btn-icon" onclick="event.preventDefault(); clearWishlistQty(<?php echo $product['id']; ?>); addToWishlist(<?php echo $product['id']; ?>); this.closest('.product-card').remove();" title="Remove from Wishlist" style="background: #e74c3c; color: #fff;">
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

                                <div class="wishlist-quantity">
                                    <label for="qty-<?php echo $product['id']; ?>">Quantity:</label>

                                    <div class="quantity-control">
                                        <button type="button"
                                            onclick="changeWishlistQty(<?php echo $product['id']; ?>, -1)">
                                            −
                                        </button>

                                        <input type="number"
                                            id="qty-<?php echo $product['id']; ?>"
                                            class="wishlist-qty"
                                            value="1"
                                            min="1"
                                            step="1">

                                        <button type="button"
                                            onclick="changeWishlistQty(<?php echo $product['id']; ?>, 1)">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <button class="btn btn-cart"
                                    onclick="addWishlistProductToCart(<?php echo (int)$product['id']; ?>)">
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



<script>
function saveWishlistQty(productId, quantity) {
    localStorage.setItem('wishlist_qty_' + productId, String(quantity));
}

function clearWishlistQty(productId) {
    localStorage.removeItem('wishlist_qty_' + productId);
}

function changeWishlistQty(productId, change) {
    const input = document.getElementById('qty-' + productId);
    let quantity = parseInt(input.value, 10) || 1;

    quantity = Math.max(1, quantity + change);
    input.value = quantity;
    saveWishlistQty(productId, quantity);
}

function addWishlistProductToCart(productId) {
    const input = document.getElementById('qty-' + productId);
    const quantity = parseInt(input.value, 10);
    const productCard = document.querySelector(`[data-wishlist-product-id="${productId}"]`);
    const addButton = productCard?.querySelector('.btn-cart');

    if (!Number.isInteger(quantity) || quantity < 1) {
        alert('Please enter a valid quantity.');
        return;
    }

    if (addButton) addButton.disabled = true;

    fetch('api/add-to-cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            product_id: productId,
            quantity: quantity,
            variation_id: 0,
            replace_quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Unable to add this product to cart.', 'error');
            return;
        }

        return fetch('api/wishlist-toggle.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({product_id: productId, action: 'remove'})
        })
        .then(response => response.json())
        .then(removeData => {
            if (!removeData.success) {
                showToast('Added to cart, but could not remove this item from your wishlist.', 'error');
                return;
            }

            clearWishlistQty(productId);
            productCard?.remove();

            const count = document.querySelector('.section-header p');
            if (count) {
                const remaining = document.querySelectorAll('[data-wishlist-product-id]').length;
                count.textContent = `${remaining} items saved`;
                if (remaining === 0) location.reload();
            }

            if (typeof updateCartCount === 'function') {
                updateCartCount(data.cart_count);
            }
            showToast('Added to cart and removed from wishlist.', 'success');
        });
    })
    .catch(error => {
        console.error('Wishlist add-to-cart error:', error);
        showToast('Unable to add this product to cart. Please try again.', 'error');
    })
    .finally(() => {
        if (addButton) addButton.disabled = false;
    });
}

document.querySelectorAll('.wishlist-qty').forEach(function (input) {
    const productId = input.id.replace('qty-', '');
    const savedQuantity = parseInt(localStorage.getItem('wishlist_qty_' + productId), 10);

    if (Number.isInteger(savedQuantity) && savedQuantity >= 1) {
        input.value = savedQuantity;
    }

    input.addEventListener('input', function () {
        const quantity = parseInt(input.value, 10);
        if (Number.isInteger(quantity) && quantity >= 1) {
            saveWishlistQty(productId, quantity);
        }
    });

    input.addEventListener('change', function () {
        const quantity = parseInt(input.value, 10);
        if (Number.isInteger(quantity) && quantity >= 1) {
            input.value = quantity;
            saveWishlistQty(productId, quantity);
        } else {
            input.value = 1;
            saveWishlistQty(productId, 1);
        }
    });
});
</script>


<style>
    .wishlist-quantity {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin: 12px 0;
        font-size: 13px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid #f58ab0;
        border-radius: 6px;
        overflow: hidden;
    }

    .quantity-control button {
        width: 32px;
        height: 32px;
        border: none;
        background: #fff0f5;
        color: #333;
        font-size: 18px;
        cursor: pointer;
    }

    .quantity-control input {
        width: 42px;
        height: 32px;
        padding: 0;
        text-align: center;
        border: none;
        outline: none;
        font-size: 14px;
    }

    .product-card .btn-cart {
        width: 100%;
    }
</style>