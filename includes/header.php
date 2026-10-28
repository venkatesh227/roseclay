<?php
$visitorCurrency = defined('VISITOR_CURRENCY') ? VISITOR_CURRENCY : (isset($_SESSION['currency']) ? strtoupper($_SESSION['currency']) : (defined('DEFAULT_CURRENCY') ? DEFAULT_CURRENCY : 'INR'));
$visitorCurrency = in_array($visitorCurrency, ['INR', 'USD'], true) ? $visitorCurrency : 'INR';
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-content">
            <div class="top-bar-left">
                <span><i class="fas fa-envelope"></i> info@roseclay.com</span>
                <span><i class="fas fa-map-marker-alt"></i> Kothapalle Village, Andhra Pradesh</span>
            </div>
            <div class="top-bar-right">
                <div class="currency-selector">
                    <i class="fas fa-globe"></i>
                    <select id="currencySelect" class="currency-dropdown" title="Choose your display currency">
                        <option value="INR" <?php echo $visitorCurrency === 'INR' ? 'selected' : ''; ?>>₹ INR</option>
                        <option value="USD" <?php echo $visitorCurrency === 'USD' ? 'selected' : ''; ?>>$ USD</option>
                    </select>
                    <script>
                        window.visitorCurrency = <?php echo json_encode($visitorCurrency); ?>;
                    </script>
                </div>

                <div class="social-links">
                    <a href="https://www.facebook.com/RoseclayIndia" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/roseclayindia/" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://in.pinterest.com/pin/308567011978693859/" target="_blank"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header">
    <div class="container">
        <div class="header-content">
           <div class="logo">
                <a href="index.php"><img src="assets/logo.png" alt="RoseClay Logo"></a>
            </div>
            <nav class="nav-menu" id="navMenu">
                <ul>
                    <li><a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>Home</a></li>
                    <li><a href="products.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product-details.php') ? 'class="active"' : ''; ?>>All Products</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="faqs.php">FAQ's</a></li>
                    <li><a href="contact.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'class="active"' : ''; ?>>Contact Us</a></li>
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <li><a href="my-account.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'my-account.php' || basename($_SERVER['PHP_SELF']) == 'my-profile.php' || basename($_SERVER['PHP_SELF']) == 'my-orders.php') ? 'class="active"' : ''; ?>>My Account</a></li>
                    <?php else: ?>
                    <li><a href="login.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php' || basename($_SERVER['PHP_SELF']) == 'register.php') ? 'class="active"' : ''; ?>>Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="header-actions">
                <form action="products.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <button class="icon-btn wishlist-btn" onclick="window.location.href='wishlist.php'">
                    <i class="far fa-heart"></i>
                    <span class="badge">0</span>
                </button>
                <button class="icon-btn cart-btn" onclick="window.location.href='cart.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge">0</span>
                </button>
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <a class="icon-btn logout-btn" href="logout.php" title="Logout" aria-label="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                <?php endif; ?>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>
