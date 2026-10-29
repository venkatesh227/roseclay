<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$cart_items = [];
$cart_count = 0;
$cart_subtotal = 0;
$cart_weight = 0.0;
$country_id = isset($_GET['country_id']) ? (int)$_GET['country_id'] : 0;
$zone = null;
$shipping_charge = 0.0;
$handling_charge = 0.0;
$fuel_percentage = 0.0;
$gst_percentage = 0.0;
$fuel_amount = 0.0;
$gst_amount = 0.0;
$grand_total = 0.0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $cart_items[] = $item;
        $cart_count += $item['quantity'];
        $cart_subtotal += $item['price'] * $item['quantity'];

        $item_weight = parseWeight($item['variation_name'] ?? '');
        $cart_weight += $item_weight * $item['quantity'];
    }
}

$shipping_charge = 0.0;
$tax = 0.0;
$cart_total = $cart_subtotal;
$grand_total = $cart_subtotal;

if ($country_id > 0) {
    $country_stmt = $conn->prepare('SELECT * FROM countries WHERE id = ? LIMIT 1');
    $country_stmt->bind_param('i', $country_id);
    $country_stmt->execute();
    $country = $country_stmt->get_result()->fetch_assoc();

    if ($country) {
        $zone = intval($country['zone']);
        $shippingData = getShippingChargeForZoneWeight($conn, $zone, $cart_weight);

        $shipping_charge = $shippingData['shipping_charge'];
        $handling_charge = $shippingData['handling_charge'];

        $fuel_stmt = $conn->query('SELECT * FROM fuel_charges LIMIT 1');
        if ($fuel_stmt) {
            $fuel_data = $fuel_stmt->fetch_assoc();
            if ($fuel_data) {
                $fuel_percentage = floatval($fuel_data['fuel_charge']);
                $gst_percentage = floatval($fuel_data['gst']);
            }
        }

        // Shipping + Handling
        $shipping_total = $shipping_charge + $handling_charge;

        // Fuel on (Shipping + Handling)
        $fuel_amount = $shipping_total * ($fuel_percentage / 100);

        // GST on (Shipping + Handling + Fuel)
        $gst_amount = ($shipping_total + $fuel_amount) * ($gst_percentage / 100);

        $tax = $gst_amount;

        // Grand Total
        $cart_total = $cart_subtotal
            + $shipping_total
            + $fuel_amount
            + $gst_amount;

        $grand_total = $cart_total;
    }
}

echo json_encode([
    'success' => true,
    'cart_items' => $cart_items,
    'cart_count' => $cart_count,
    'cart_subtotal' => $cart_subtotal,
    'cart_weight' => $cart_weight,
    'zone' => $zone,
    'shipping_charge' => $shipping_charge,
    'fuel_percentage' => $fuel_percentage,
    'fuel_amount' => $fuel_amount,
    'handling_charge' => $handling_charge,
    'gst_percentage' => $gst_percentage,
    'gst_amount' => $gst_amount,
    'tax' => $tax,
    'cart_total' => $cart_total,
    'grand_total' => $grand_total
]);
