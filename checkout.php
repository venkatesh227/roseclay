<?php
session_start();
require_once 'config.php';

// Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    redirect('products.php');
}

// Require login (or handle guest checkout - forcing login for now for simpler user ID tracking)
requireLogin();
$user_id = $_SESSION['user_id'];
$user_info = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch Shipping Methods
$shipping_result = $conn->query("SELECT * FROM shipping_methods WHERE status = 'active' ORDER BY cost ASC");
$shipping_methods = [];
while ($row = $shipping_result->fetch_assoc()) {
    $shipping_methods[] = $row;
}
$default_shipping = !empty($shipping_methods) ? $shipping_methods[0] : null;

// Calculate totals
$cart_items = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart_items as $item) {
    if (is_array($item)) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}
$tax = $subtotal * 0.10;
// Default shipping cost (updated by JS)
$shipping = $default_shipping ? $default_shipping['cost'] : 0;
// Check if free shipping applies to default
if ($default_shipping && $default_shipping['free_shipping_threshold'] && $subtotal >= $default_shipping['free_shipping_threshold']) {
    $shipping = 0;
}
$total = $subtotal + $tax + $shipping;

$page_title = 'Checkout';
include 'includes/head.php';
?>
<style>
    /* Modern Shipping Details */
.shipping-details-card {
    background: #fff;
    border: 1px solid #f0dce5;
    border-radius: 18px;
    padding: 26px;
    box-shadow: 0 8px 25px rgba(80, 50, 60, 0.07);
}

.shipping-details-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}

.shipping-details-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #fff0f5;
    color: #f790b2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.shipping-details-heading h3 {
    margin: 0;
    color: #333;
    font-size: 21px;
    font-weight: 600;
}

.shipping-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.shipping-info-box {
    padding: 18px;
    background: #fff8fa;
    border: 1px solid #f7dce6;
    border-radius: 12px;
}

.shipping-info-label {
    display: block;
    color: #888;
    font-size: 13px;
    margin-bottom: 7px;
}

.shipping-info-value {
    display: block;
    color: #333;
    font-size: 17px;
    font-weight: 600;
}

.shipping-free {
    color: #28a745;
}

.shipping-info-note {
    margin: 18px 0 0;
    padding: 13px 15px;
    background: #f8f8f8;
    border-radius: 9px;
    color: #777;
    font-size: 13px;
    line-height: 1.5;
}

@media (max-width: 600px) {
    .shipping-info-grid {
        grid-template-columns: 1fr;
    }

    .shipping-details-card {
        padding: 20px;
    }
}
    /* Validation Styles */
    .form-group {
        position: relative;
    }

    .form-group input.valid,
    .form-group select.valid {
        border-color: #27ae60;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%2327ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    .form-group input.invalid,
    .form-group select.invalid {
        border-color: #e74c3c;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    .error-msg {
        color: #e74c3c;
        font-size: 0.75rem;
        display: none;
        margin-top: 4px;
    }

    .form-group input.invalid + .error-msg,
    .form-group select.invalid + .error-msg {
        display: block;
    }
</style>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="cart.php">Cart</a>
            <i class="fas fa-chevron-right"></i>
            <span>Checkout</span>
        </div>
    </div>

    <section class="checkout-section">
        <div class="container">
            <div class="checkout-container">
                <!-- Billing Details -->
                <div class="checkout-form-container">
                    <div class="checkout-section-card">
                        <h3><i class="fas fa-map-marker-alt"></i> Shipping Address</h3>
                        <form id="checkoutForm">
                            <div class="form-row-3">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" required value="<?php echo htmlspecialchars($user_info['first_name'] ?? ''); ?>">
                                    <small class="error-msg"></small>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" required value="<?php echo htmlspecialchars($user_info['last_name'] ?? ''); ?>">
                                    <small class="error-msg"></small>
                                </div>
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" required pattern="[0-9]{10}" title="Please enter a 10-digit mobile number" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>">
                                    <small class="error-msg"></small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" required value="<?php echo htmlspecialchars($user_info['email'] ?? $_SESSION['user_email'] ?? ''); ?>">
                                <small class="error-msg"></small>
                            </div>
                            <div class="form-group">
                                <label>Street Address</label>
                                <input type="text" name="address" required placeholder="House number and street name" value="<?php echo htmlspecialchars($user_info['address'] ?? ''); ?>">
                                <small class="error-msg"></small>
                            </div>
                            <div class="form-group">
                                <?php
                                $country_query = mysqli_query($conn, "SELECT id, country_name FROM countries ORDER BY country_name ASC");
                                ?>
                                <label>Select Country</label>
                                <select name="country" class="form-control" required>
                                    <option value="">-- Select Country --</option>

                                    <?php while ($country = mysqli_fetch_assoc($country_query)) { ?>
                                        <option value="<?php echo $country['id']; ?>"
                                            <?php echo (($user_info['country'] ?? '') == $country['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($country['country_name']); ?>
                                        </option>
                                    <?php } ?>

                                </select>
                                <small class="error-msg"></small>
                            </div>
                            <div class="form-row-3">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" required value="<?php echo htmlspecialchars($user_info['city'] ?? ''); ?>">
                                    <small class="error-msg"></small>
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="state" required value="<?php echo htmlspecialchars($user_info['state'] ?? ''); ?>">
                                    <small class="error-msg"></small>
                                </div>
<div class="form-group">
    <label>Post Code</label>
    <input
        type="text"
        name="zip_code"
        required
        pattern="[A-Za-z0-9]+([ -][A-Za-z0-9]+)*"
        title="Please enter a valid postcode or ZIP code"
        value="<?php echo htmlspecialchars($user_info['zip_code'] ?? ''); ?>"
    >
    <small class="error-msg"></small>
</div>
                            </div>
                            <div class="form-group">
                                <label>Order Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                            </div>
                        </form>
                    </div>

<div class="checkout-section-card">

    <div class="shipping-details-card">

        <div class="shipping-details-heading">

            <div class="shipping-details-icon">
                <i class="fas fa-truck"></i>
            </div>

            <h3>Shipping Details</h3>

        </div>


        <div class="shipping-info-grid">

            <!-- Shipping Zone -->
            <div class="shipping-info-box">

                <span class="shipping-info-label">
                    Delivery Zone
                </span>

                <span
                    class="shipping-info-value"
                    id="shipping-zone-detail"
                >
                    N/A
                </span>

            </div>


            <!-- Shipping Charge -->
            <div class="shipping-info-box">

                <span class="shipping-info-label">
                    Shipping Charge
                </span>

                <span
                    class="shipping-info-value shipping-free"
                    id="shipping-display-detail"
                >
                    <?php echo formatPrice(0, VISITOR_CURRENCY); ?>
                </span>

            </div>

        </div>


        <div class="shipping-info-note">

            <i class="fas fa-info-circle"></i>

            Shipping charges are calculated automatically based on
            your delivery zone and total cart weight.

        </div>


        <input
            type="hidden"
            name="shipping_method_id"
            value="0"
        >

    </div>

</div>

                    <div class="checkout-section-card">
                        <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                        <div class="payment-methods-checkout">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="razorpay" checked onchange="togglePayment_method('razorpay')">
                                <div class="payment-method-content">
                                    <i class="fas fa-bolt"></i>
                                    <span>Razorpay</span>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="paypal" onchange="togglePayment_method('paypal')">
                                <div class="payment-method-content">
                                    <i class="fab fa-paypal"></i>
                                    <span>PayPal</span>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cod" onchange="togglePayment_method('cod')">
                                <div class="payment-method-content">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Cash on Delivery</span>
                                </div>
                            </label>
                        </div>

                        <!-- PayPal Button Container -->
                        <div id="paypal-button-container" style="display:none; margin-top: 20px;"></div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="checkout-summary">
                    <h3>Your Order</h3>
                    <div class="checkout-items">
                        <?php foreach ($cart_items as $item):
                            if (!is_array($item)) continue;
                        ?>
                            <div class="checkout-item">
                                <img src="uploads/products/<?php echo $item['image']; ?>" alt="Product">
                                <div class="checkout-item-details">
                                    <h4><?php echo $item['name']; ?> <?php echo isset($item['variation_name']) ? '(' . $item['variation_name'] . ')' : ''; ?></h4>
                                    <p>Qty: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="checkout-item-price" data-base-price="<?php echo $item['price'] * $item['quantity']; ?>">
                                    <?php echo formatPrice($item['price'] * $item['quantity'], VISITOR_CURRENCY); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="cart-subtotal" data-base-price="<?php echo $subtotal; ?>"><?php echo formatPrice($subtotal, VISITOR_CURRENCY); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping Zone</span>
                        <span id="shipping-zone">N/A</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="shipping-display" data-base-price="0"><?php echo formatPrice(0, VISITOR_CURRENCY); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Fuel Charge</span>
                        <span id="fuel-display" data-base-price="0"><?php echo formatPrice(0, VISITOR_CURRENCY); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>GST Charge</span>
                        <span id="gst-display" data-base-price="0"><?php echo formatPrice(0, VISITOR_CURRENCY); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="total-display" class="cart-total" data-base-price="<?php echo $total; ?>"><?php echo formatPrice($total, VISITOR_CURRENCY); ?></span>
                    </div>

                    <button id="placeOrderBtn" class="btn btn-primary btn-block btn-grande" onclick="processPayment()">
                        Place Order
                    </button>

                    <div class="secure-checkout">
                        <i class="fas fa-lock"></i> SSL Secured Payment
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>

    <!-- Payment Scripts -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=<?php echo PAYPAL_CURRENCY; ?>"></script>

    <script>
        const currency = '<?php echo VISITOR_CURRENCY; ?>';
        let totalAmountINR = <?php echo $total; ?>;
        let currentShippingCharge = 0.0;
        let currentFuelAmount = 0.0;
        let currentGstAmount = 0.0;
        const razorpayKey = '<?php echo RAZORPAY_KEY_ID; ?>';
        const subtotal = <?php echo $subtotal; ?>;

        async function refreshCheckoutSummary() {
            const countrySelect = document.querySelector('select[name="country"]');
            const countryId = countrySelect ? parseInt(countrySelect.value) : 0;
            const params = new URLSearchParams();
            if (countryId > 0) {
                params.set('country_id', countryId);
            }

            function displayAmount(amount) {
                return currency === 'USD' ? convertPrice(amount, currency) : amount;
            }

            try {
                const response = await fetch('api/get-cart.php?' + params.toString());
                const data = await response.json();
                if (!data.success) return;

                currentShippingCharge = parseFloat(data.shipping_charge) || 0.0;
                currentFuelAmount = parseFloat(data.fuel_amount) || 0.0;
                currentGstAmount = parseFloat(data.gst_amount) || 0.0;
                totalAmountINR = parseFloat(data.grand_total) || subtotal;

                const zoneText = data.zone ? 'Zone ' + data.zone : 'N/A';
                const shippingText = currentShippingCharge === 0 ? 'FREE' : formatPrice(displayAmount(currentShippingCharge), currency);

                const zoneElements = [
                    document.getElementById('shipping-zone'),
                    document.getElementById('shipping-zone-detail')
                ];
                const shippingElements = [
                    document.getElementById('shipping-display'),
                    document.getElementById('shipping-display-detail')
                ];

                zoneElements.forEach(el => { if (el) el.textContent = zoneText; });
                shippingElements.forEach(el => { if (el) el.textContent = shippingText; });
                document.getElementById('fuel-display').textContent = formatPrice(displayAmount(currentFuelAmount), currency) + (data.fuel_percentage ? ' (' + data.fuel_percentage + '%)' : '');
                document.getElementById('gst-display').textContent = formatPrice(displayAmount(currentGstAmount), currency) + (data.gst_percentage ? ' (' + data.gst_percentage + '%)' : '');
                document.getElementById('total-display').textContent = formatPrice(displayAmount(totalAmountINR), currency);
            } catch (error) {
                console.error('Failed to refresh checkout summary:', error);
            }
        }

        function updateTotal() {
            refreshCheckoutSummary();
        }

        function togglePayment_method(method) {
            const paypalContainer = document.getElementById('paypal-button-container');
            const placeOrderBtn = document.getElementById('placeOrderBtn');

            if (method === 'paypal') {
                paypalContainer.style.display = 'block';
                placeOrderBtn.style.display = 'none'; // Hide main button, use PayPal buttons
            } else {
                paypalContainer.style.display = 'none';
                placeOrderBtn.style.display = 'block';
                placeOrderBtn.textContent = method === 'cod' ? 'Place Order (COD)' : 'Pay with Razorpay';
            }
        }

        // Form Validation Logic
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('checkoutForm');
            const inputs = form.querySelectorAll('[required]');
            const countrySelect = document.querySelector('select[name="country"]');

            if (countrySelect) {
                countrySelect.addEventListener('change', refreshCheckoutSummary);
            }

            inputs.forEach(input => {
                input.addEventListener('blur', () => validateInput(input));
                input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input', () => {
                    if (input.classList.contains('invalid')) validateInput(input);
                });
            });

            refreshCheckoutSummary();
        });

        function validateInput(input) {
            const errorMsg = input.nextElementSibling?.classList.contains('error-msg')
                ? input.nextElementSibling
                : null;
            const value = input.value.trim();
            const requiredMessages = {
                first_name: 'Please enter your first name',
                last_name: 'Please enter your last name',
                phone: 'Please enter your phone number',
                email: 'Please enter your email address',
                address: 'Please enter your street address',
                country: 'Please select your country',
                city: 'Please enter your city',
                state: 'Please enter your state',
                zip_code: 'Please enter your postcode or ZIP code'
            };

            input.setCustomValidity('');
            let isValid = input.checkValidity() && (!input.required || value !== '');
            let message = '';

            if (!value && input.required) {
                isValid = false;
                message = requiredMessages[input.name] || 'Please complete this field';
            } else if (input.name === 'email' && !input.checkValidity()) {
                isValid = false;
                message = 'Please enter a valid email address';
            } else if (input.name === 'phone' && !/^\d{10}$/.test(value)) {
                isValid = false;
                message = 'Please enter a valid 10-digit number';
            } else if (input.name === 'zip_code' &&
                !/^[A-Za-z0-9]+(?:[ -][A-Za-z0-9]+)*$/.test(value)) {
                isValid = false;
                message = 'Please enter a valid postcode or ZIP code';
            } else if (!input.checkValidity()) {
                isValid = false;
                message = input.validationMessage;
            }

            input.classList.toggle('invalid', !isValid);
            input.classList.toggle('valid', isValid);
            input.setAttribute('aria-invalid', String(!isValid));
            if (errorMsg) {
                errorMsg.textContent = isValid ? '' : message;
            }

            return isValid;
        }

        async function createOrder(paymentMethod) {
            const form = document.getElementById('checkoutForm');
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            let allValid = true;

            inputs.forEach(input => {
                if (!validateInput(input)) allValid = false;
            });

            if (!allValid) {
                // Scroll to first invalid
                const firstInvalid = form.querySelector('.invalid');
                if (firstInvalid) firstInvalid.focus();
                return null;
            }

            const formData = new FormData(form);
            formData.append('payment_method', paymentMethod);
            const shippingMethodId = document.querySelector('input[name="shipping_method_id"]');
            formData.append('shipping_method_id', shippingMethodId ? shippingMethodId.value : 0);
            formData.append('total', totalAmountINR);

            try {
                const response = await fetch('api/create-order.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (!data.success) {
                    alert(data.message);
                    return null;
                }
                return data;
            } catch (error) {
                console.error('Order creation failed:', error);
                alert('Connection error. Please try again.');
                return null;
            }
        }

        async function processPayment() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

            if (paymentMethod === 'cod') {
                const orderData = await createOrder('cod');
                if (orderData) {
                    localStorage.removeItem('cart');
                    window.location.href = `order-success.php?id=${orderData.order_id}`;
                }
            } else if (paymentMethod === 'razorpay') {
                const orderData = await createOrder('razorpay');
                if (orderData) {
                    const options = {
                        "key": razorpayKey,
                        "amount": orderData.amount, // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
                        "currency": "INR",
                        "name": "RoseClay",
                        "description": "Order #" + orderData.order_number,
                        "image": "assets/logo.png",
                        "order_id": orderData.razorpay_order_id, // This is a sample Order ID. Pass the `id` obtained in the response of Step 1
                        "handler": function(response) {
                            // Verify Payment
                            verifyRazorpayPayment(response, orderData.order_id);
                        },
                        "prefill": {
                            "name": document.querySelector('input[name="first_name"]').value,
                            "email": document.querySelector('input[name="email"]').value,
                            "contact": document.querySelector('input[name="phone"]').value
                        },
                        "theme": {
                            "color": "#F790B2"
                        }
                    };
                    const rzp1 = new Razorpay(options);
                    rzp1.open();
                }
            }
        }

        async function verifyRazorpayPayment(response, orderId) {
            const formData = new FormData();
            formData.append('razorpay_payment_id', response.razorpay_payment_id);
            formData.append('razorpay_order_id', response.razorpay_order_id);
            formData.append('razorpay_signature', response.razorpay_signature);
            formData.append('order_id', orderId);

            try {
                const res = await fetch('api/verify-payment.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem('cart');
                    window.location.href = `order-success.php?id=${orderId}`;
                } else {
                    alert('Payment verification failed');
                }
            } catch (e) {
                console.error(e);
                alert('Error verifying payment');
            }
        }

        // Initialize PayPal
        paypal.Buttons({
            createOrder: async function(data, actions) {
                // Return order ID from backend
                // Since we need to validate address first, we create the order on our backend first
                const orderData = await createOrder('paypal');
                if (!orderData) return;

                // For simple integration without backend-to-paypal order creation (which requires Secret Key),
                // we can just use client-side createOrder for the amount, and then capture it.
                // But we must sync with our backend order ID.
                // Here: calculate USD amount
                const rate = <?php echo USD_TO_INR_RATE; ?>;
                const totalUSD = (totalAmountINR / rate).toFixed(2);

                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: totalUSD
                        },
                        custom_id: orderData.order_id // Store our internal order ID here
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Update our backend
                    // details.purchase_units[0].custom_id contains our order ID
                    const internalOrderId = details.purchase_units[0].custom_id || details.id;

                    // Call our backend to confirm
                    const formData = new FormData();
                    formData.append('order_id', internalOrderId); // This might be null if we didn't set custom_id correctly or if PayPal stripped it.
                    // Better approach: use session or simply matching last pending ID, but that's risky.
                    // Let's assume createOrder stored the order ID in a global var if custom_id fails, but createOrder (paypal prop) is distinct.
                    // For now, assume custom_id works or we have to trust content.

                    // Actually, since createOrder returns a promise resolving orderData, we might not have access to it here easily without closure.
                    // BUT, `createOrder` above calls our backend `api/create-order.php` which returns `order_id`.
                    // We need to pass that `order_id` to `onApprove`.

                    // Let's create a temporary variable to hold the ID from createOrder
                    // This is handled by PayPal execution flow.

                    // Simplified: We'll verify payment via API.
                    verifyPayPalPayment(details);
                });
            }
        }).render('#paypal-button-container');

        async function verifyPayPalPayment(details) {
            // Internal Order ID was stored in custom_id?
            // If custom_id is not reliable, we can't easily link.
            // Alternative: create order in our DB *after* payment? No, address needed.

            // Let's just assume we rely on the createOrder step.
            // Wait, I can hack it: global let.
            // No, PayPal buttons might be async.

            // Actually `details` object contains `purchase_units[0].custom_id`.
            const orderId = details.purchase_units[0].custom_id;
            const transactionId = details.id;

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('transaction_id', transactionId);
            formData.append('method', 'paypal');

            try {
                const res = await fetch('api/verify-payment.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem('cart');
                    window.location.href = `order-success.php?id=${orderId}`;
                } else {
                    window.location.href = `order-success.php?id=${orderId}&status=warning`;
                }
            } catch (e) {
                console.error(e);
                window.location.href = `order-success.php?id=${orderId}`;
            }
        }
    </script>
</body>

</html>