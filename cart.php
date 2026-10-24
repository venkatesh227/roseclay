<?php
session_start();
require_once 'config.php';

// Get cart items
$cart_items = $_SESSION['cart'] ?? [];

// If cart is empty, show empty state
$is_empty = empty($cart_items);

// Calculate totals
$subtotal = 0;
if (!$is_empty) {
    foreach ($cart_items as $item) {
        if (is_array($item) && isset($item['price'], $item['quantity'])) {
            $subtotal += $item['price'] * $item['quantity'];
        }
    }
}

// Shipping Threshold (Approx $50 USD -> 4000 INR)
$shipping_threshold = 4000;
$tax = $subtotal * 0.10;
$shipping = $subtotal >= $shipping_threshold ? 0 : 100; // Flat 100 INR shipping if not free
$total = $subtotal + $tax + $shipping;

include 'includes/head.php'; // Include head (styles etc)
?>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Shopping Cart</span>
        </div>
    </div>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <?php if ($is_empty): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything to your cart yet</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
            <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-layout">
                <div class="cart-items">
                    <h2>Shopping Cart (<?php echo count($cart_items); ?> items)</h2>
                    
                    <?php foreach ($cart_items as $cart_key => $item): ?>
                    <?php if (!is_array($item)) continue; ?>
                    <div class="cart-item" data-cart-key="<?php echo $cart_key; ?>">
                        <div class="cart-item-image">
                            <img src="uploads/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                        </div>
                        <div class="cart-item-details">
                            <h3><?php echo $item['name']; ?></h3>
                            <?php if (isset($item['variation_name'])): ?>
                            <p class="cart-item-variation">
                                <i class="fas fa-weight"></i> <?php echo $item['variation_name']; ?>
                            </p>
                            <?php endif; ?>
                            <p class="cart-item-price" data-base-price="<?php echo $item['price']; ?>"><?php echo formatPrice($item['price']); ?></p>
                            <?php if (isset($item['original_price']) && $item['original_price'] > $item['price']): ?>
                            <p class="cart-item-save">You save: <span data-base-price="<?php echo $item['original_price'] - $item['price']; ?>"><?php echo formatPrice($item['original_price'] - $item['price']); ?></span></p>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-quantity">
                            <button class="qty-btn" onclick="updateQuantity('<?php echo $cart_key; ?>', <?php echo $item['quantity'] - 1; ?>)">-</button>
                            <input type="number" value="<?php echo $item['quantity']; ?>" readonly>
                            <button class="qty-btn" onclick="updateQuantity('<?php echo $cart_key; ?>', <?php echo $item['quantity'] + 1; ?>)">+</button>
                        </div>
                        <div class="cart-item-total" data-base-price="<?php echo $item['price'] * $item['quantity']; ?>">
                            <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                        </div>
                        <button class="cart-item-remove" onclick="removeFromCart('<?php echo $cart_key; ?>')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>

                    <div class="cart-actions">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="cart-subtotal" data-base-price="<?php echo $subtotal; ?>"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <!-- <div class="summary-row">
                        <span>Tax (10%)</span>
                        <span class="cart-tax" data-base-price="<?php echo $tax; ?>"><?php echo formatPrice($tax); ?></span>
                    </div> -->
                    <!-- <div class="summary-row">
                        <span>Shipping</span>
                        <span data-base-price="<?php echo $shipping; ?>"><?php echo $shipping == 0 ? 'FREE' : formatPrice($shipping); ?></span>
                    </div> -->
                    <?php if ($subtotal < $shipping_threshold): ?>
                    <div class="summary-note">
                        <i class="fas fa-info-circle"></i>
                        Add <span data-base-price="<?php echo $shipping_threshold - $subtotal; ?>"><?php echo formatPrice($shipping_threshold - $subtotal); ?></span> more for free shipping!
                    </div>
                    <?php endif; ?>
                    <!-- <div class="summary-row summary-total">
                        <span>Total</span>
                        <span class="cart-total" data-base-price="<?php echo $total; ?>"><?php echo formatPrice($total); ?></span>
                    </div> -->
                    
                    <a href="checkout.php" class="btn btn-primary btn-block">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                    
                    <div class="trust-badges">
                        <div class="trust-badge">
                            <i class="fas fa-lock"></i>
                            <span>Secure Checkout</span>
                        </div>
                        <div class="trust-badge">
                            <i class="fas fa-undo"></i>
                            <span>Easy Returns</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <?php include 'includes/scripts.php'; ?>
    <script src="cart.js"></script>
    <script>
        // Sync localStorage cart to PHP session on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check if cart is empty in session but valid in localStorage (e.g. fresh session)
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const isSessionEmpty = <?php echo $is_empty ? 'true' : 'false'; ?>;
            
            if (cart.length > 0 && isSessionEmpty) {
                // Send cart data to server
                fetch('api/sync-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(cart)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Cart sync error:', error));
            }
        });
        
        function updateQuantity(cartKey, quantity) {
            // ... Logic is in cart.js mostly, but cart.php had inline logic too. 
            // Better to use global function or this one. 
            // cart.php inline logic was:
            if (quantity < 1) {
                removeFromCart(cartKey);
                return;
            }
            
            // ... update localStorage ...
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const parts = cartKey.split('_');
            const productId = parts[0];
            const variationId = parts[1] || 0; // handle simple products
            
            const itemIndex = cart.findIndex(item => 
                item.productId == productId && item.variationId == variationId
            );
            
            if (itemIndex > -1) {
                cart[itemIndex].quantity = quantity;
                localStorage.setItem('cart', JSON.stringify(cart));
            }
            
            fetch('api/update-cart-quantity.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({cartKey: cartKey, quantity: quantity})
            })
            .then(() => location.reload())
            .catch(error => console.error('Update error:', error));
        }
        
        function removeFromCart(cartKey) {
            if (!confirm('Remove this item from cart?')) return;
            
            // ... remove from localStorage ...
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const parts = cartKey.split('_');
            const productId = parts[0];
            const variationId = parts[1] || 0;
            
            cart = cart.filter(item => 
                !(item.productId == productId && item.variationId == variationId)
            );
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            fetch('api/remove-from-cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({cartKey: cartKey})
            })
            .then(() => location.reload())
            .catch(error => console.error('Remove error:', error));
        }
    </script>
</body>
</html>
