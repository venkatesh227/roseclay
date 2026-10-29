<?php
session_start();
require_once 'config.php';

// Get cart items
$cart_items = $_SESSION['cart'] ?? [];

// If cart is empty, show empty state
$is_empty = empty($cart_items);

// Calculate totals
$subtotal = 0;
$cart_count = 0;
if (!$is_empty) {
    foreach ($cart_items as $item) {
        if (is_array($item) && isset($item['price'], $item['quantity'])) {
            $subtotal += $item['price'] * $item['quantity'];
            $cart_count += (int)$item['quantity'];
        }
    }
}

// Shipping Threshold (Approx $50 USD -> 4000 INR)
$shipping_threshold = 4000;
$total = $subtotal;
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
                            <p class="cart-item-price" data-base-price="<?php echo $item['price']; ?>"><?php echo formatVisitorPrice($item['price']); ?></p>
                            <?php if (isset($item['original_price']) && $item['original_price'] > $item['price']): ?>
                            <p class="cart-item-save">You save: <span data-base-price="<?php echo $item['original_price'] - $item['price']; ?>"><?php echo formatVisitorPrice($item['original_price'] - $item['price']); ?></span></p>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-quantity">
                            <button type="button" class="qty-btn" onclick="updateQuantity('<?php echo $cart_key; ?>', -1)">-</button>
                            <input type="number" value="<?php echo $item['quantity']; ?>" readonly>
                            <button type="button" class="qty-btn" onclick="updateQuantity('<?php echo $cart_key; ?>', 1)">+</button>
                        </div>
                        <div class="cart-item-total" data-base-price="<?php echo $item['price'] * $item['quantity']; ?>">
                            <?php echo formatVisitorPrice($item['price'] * $item['quantity']); ?>
                        </div>
                        <button type="button" class="cart-item-remove" onclick="removeCartItemByKey('<?php echo $cart_key; ?>')">
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
                        <span class="cart-subtotal" data-base-price="<?php echo $subtotal; ?>"><?php echo formatVisitorPrice($subtotal); ?></span>
                    </div>


                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span class="cart-total" data-base-price="<?php echo $total; ?>"><?php echo formatVisitorPrice($total); ?></span>
                    </div>
                    
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include 'includes/scripts.php'; ?>
    <script>
        window.cartPageCount = <?php echo json_encode($cart_count); ?>;
        window.cartDebug = new URLSearchParams(window.location.search).get('cart_debug') === '1';

        // Sync localStorage cart to PHP session on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.cartDebug) {
                console.info('[cart-debug] Cart page rendered from PHP session:', {
                    itemRows: <?php echo json_encode(count($cart_items)); ?>,
                    quantityTotal: window.cartPageCount
                });
            }

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
        
        function updateQuantity(cartKey, change) {
            const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
            if (!cartItem) return;

            const quantityInput = cartItem.querySelector('.cart-item-quantity input');
            const quantity = parseInt(quantityInput.value, 10) + change;

            if (quantity < 1) {
                removeCartItemByKey(cartKey);
                return;
            }

            const buttons = cartItem.querySelectorAll('.qty-btn');
            buttons.forEach(button => button.disabled = true);

            fetch('api/update-cart-quantity.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({cartKey: cartKey, quantity: quantity})
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unable to update quantity',
                    text: data.message || 'Please try again.'
                });
                return;
            }

                quantityInput.value = data.quantity;
                const itemTotal = cartItem.querySelector('.cart-item-total');
                itemTotal.dataset.basePrice = data.item_total;
                itemTotal.textContent = data.item_total_formatted;

                const subtotal = document.querySelector('.cart-subtotal');
                subtotal.dataset.basePrice = data.cart_subtotal;
                subtotal.textContent = data.cart_subtotal_formatted;

                const total = document.querySelector('.cart-total');
                total.dataset.basePrice = data.cart_total;
                total.textContent = data.cart_total_formatted;

                let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                const parts = cartKey.split('_');
                const productId = parts[0];
                const variationId = parts[1] || 0;
                const itemIndex = cart.findIndex(item =>
                    item.productId == productId && (item.variationId || 0) == variationId
                );
                if (itemIndex > -1) {
                    cart[itemIndex].quantity = data.quantity;
                    localStorage.setItem('cart', JSON.stringify(cart));
                }

                if (typeof updateCartCount === 'function') {
                    updateCartCount(data.cart_count);
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Request failed',
                    text: 'Unable to update cart quantity. Please try again.'
                });
            })
            .finally(() => buttons.forEach(button => button.disabled = false));
        }
        
        async function removeCartItemByKey(cartKey) {
            const result = await Swal.fire({
                title: 'Remove item?',
                text: 'Are you sure you want to remove this item from your cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Keep item'
            });

            if (!result.isConfirmed) return;
            
            fetch('api/remove-from-cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({cartKey: cartKey})
            })
            .then(response => response.json())
            .then(async data => {
                if (data.success) {
                    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    const [productId, variationId = '0'] = cartKey.split('_');
                    cart = cart.filter(item =>
                        !(String(item.productId) === productId &&
                            String(item.variationId || 0) === variationId)
                    );
                    localStorage.setItem('cart', JSON.stringify(cart));
                    await Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: data.message || 'Item removed from cart.',
                        confirmButtonColor: '#f790b2'
                    });
                    location.reload();
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Unable to remove item',
                    text: data.message || 'Please try again.'
                });
            })
            .catch(error => {
                console.error('Remove error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Request failed',
                    text: 'Unable to remove this item. Please try again.'
                });
            });
        }
    </script>
</body>
</html>
