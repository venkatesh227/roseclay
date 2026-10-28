<?php
session_start();
require_once '../config.php';
require_once '../vendor/autoload.php';

use Razorpay\Api\Api;

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to checkout']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    // Get form data
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip_code = sanitize($_POST['zip_code']);
    $country_id = isset($_POST['country']) ? intval($_POST['country']) : 0;
    $payment_method = sanitize($_POST['payment_method']);
    $shipping_method_id = isset($_POST['shipping_method_id']) ? intval($_POST['shipping_method_id']) : 0;
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    if (!preg_match('/^\+?[0-9]{8,15}$/', $phone)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid phone number'
        ]);
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9]+(?:[ -][A-Za-z0-9]+)*$/', $zip_code)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid postcode or ZIP code']);
        exit;
    }

    // Recalculate Total (Security)
    $cart_items = $_SESSION['cart'] ?? [];
    if (empty($cart_items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    $subtotal = 0;
    $cart_weight = 0.0;
    foreach ($cart_items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $subtotal += $item['price'] * $item['quantity'];
        $cart_weight += parseWeight($item['variation_name'] ?? '') * $item['quantity'];
    }

    $shipping_charge = 0.0;
    $shipping_method_name = 'Default Shipping';
    $zone = null;
    $country_name = '';

    if ($country_id > 0) {
        $country_stmt = $conn->prepare('SELECT * FROM countries WHERE id = ? LIMIT 1');
        $country_stmt->bind_param('i', $country_id);
        $country_stmt->execute();
        $country = $country_stmt->get_result()->fetch_assoc();

        if ($country) {
            $country_name = $country['country_name'];
            $zone = intval($country['zone']);
            $shippingData = getShippingChargeForZoneWeight($conn, $zone, $cart_weight);

            $shipping_charge = $shippingData['shipping_charge'];
            $handling_charge = $shippingData['handling_charge'];

            $shipping_method_name = 'Zone ' . $zone . ' Shipping';
        }
    }

    if (empty($country_name)) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid country']);
        exit;
    }

    $fuel_stmt = $conn->query('SELECT * FROM fuel_charges LIMIT 1');
    $fuel_percentage = 0.0;
    $gst_percentage = 0.0;
    if ($fuel_stmt) {
        $fuel_data = $fuel_stmt->fetch_assoc();
        if ($fuel_data) {
            $fuel_percentage = floatval($fuel_data['fuel_charge']);
            $gst_percentage = floatval($fuel_data['gst']);
        }
    }

    $shipping_total = $shipping_charge + $handling_charge;

    // Fuel on Shipping + Handling
    $fuel_amount = $shipping_total * ($fuel_percentage / 100);

    // GST on Shipping + Handling + Fuel
    $gst_amount = ($shipping_total + $fuel_amount) * ($gst_percentage / 100);

    $tax = $gst_amount;

    $total = $subtotal
        + $shipping_total
        + $fuel_amount
        + $gst_amount;

    $order_number = generateOrderNumber();
    $status = 'pending';
    $payment_status = $payment_method == 'cod' ? 'pending' : 'Awaiting Payment';

    // Insert Order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, first_name, last_name, email, phone, address, city, state, zip_code, country, subtotal, tax, shipping_cost, shipping_method, total, payment_method, status, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssssdddsdssss", $user_id, $order_number, $first_name, $last_name, $email, $phone, $address, $city, $state, $zip_code, $country_name, $subtotal, $tax, $shipping_charge, $shipping_method_name, $total, $payment_method, $status, $payment_status, $notes);

    if ($stmt->execute()) {
        $order_id = $conn->insert_id;

        // Insert Order Items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, variation_id, product_name, variation_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($cart_items as $item) {
            if (!is_array($item)) continue;
            $item_total = $item['price'] * $item['quantity'];

            $product_id = $item['id'];
            $variation_id = (isset($item['variation_id']) && $item['variation_id'] > 0) ? $item['variation_id'] : null;
            $product_name = $item['name'];
            $variation_name = isset($item['variation_name']) ? $item['variation_name'] : null;

            $item_stmt->bind_param("iiissidd", $order_id, $product_id, $variation_id, $product_name, $variation_name, $item['quantity'], $item['price'], $item_total);
            if (!$item_stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Order item insert failed: ' . $item_stmt->error]);
                exit;
            }
        }

        if ($payment_method === 'cod') {
            unset($_SESSION['cart']);
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'order_number' => $order_number
            ]);
            exit;
        }

        // Response
        // Ideally call Razorpay API here to create order_id
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        try {

            $razorpayOrder = $api->order->create([
                'receipt' => $order_number,
                'amount'   => round($total * 100), // Amount in paise
                'currency' => 'INR'
            ]);

            $razorpay_order_id = $razorpayOrder['id'];
        } catch (Exception $e) {

            echo json_encode([
                'success' => false,
                'message' => 'Razorpay Error: ' . $e->getMessage()
            ]);
            exit;
        }
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number,
            'amount' => $total * 100, // Amount in paise
            'razorpay_order_id' => $razorpay_order_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
