<?php
session_start();
require_once 'config.php';

// Get featured products
$featured_products = $conn->query("SELECT * FROM products WHERE is_featured = 1 AND status = 1 ORDER BY created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'includes/head.php'; ?>
    <title>RoseClay - Handcrafted Clay Products | Natural & Organic</title>
    <meta name="description"
        content="Discover premium handcrafted clay products. From pottery to home decor, experience the beauty of natural, eco-friendly clay craftsmanship.">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <span class="hero-subtitle">Naturally Real. Naturally Clay.</span>
                    <h1 class="hero-title">Taste the Earth, Naturally.</h1>
                    <p class="hero-description">
                       100% Natural Edible Clays, Delivered to Your Door—Anywhere in the World.


                    </p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary">Shop Now</a>
                        <a href="#about" class="btn btn-secondary">Learn More</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="assets/hero_image.png" alt="RoseClay Slide 1" class="hero-slide active">
                    <img src="assets/hs2.png" alt="RoseClay Slide 2" class="hero-slide">
                    <img src="assets/hs3.png" alt="RoseClay Slide 3" class="hero-slide">    
                    <img src="assets/hs4.png" alt="RoseClay Slide 4" class="hero-slide">    
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="assets/about-clay.png" alt="About RoseClay">
                  
                </div>
                <div class="about-text">
                    <span class="section-subtitle">About Us</span>
                    <h2 class="section-title">Crafting Beauty from Earth</h2>
                    <p>
                        At RoseClay, we believe in the timeless beauty of handcrafted clay products. Each piece in our
                        collection is meticulously crafted by skilled artisans who pour their heart and soul into every
                        creation.
                    </p>
                    <p>
                        Our commitment to quality and sustainability ensures that you receive not just a product, but a
                        piece of art that will last for generations.
                    </p>
                    <a href="products.php" class="btn btn-primary">Shop Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Our Products</span>
                <h2 class="section-title">Featured Collection</h2>
                <p>Discover our handpicked selection of premium clay products</p>
            </div>

            <div class="products-grid">
                <?php if ($featured_products->num_rows > 0): ?>
                    <?php while($product = $featured_products->fetch_assoc()): ?>
                    <!-- Product Card -->
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
                                    <button class="btn-icon" onclick="event.preventDefault(); addToWishlist(<?php echo $product['id']; ?>)">
                                        <i class="far fa-heart"></i>
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
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-details">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1/-1; padding: 3rem; color: #999;">
                        No featured products available at the moment.
                    </p>
                <?php endif; ?>
            </div>

            <div class="section-footer">
                <a href="products.php" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Free Shipping</h3>
                        <p>On orders over ₹4,000</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Easy Returns</h3>
                        <p>30-day return policy</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Secure Payment</h3>
                        <p>100% secure transactions</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-content">
                        <h3>24/7 Support</h3>
                        <p>Dedicated customer service</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Subscribe to Our Newsletter</h2>
                    <p>Get updates on new products and exclusive offers</p>
                </div>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
