<?php
session_start();
require_once 'config.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id == 0) {
    header('Location: products.php');
    exit;
}

// Fetch product details
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product = $product_query->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Fetch product variations
$variations = $conn->query("SELECT * FROM product_variations WHERE product_id = $product_id AND status = 1 ORDER BY price ASC");
$has_variations = $variations->num_rows > 0;

// Fetch related products (same category)
$related_products = $conn->query("SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != $product_id AND status = 1 LIMIT 4");

// Fetch reviews
$reviews_query = $conn->query("SELECT r.*, u.first_name, u.last_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = $product_id AND r.status = 1 ORDER BY r.created_at DESC");
$reviews_count = $reviews_query->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title><?php echo $product['name']; ?> - RoseClay</title>
    <meta name="description" content="<?php echo substr($product['description'], 0, 160); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="products.php">Products</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo $product['name']; ?></span>
        </div>
    </div>

    <!-- Product Details Section -->
    <section class="product-details-section">
        <div class="container">
            <div class="product-details-layout">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <img src="uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" id="mainImage">
                        <?php if ($product['sale_price']): ?>
                        <div class="product-badge sale">
                            -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                        </div>
                        <?php elseif ($product['is_new']): ?>
                        <div class="product-badge new">New</div>
                        <?php endif; ?>
                    </div>
                    <?php 
                    $gallery = !empty($product['gallery']) ? json_decode($product['gallery'], true) : [];
                    // Add main image to gallery for thumbnails if not already there (optional, but good UX to have all in strip)
                    // Let's just prepend main image to gallery list for display purposes
                    $all_images = [];
                    if ($product['image']) $all_images[] = $product['image'];
                    if (is_array($gallery)) {
                        $all_images = array_merge($all_images, $gallery);
                    }
                    $all_images = array_unique($all_images);
                    
                    if (count($all_images) > 1):
                    ?>
                    <div class="product-thumbnails" style="display: flex; gap: 10px; margin-top: 10px; overflow-x: auto;">
                        <?php foreach($all_images as $img): ?>
                        <div class="thumbnail" onclick="changeMainImage('uploads/products/<?php echo $img; ?>')" style="cursor: pointer; width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                            <img src="uploads/products/<?php echo $img; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-details-info">
                    <h1 class="product-title"><?php echo $product['name']; ?></h1>
                    
                    <!-- Rating -->
                    <div class="product-rating-large">
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
                        <span class="rating-text">(<?php echo number_format($rating, 1); ?> rating)</span>
                    </div>

                    <!-- Price -->
                    <div class="product-price-large" id="productPrice">
                        <?php if ($has_variations):
                            // Get default variation
                            $variations->data_seek(0);
                            $default_var = null;
                            while($v = $variations->fetch_assoc()) {
                                if ($v['is_default'] == 1) {
                                    $default_var = $v;
                                    break;
                                }
                            }
                            if (!$default_var) {
                                $variations->data_seek(0);
                                $default_var = $variations->fetch_assoc();
                            }
                            $display_price = $default_var['sale_price'] ?? $default_var['price'];
                            $original_price = $default_var['price'];
                        ?>
                            <?php if ($default_var['sale_price']): ?>
                            <span class="price-old-large" data-base-price="<?php echo $original_price; ?>"><?php echo formatPrice($original_price); ?></span>
                            <span class="price-new-large" data-base-price="<?php echo $display_price; ?>"><?php echo formatPrice($display_price); ?></span>
                            <?php else: ?>
                            <span class="price-new-large" data-base-price="<?php echo $display_price; ?>"><?php echo formatPrice($display_price); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($product['sale_price']): ?>
                            <span class="price-old-large" data-base-price="<?php echo $product['price']; ?>"><?php echo formatPrice($product['price']); ?></span>
                            <span class="price-new-large" data-base-price="<?php echo $product['sale_price']; ?>"><?php echo formatPrice($product['sale_price']); ?></span>
                            <?php else: ?>
                            <span class="price-new-large" data-base-price="<?php echo $product['price']; ?>"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description -->
                    <div class="product-short-desc">
                        <p><?php echo nl2br(substr($product['description'], 0, 200)); ?>...</p>
                    </div>

                    <?php if ($has_variations): ?>
                    <!-- Variations Section -->
                    <div class="product-variations-section">
                        <h3>Select Size:</h3>
                        <div class="variations-options">
                            <?php 
                            $variations->data_seek(0);
                            while($variation = $variations->fetch_assoc()): 
                                $var_price = $variation['sale_price'] ?? $variation['price'];
                                $is_default = $variation['is_default'] == 1;
                                $is_out_of_stock = $variation['stock'] == 0;
                            ?>
                            <div class="variation-option <?php echo $is_default ? 'active' : ''; ?> <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>"
                                 data-variation-id="<?php echo $variation['id']; ?>"
                                 data-price="<?php echo $variation['price']; ?>"
                                 data-sale-price="<?php echo $variation['sale_price']; ?>"
                                 data-stock="<?php echo $variation['stock']; ?>"
                                 onclick="selectVariation(this)">
                                <div class="variation-name"><?php echo $variation['variation_value']; ?></div>
                                <div class="variation-price" data-base-price="<?php echo $var_price; ?>"><?php echo formatPrice($var_price); ?></div>
                                <?php if ($is_out_of_stock): ?>
                                <div class="variation-stock-badge">Out of Stock</div>
                                <?php elseif ($variation['stock'] < 10): ?>
                                <div class="variation-stock-badge low">Only <?php echo $variation['stock']; ?> left</div>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quantity -->
                    <div class="product-quantity-section">
                        <h3>Quantity:</h3>
                        <div class="quantity-selector">
                            <button class="qty-btn" onclick="decreaseQuantity()">-</button>
                            <input type="number" id="quantity" value="1" min="1" readonly>
                            <button class="qty-btn" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>

                    <!-- Add to Cart Button -->
                    <div class="product-actions">
                        <button class="btn btn-primary btn-large" id="addToCartBtn" onclick="handleAddToCart()">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn btn-secondary btn-icon-only" onclick="addToWishlist(<?php echo $product_id; ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>

                    <!-- Product Meta -->
                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">SKU:</span>
                            <span class="meta-value"><?php echo $product['sku']; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value"><?php 
                                $cat = $conn->query("SELECT name FROM categories WHERE id = {$product['category_id']}")->fetch_assoc();
                                echo $cat['name'];
                            ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Stock:</span>
                            <span class="meta-value" id="stockStatus">
                                <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Tabs -->
            <div class="product-tabs">
                <div class="tabs-nav">
                    <button class="tab-btn active" onclick="openTab('description')">Description</button>
                    <button class="tab-btn" onclick="openTab('additional')">Additional Information</button>
                    <button class="tab-btn" onclick="openTab('reviews')">Reviews (<?php echo $reviews_count; ?>)</button>
                </div>

                <div class="tabs-content">
                    <div id="description" class="tab-pane active">
                        <h3>Product Description</h3>
                        <p><?php echo nl2br($product['description']); ?></p>
                    </div>

                    <div id="additional" class="tab-pane">
                        <h3>Additional Information</h3>
                        <table class="info-table">
                            <tr>
                                <th>Weight</th>
                                <td><?php echo $has_variations ? 'Multiple options available' : 'Standard'; ?></td>
                            </tr>
                            <tr>
                                <th>Material</th>
                                <td>Handcrafted Clay</td>
                            </tr>
                            <tr>
                                <th>Care Instructions</th>
                                <td>Hand wash recommended</td>
                            </tr>
                        </table>
                    </div>

                    <div id="reviews" class="tab-pane">
                        <div class="reviews-container">
                            <div class="reviews-list">
                                <h3>Customer Reviews (<?php echo $reviews_count; ?>)</h3>
                                <?php if ($reviews_count > 0): ?>
                                    <?php while($review = $reviews_query->fetch_assoc()): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="review-stars">
                                                <?php for($i=1; $i<=5; $i++) echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                                            </div>
                                            <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                        <h4 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h4>
                                        <p class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        <div class="review-author">
                                            - <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="review-form-container">
                                <h3>Write a Review</h3>
                                <?php if (isLoggedIn()): ?>
                                <form id="reviewForm" onsubmit="submitReview(event)">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <div class="form-group">
                                        <label>Rating</label>
                                        <div class="star-rating-input">
                                            <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="title">Review Title</label>
                                        <input type="text" id="title" name="title" required class="form-control" placeholder="Example: Great product!">
                                    </div>
                                    <div class="form-group">
                                        <label for="comment">Your Review</label>
                                        <textarea id="comment" name="comment" required class="form-control" rows="4" placeholder="Tell us what you liked or disliked..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                                <?php else: ?>
                                <div class="login-to-review">
                                    <p>Please <a href="login.php?redirect=product-details.php?id=<?php echo $product_id; ?>">login</a> to write a review.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if ($related_products->num_rows > 0): ?>
            <div class="related-products">
                <h2>Related Products</h2>
                <div class="products-grid">
                    <?php while($related = $related_products->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php if ($related['sale_price']): ?>
                        <div class="product-badge sale">
                            -<?php echo round((($related['price'] - $related['sale_price']) / $related['price']) * 100); ?>%
                        </div>
                        <?php endif; ?>
                        <a href="product-details.php?id=<?php echo $related['id']; ?>">
                            <div class="product-image">
                                <img src="uploads/products/<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>">
                            </div>
                        </a>
                        <div class="product-info">
                            <h3><a href="product-details.php?id=<?php echo $related['id']; ?>"><?php echo $related['name']; ?></a></h3>
                            <div class="product-price">
                                <?php if ($related['sale_price']): ?>
                                <span class="price-old" data-base-price="<?php echo $related['price']; ?>"><?php echo formatPrice($related['price']); ?></span>
                                <span class="price-new" data-base-price="<?php echo $related['sale_price']; ?>"><?php echo formatPrice($related['sale_price']); ?></span>
                                <?php else: ?>
                                <span class="price-new" data-base-price="<?php echo $related['price']; ?>"><?php echo formatPrice($related['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <?php include 'includes/scripts.php'; ?>
    <script>
        let selectedVariationId = <?php echo $has_variations ? ($default_var['id'] ?? 'null') : 'null'; ?>;
        let currentStock = <?php echo $has_variations ? ($default_var['stock'] ?? $product['stock']) : $product['stock']; ?>;

        // Select variation
        function selectVariation(element) {
            // Remove active class from all
            document.querySelectorAll('.variation-option').forEach(opt => opt.classList.remove('active'));
            
            // Add active to selected
            element.classList.add('active');
            
            // Get variation data
            selectedVariationId = element.dataset.variationId;
            const price = parseFloat(element.dataset.price);
            const salePrice = element.dataset.salePrice ? parseFloat(element.dataset.salePrice) : null;
            const stock = parseInt(element.dataset.stock);
            currentStock = stock;
            
            // Update price using global currency functions
            const priceContainer = document.getElementById('productPrice');
            
            // Ensure we use the current currency
            const currency = window.currentCurrency || 'INR';

            if (salePrice) {
                const convertedPrice = convertPrice(price, currency);
                const convertedSalePrice = convertPrice(salePrice, currency);
                
                priceContainer.innerHTML = `
                    <span class="price-old-large" data-base-price="${price}">${formatPrice(convertedPrice, currency)}</span>
                    <span class="price-new-large" data-base-price="${salePrice}">${formatPrice(convertedSalePrice, currency)}</span>
                `;
            } else {
                const convertedPrice = convertPrice(price, currency);
                
                priceContainer.innerHTML = `<span class="price-new-large" data-base-price="${price}">${formatPrice(convertedPrice, currency)}</span>`;
            }
            
            // Update stock status
            const stockStatus = document.getElementById('stockStatus');
            if (stock > 0) {
                stockStatus.textContent = 'In Stock';
                stockStatus.style.color = '#27ae60';
                document.getElementById('addToCartBtn').disabled = false;
            } else {
                stockStatus.textContent = 'Out of Stock';
                stockStatus.style.color = '#e74c3c';
                document.getElementById('addToCartBtn').disabled = true;
            }
        }

        // Quantity controls
        function increaseQuantity() {
            const qtyInput = document.getElementById('quantity');
            let qty = parseInt(qtyInput.value);
            if (qty < currentStock) {
                qtyInput.value = qty + 1;
            }
        }

        function decreaseQuantity() {
            const qtyInput = document.getElementById('quantity');
            let qty = parseInt(qtyInput.value);
            if (qty > 1) {
                qtyInput.value = qty - 1;
            }
        }

        // Handle Add to cart
        function handleAddToCart() {
            const quantity = parseInt(document.getElementById('quantity').value);
            const productId = <?php echo $product_id; ?>;
            let variationId = 0;
            
            <?php if ($has_variations): ?>
            if (!selectedVariationId) {
                alert('Please select a size');
                return;
            }
            variationId = selectedVariationId;
            <?php endif; ?>

            // Call global cart function
            addToCart(productId, quantity, variationId);
        }

        // Tab functionality
        function openTab(tabName) {
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }



        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        function submitReview(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            // Validate rating
            if (!formData.get('rating')) {
                alert('Please select a rating');
                return;
            }

            fetch('api/submit-review.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>
