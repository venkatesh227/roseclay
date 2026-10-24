<?php
session_start();
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Get all orders
$orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - RoseClay</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="my-account.php">My Account</a>
            <i class="fas fa-chevron-right"></i>
            <span>My Orders</span>
        </div>
    </div>

    <!-- Account Section -->
    <section class="account-section">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="account-user">
                        <div class="account-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                        <p><?php echo $user['email']; ?></p>
                    </div>
                    <nav class="account-nav">
                        <a href="my-account.php">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="my-orders.php" class="active">
                            <i class="fas fa-shopping-bag"></i> My Orders
                        </a>
                        <a href="my-profile.php">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </a>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </aside>

                <!-- Main Content -->
                <div class="account-main">
                    <h1>My Orders</h1>
                    <p>View and track your orders</p>

                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($order = $orders->fetch_assoc()): ?>
                        <?php
                        // Get order items
                        $order_items = $conn->query("SELECT * FROM order_items WHERE order_id = " . $order['id']);
                        ?>
                        
                        <!-- Order Card -->
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo $order['order_number']; ?></h3>
                                    <p>Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="order-status">
                                    <?php
                                    $badge_class = 'info';
                                    if ($order['status'] == 'delivered') $badge_class = 'success';
                                    elseif ($order['status'] == 'cancelled') $badge_class = 'danger';
                                    elseif ($order['status'] == 'pending') $badge_class = 'warning';
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-items">
                                <?php while($item = $order_items->fetch_assoc()): ?>
                                <div class="order-item">
                                    <div class="order-item-details">
                                        <h4><?php echo $item['product_name']; ?></h4>
                                        <p>Quantity: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></p>
                                    </div>
                                    <div class="order-item-total">
                                        <?php echo formatPrice($item['total']); ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>

                            <div class="order-footer">
                                <div class="order-total">
                                    <span>Order Total:</span>
                                    <strong><?php echo formatPrice($order['total']); ?></strong>
                                </div>
                                <div class="order-actions">
                                    <button class="btn btn-secondary btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <?php if ($order['status'] == 'delivered'): ?>
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-redo"></i> Reorder
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>No orders yet</h3>
                            <p>You haven't placed any orders yet</p>
                            <a href="products.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Details Modal -->
    <div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
        <div style="max-width: 800px; margin: 2rem auto; background: white; border-radius: 16px; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Order Details</h2>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div id="orderDetailsContent"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <script>
        function viewOrderDetails(orderId) {
            // Fetch order details via AJAX
            fetch('api/get-order-details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showOrderModal(data.order, data.items);
                    }
                });
        }

        function showOrderModal(order, items) {
            let html = `
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                    <h3>Order #${order.order_number}</h3>
                    <p><strong>Status:</strong> ${order.status}</p>
                    <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                </div>
                
                <h3>Shipping Address</h3>
                <p>${order.address}<br>
                ${order.apartment ? order.apartment + '<br>' : ''}
                ${order.city}, ${order.state} ${order.zip_code}<br>
                ${order.country}</p>
                
                <h3>Order Items</h3>
                <table style="width: 100%; margin-top: 1rem;">
                    ${items.map(item => `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>Qty: ${item.quantity}</td>
                            <td style="text-align: right;">${formatPrice(item.total)}</td>
                        </tr>
                    `).join('')}
                    <tr style="border-top: 2px solid #dee2e6; font-weight: bold;">
                        <td colspan="2">Total</td>
                        <td style="text-align: right;">${formatPrice(order.total)}</td>
                    </tr>
                </table>
            `;
            
            document.getElementById('orderDetailsContent').innerHTML = html;
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
    </script>
</body>
</html>
