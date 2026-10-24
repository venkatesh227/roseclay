<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Dashboard';

// Get statistics
$stats = [];
$stats['products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$stats['orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['users'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
$revenue_result = $conn->query("SELECT SUM(total) as revenue FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $revenue_result->fetch_assoc()['revenue'] ?? 0;

// Get recent orders
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");

include 'includes/header.php';
?>

<div class="admin-stats">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['products']; ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['orders']; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['users']; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatPrice($stats['revenue']); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Recent Orders</h2>
        <a href="orders.php" class="btn-admin btn-admin-primary">View All Orders</a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recent_orders->num_rows > 0): ?>
                <?php while($order = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                    <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                    <td><?php echo $order['email']; ?></td>
                    <td><strong><?php echo formatPrice($order['total']); ?></strong></td>
                    <td>
                        <?php
                        $badge_class = 'info';
                        if ($order['status'] == 'delivered') $badge_class = 'success';
                        elseif ($order['status'] == 'cancelled') $badge_class = 'danger';
                        elseif ($order['status'] == 'processing') $badge_class = 'warning';
                        ?>
                        <span class="badge badge-<?php echo $badge_class; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn-admin btn-admin-secondary" style="font-size: 0.875rem;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
