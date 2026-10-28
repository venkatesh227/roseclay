<?php
// Start session if not already started - MUST BE FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SERVER['HTTP_X_CURRENCY_UPDATE'] ?? '') === '1') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    $currency = is_array($input) && isset($input['currency']) ? $input['currency'] : null;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    if (!in_array($currency, ['INR', 'USD'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unsupported currency']);
        exit;
    }

    $_SESSION['currency'] = $currency;
    echo json_encode(['success' => true, 'currency' => $currency]);
    exit;
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'roseclay');

// Site Configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Determine the project root based on the location of config.php
// Determine the project root based on the SCRIPT_NAME (URL path)
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // e.g. /roseclay/ecomm/admin or /roseclay/ecomm
$script_path = rtrim($script_path, '/');

// If we are in the admin folder, go up one level to get site root
if (substr($script_path, -6) === '/admin') {
    $path = substr($script_path, 0, -6);
} else {
    $path = $script_path;
}

define('SITE_URL', $protocol . '://' . $host . $path);
define('ADMIN_URL', SITE_URL . '/admin');

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Helper Functions
function sanitize($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function redirect($url)
{
    header("Location: " . $url);
    exit();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) || isset($_SESSION['admin_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect(SITE_URL . '/login.php');
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

// Currency Configuration
define('DEFAULT_CURRENCY', 'INR');
if (!isset($_SESSION['currency']) || !in_array(strtoupper($_SESSION['currency']), ['INR', 'USD'], true)) {
    $_SESSION['currency'] = DEFAULT_CURRENCY;
}
define('VISITOR_CURRENCY', strtoupper($_SESSION['currency']));
define('USD_TO_INR_RATE', 83); // 1 USD = 83 INR

// Payment Gateway Configuration
// Razorpay (Replace with your actual keys from https://dashboard.razorpay.com/)
define('RAZORPAY_KEY_ID', 'rzp_test_TRaIzjnDQOKKBD');
define('RAZORPAY_KEY_SECRET', 'QaibV1MkjxfGyZzrS2MoK6Lr');

// PayPal (Replace with your actual Client ID from https://developer.paypal.com/)
define('PAYPAL_CLIENT_ID', 'sb'); // 'sb' for sandbox/test mode
define('PAYPAL_CURRENCY', 'USD');

function formatPrice($price, $currency = 'INR')
{
    if ($currency == 'INR') {
        return '₹' . number_format($price, 2);
    }
    return '$' . number_format($price / USD_TO_INR_RATE, 2);
}

function formatVisitorPrice($price)
{
    return formatPrice($price, VISITOR_CURRENCY);
}

function parseWeight($weight_value)
{
    if (!$weight_value) {
        return 0.0;
    }

    $weight = trim(strtolower($weight_value));
    $weight = str_replace([' ', ','], '', $weight);

    if (preg_match('/^([0-9]+(?:\.[0-9]+)?)(kg|kilogram|kilograms)$/', $weight, $matches)) {
        return floatval($matches[1]);
    }

    if (preg_match('/^([0-9]+(?:\.[0-9]+)?)(g|gm|gms|gram|grams)$/', $weight, $matches)) {
        return floatval($matches[1]) / 1000.0;
    }

    if (preg_match('/^([0-9]+(?:\.[0-9]+)?)(lb|lbs|pound|pounds)$/', $weight, $matches)) {
        return floatval($matches[1]) * 0.45359237;
    }

    if (preg_match('/^([0-9]+(?:\.[0-9]+)?)$/', $weight, $matches)) {
        return floatval($matches[1]);
    }

    return 0.0;
}

function normalizeShippingRateType($value)
{
    $normalized = strtolower(trim((string)$value));
    $normalized = str_replace(['-', '_'], ' ', $normalized);
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    return $normalized;
}

function getShippingChargeForZoneWeight($conn, $zone, $weight)
{
    $zone = intval($zone);

    if ($zone < 1 || $zone > 10) {
        return [
            'shipping_charge' => 0,
            'handling_charge' => 0
        ];
    }

    $zone_col = "zone" . $zone;
    $normalized_rate_type = normalizeShippingRateType('Non Documents');

    /*
    ===================================================
    WEIGHT <= 30 KG
    ===================================================
    */

    if ($weight <= 30) {

        $stmt = $conn->prepare("
            SELECT *
            FROM shipping_charges
            WHERE LOWER(REPLACE(REPLACE(rate_type,'-',' '),'_',' '))=?
            AND CAST(weight AS DECIMAL(10,2)) >= ?
            ORDER BY CAST(weight AS DECIMAL(10,2)) ASC
            LIMIT 1
        ");

        $stmt->bind_param("sd", $normalized_rate_type, $weight);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            return [

                'shipping_charge' => (float)$row[$zone_col],

                'handling_charge' => (float)$row['handling_charges']

            ];
        }

        // Highest slab fallback

        $stmt = $conn->prepare("
            SELECT *
            FROM shipping_charges
            WHERE LOWER(REPLACE(REPLACE(rate_type,'-',' '),'_',' '))=?
            ORDER BY CAST(weight AS DECIMAL(10,2)) DESC
            LIMIT 1
        ");

        $stmt->bind_param("s", $normalized_rate_type);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            return [

                'shipping_charge' => (float)$row[$zone_col],

                'handling_charge' => (float)$row['handling_charges']

            ];
        }
    }

    /*
    ===================================================
    WEIGHT > 30 KG
    ===================================================
    */ else {

        $stmt = $conn->prepare("
            SELECT
                $zone_col AS multiplier,
                handling_charges
            FROM shipping_multiplier
            WHERE ? BETWEEN weight_from AND weight_to
            LIMIT 1
        ");

        $stmt->bind_param("d", $weight);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $shipping_charge = $weight * (float)$row['multiplier'];
            return [

                'shipping_charge' => $shipping_charge,

                'handling_charge' => (float)$row['handling_charges']

            ];
        }
    }

    return [

        'shipping_charge' => 0,

        'handling_charge' => 0

    ];
}

function generateOrderNumber()
{
    return 'RC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function setMessage($message, $type = 'success')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function getMessage()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
