<?php
session_start();
require_once 'config.php';
requireLogin();

// Get user data
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Get order statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id AND status = 'pending'")->fetch_assoc()['count'];
$total_spent = $conn->query("SELECT SUM(total) as total FROM orders WHERE user_id = $user_id AND status != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// Get recent orders
$recent_orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - RoseClay</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>My Account</span>
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
                        <a href="my-account.php" class="active">
                            <i class="fas fa-th-large"></i> Dashboard
                        </a>
                        <a href="my-orders.php">
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
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo $user['first_name']; ?>!</p>

                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_orders; ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $pending_orders; ?></h3>
                                <p>Pending Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo formatPrice($total_spent); ?></h3>
                                <p>Total Spent</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="account-section-box">
                        <div class="section-header">
                            <h2>Recent Orders</h2>
                            <a href="my-orders.php" class="btn btn-secondary">View All</a>
                        </div>

                        <?php if ($recent_orders->num_rows > 0): ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo formatPrice($order['total']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = 'info';
                                            if ($order['status'] == 'delivered') $badge_class = 'success';
                                            elseif ($order['status'] == 'cancelled') $badge_class = 'danger';
                                            elseif ($order['status'] == 'pending') $badge_class = 'warning';
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="my-orders.php" class="btn btn-sm btn-secondary">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>No orders yet</h3>
                            <p>Start shopping to see your orders here</p>
                            <a href="products.php" class="btn btn-primary">Shop Now</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Account Info -->
                    <div class="account-section-box">
                        <div class="section-header">
                            <h2>Account Information</h2>
                            <a href="my-profile.php" class="btn btn-secondary">Edit</a>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Name</label>
                                <p><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Email</label>
                                <p><?php echo $user['email']; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Phone</label>
                                <p><?php echo $user['phone'] ?? 'Not provided'; ?></p>
                            </div>
                            <div class="info-item">
                                <label>Member Since</label>
                                <p><?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
