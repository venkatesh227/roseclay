<?php
session_start();
require_once 'config.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setMessage('Your cart is empty', 'error');
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $address = sanitize($_POST['address']);
    $apartment = sanitize($_POST['apartment'] ?? '');
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $zip_code = sanitize($_POST['zip_code']);
    $country = sanitize($_POST['country']);
    $shipping_method = sanitize($_POST['shipping'] ?? 'standard');
    $payment_method = sanitize($_POST['payment'] ?? 'card');
    
    // Calculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Calculate shipping cost
    $shipping_cost = 0;
    if ($shipping_method == 'express') {
        $shipping_cost = 15.00;
    } elseif ($shipping_method == 'overnight') {
        $shipping_cost = 30.00;
    }
    
    $tax = $subtotal * 0.10; // 10% tax
    $total = $subtotal + $tax + $shipping_cost;
    
    // Generate order number
    $order_number = generateOrderNumber();
    
    // Get user ID if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
    
    // Insert order
    $sql = "INSERT INTO orders (user_id, order_number, first_name, last_name, email, phone, address, apartment, city, state, zip_code, country, subtotal, tax, shipping_cost, total, payment_method, shipping_method, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssssssdddsss", 
        $user_id, $order_number, $first_name, $last_name, $email, $phone, 
        $address, $apartment, $city, $state, $zip_code, $country, 
        $subtotal, $tax, $shipping_cost, $total, $payment_method, $shipping_method
    );
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $item_total = $item['price'] * $item['quantity'];
            
            $sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisidd", 
                $order_id, $item['id'], $item['name'], 
                $item['quantity'], $item['price'], $item_total
            );
            $stmt->execute();
            
            // Update product stock
            $new_stock = $item['stock'] - $item['quantity'];
            $conn->query("UPDATE products SET stock = $new_stock WHERE id = " . $item['id']);
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Set success message
        setMessage('Order placed successfully! Order #' . $order_number, 'success');
        
        // Redirect based on user status
        if (isset($_SESSION['user_id'])) {
            redirect('my-orders.php');
        } else {
            redirect('order-success.php?order=' . $order_number);
        }
    } else {
        setMessage('Error placing order. Please try again.', 'error');
        redirect('checkout.php');
    }
} else {
    redirect('checkout.php');
}
?>
