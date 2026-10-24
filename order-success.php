<?php
session_start();
require_once 'config.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order_number = isset($_GET['order']) ? sanitize($_GET['order']) : '';

if (!$order_id && !$order_number) {
    redirect('index.php');
}

// Get order details
if ($order_id) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('index.php');
}

$order = $result->fetch_assoc();

// Get order items
$sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$order_items = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - RoseClay</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="pages-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 3rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: white;
        }
        .order-details {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin-bottom: 1rem;">Order Confirmed!</h1>
        <p style="font-size: 1.125rem; color: #666; margin-bottom: 2rem;">
            Thank you for your order. We've received your order and will process it shortly.
        </p>
        
        <div class="order-details">
            <h2 style="margin-bottom: 1.5rem;">Order Details</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <strong>Order Number:</strong><br>
                    <span style="color: #F790B2; font-size: 1.25rem; font-weight: 700;"><?php echo $order['order_number']; ?></span>
                </div>
                <div>
                    <strong>Order Date:</strong><br>
                    <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?> | 
                <strong>Status:</strong> <span style="color: <?php echo $order['payment_status']=='Completed' ? 'green' : 'orange'; ?>"><?php echo $order['payment_status']; ?></span>
            </div>

            <div style="border-top: 1px solid #dee2e6; padding-top: 1.5rem; margin-top: 1.5rem;">
                <h3>Items Ordered:</h3>
                <table style="width: 100%; margin-top: 1rem;">
                    <?php while($item = $order_items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td>Qty: <?php echo $item['quantity']; ?></td>
                        <td style="text-align: right;"><?php echo formatPrice($item['total']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr style="border-top: 2px solid #dee2e6;">
                        <td colspan="2"><strong>Total:</strong></td>
                        <td style="text-align: right;"><strong><?php echo formatPrice($order['total']); ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <div style="border-top: 1px solid #dee2e6; padding-top: 1.5rem; margin-top: 1.5rem;">
                <h3>Shipping Address:</h3>
                <p style="margin-top: 0.5rem;">
                    <?php echo $order['first_name'] . ' ' . $order['last_name']; ?><br>
                    <?php echo $order['address']; ?><br>
                    <?php if($order['apartment']): echo $order['apartment'] . '<br>'; endif; ?>
                    <?php echo $order['city'] . ', ' . $order['state'] . ' ' . $order['zip_code']; ?><br>
                    <?php echo $order['country']; ?>
                </p>
            </div>
        </div>
        
        <p style="color: #666; margin-bottom: 2rem;">
            A confirmation email has been sent to <strong><?php echo $order['email']; ?></strong>
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>
</body>
</html>
