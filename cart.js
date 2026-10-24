// Shopping Cart Functions

// Add to Cart
// Add to Cart
function addToCart(productId, quantity = 1, variationId = 0) {
    fetch('api/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}&variation_id=${variationId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                updateCartCount(data.cart_count);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error adding to cart', 'error');
        });
}

// Update Cart Quantity
function updateCartQuantity(productId, quantity) {
    fetch('api/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cart_count);
                updateCartTotals(data.cart_subtotal, data.tax, data.cart_total);

                // Update item total
                const itemTotal = document.querySelector(`[data-product-id="${productId}"] .cart-item-total`);
                if (itemTotal) {
                    const price = parseFloat(itemTotal.dataset.basePrice); // Unit price (base INR)
                    const newTotalBase = price * quantity;
                    itemTotal.dataset.basePrice = newTotalBase; // Update total base price

                    const currency = window.currentCurrency || 'INR';
                    itemTotal.textContent = formatPrice(convertPrice(newTotalBase, currency), currency);
                }
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating cart', 'error');
        });
}

// Remove from Cart
function removeFromCart(productId) {
    if (!confirm('Remove this item from cart?')) return;

    fetch('api/remove-from-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                updateCartCount(data.cart_count);
                updateCartTotals(data.cart_subtotal, data.tax, data.cart_total);

                // Remove item from DOM
                const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
                if (cartItem) {
                    cartItem.remove();
                }

                // Check if cart is empty
                if (data.cart_count === 0) {
                    location.reload();
                }
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error removing from cart', 'error');
        });
}

// Update Cart Count in Header
function updateCartCount(count) {
    const badges = document.querySelectorAll('.cart-btn .badge');
    badges.forEach(badge => {
        badge.textContent = count;
    });
}

// Update Cart Totals
function updateCartTotals(subtotal, tax, total) {
    const subtotalEl = document.querySelector('.cart-subtotal');
    const taxEl = document.querySelector('.cart-tax');
    const totalEl = document.querySelector('.cart-total');

    const currency = window.currentCurrency || 'INR';

    if (subtotalEl) {
        subtotalEl.dataset.basePrice = subtotal;
        subtotalEl.textContent = formatPrice(convertPrice(subtotal, currency), currency);
    }
    if (taxEl) {
        taxEl.dataset.basePrice = tax;
        taxEl.textContent = formatPrice(convertPrice(tax, currency), currency);
    }
    if (totalEl) {
        totalEl.dataset.basePrice = total;
        totalEl.textContent = formatPrice(convertPrice(total, currency), currency);
    }
}

// Load Cart on Page Load
document.addEventListener('DOMContentLoaded', function () {
    // Update cart count
    fetch('api/get-cart.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cart_count);
            }
        })
        .catch(error => console.error('Error loading cart:', error));

    // Cart page quantity controls
    const qtyButtons = document.querySelectorAll('.qty-btn');
    qtyButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const cartItem = this.closest('.cart-item');
            const productId = cartItem.dataset.productId;
            const qtyInput = cartItem.querySelector('.cart-item-quantity input');
            let quantity = parseInt(qtyInput.value);

            if (this.textContent === '+') {
                quantity++;
            } else if (this.textContent === '-' && quantity > 1) {
                quantity--;
            }

            qtyInput.value = quantity;
            updateCartQuantity(productId, quantity);
        });
    });

    // Remove buttons
    const removeButtons = document.querySelectorAll('.cart-item-remove');
    removeButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const cartItem = this.closest('.cart-item');
            const productId = cartItem.dataset.productId;
            removeFromCart(productId);
        });
    });
});

// Apply Coupon
function applyCoupon() {
    const couponCode = document.querySelector('.coupon-section input').value;

    if (!couponCode) {
        showToast('Please enter a coupon code', 'info');
        return;
    }

    // For now, show a message (implement coupon logic in backend later)
    showToast('Coupon functionality coming soon!', 'info');
}
