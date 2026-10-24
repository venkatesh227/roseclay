<?php
session_start();
require_once 'config.php';

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$where = "WHERE status = 1";

if ($category_filter > 0) {
    $where .= " AND category_id = $category_filter";
}

if ($search) {
    $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

// Sorting
$order_by = "ORDER BY created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "ORDER BY COALESCE(sale_price, price) ASC";
        break;
    case 'price_high':
        $order_by = "ORDER BY COALESCE(sale_price, price) DESC";
        break;
    case 'name':
        $order_by = "ORDER BY name ASC";
        break;
    case 'popular':
        $order_by = "ORDER BY rating DESC, reviews_count DESC";
        break;
}

// Get products
$products = $conn->query("SELECT * FROM products $where $order_by");

// Get categories for filter
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count 
                            FROM categories c 
                            LEFT JOIN products p ON c.id = p.category_id AND p.status = 1
                            WHERE c.status = 1 
                            GROUP BY c.id 
                            ORDER BY c.name");

// Get total count (filtered)
$total_products = $conn->query("SELECT COUNT(*) as count FROM products $where")->fetch_assoc()['count'];

// Get all products count (unfiltered) for sidebar
$all_products_count = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 1")->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - RoseClay</title>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Products</span>
        </div>
    </div>

    <!-- Products Section -->
    <section class="products-page">
        <div class="container">
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="products-sidebar">
                    <div class="filter-section">
                        <h3>Categories</h3>
                        <ul class="filter-list">
                            <li>
                                <a href="products.php" class="<?php echo $category_filter == 0 ? 'active' : ''; ?>">
                                    All Products (<?php echo $all_products_count; ?>)
                                </a>
                            </li>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <li>
                                    <a href="products.php?category=<?php echo $cat['id']; ?>"
                                        class="<?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                                        <?php echo $cat['name']; ?> (<?php echo $cat['product_count']; ?>)
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="products-main">
                    <!-- Toolbar -->
                    <div class="products-toolbar">
                        <div class="toolbar-left">
                            <p>Showing <?php echo $products->num_rows; ?> of <?php echo $total_products; ?> products</p>
                        </div>
                        <div class="toolbar-right">
                            <select onchange="window.location.href='products.php?sort=' + this.value + '<?php echo $category_filter ? '&category=' . $category_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>'">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A-Z</option>
                            </select>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="products-grid">
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <?php
                                $defaultVariation = null;

                                $stmt = $conn->prepare("
    SELECT price, sale_price
    FROM product_variations
    WHERE product_id = ?
      AND is_default = 1
      AND status = 1
    LIMIT 1
");

                                $stmt->bind_param("i", $product['id']);
                                $stmt->execute();

                                $resultVariation = $stmt->get_result();

                                if ($resultVariation->num_rows > 0) {
                                    $defaultVariation = $resultVariation->fetch_assoc();
                                }
                                ?>
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

                                            <?php
                                            if ($defaultVariation) {

                                                $price = $defaultVariation['price'];
                                                $salePrice = $defaultVariation['sale_price'];
                                            } else {

                                                $price = $product['price'];
                                                $salePrice = $product['sale_price'];
                                            }

                                            if (!empty($salePrice) && $salePrice > 0) {
                                            ?>

                                                <span class="price-old"
                                                    data-base-price="<?= $price ?>">
                                                    <?= formatPrice($price) ?>
                                                </span>

                                                <span class="price-new"
                                                    data-base-price="<?= $salePrice ?>">
                                                    <?= formatPrice($salePrice) ?>
                                                </span>

                                            <?php } else { ?>

                                                <span class="price-new"
                                                    data-base-price="<?= $price ?>">
                                                    <?= formatPrice($price) ?>
                                                </span>

                                            <?php } ?>

                                        </div>
                                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-details">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="grid-column: 1/-1; text-align: center; padding: 4rem;">
                                <i class="fas fa-box-open" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                                <h3>No products found</h3>
                                <p>Try adjusting your filters or search terms</p>
                                <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">View All Products</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>

</html>