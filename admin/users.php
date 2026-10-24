<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Manage Users';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'toggle_admin':
            $user_id = (int)$_POST['user_id'];
            $sql = "UPDATE users SET is_admin = NOT is_admin WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                setMessage('User role updated successfully', 'success');
            } else {
                setMessage('Error updating user role', 'error');
            }
            redirect('users.php');
            break;
            
        case 'delete':
            $user_id = (int)$_POST['user_id'];
            
            // Don't allow deleting yourself
            if ($user_id == $_SESSION['admin_id']) {
                setMessage('Cannot delete your own account', 'error');
                redirect('users.php');
                break;
            }
            
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                setMessage('User deleted successfully', 'success');
            } else {
                setMessage('Error deleting user', 'error');
            }
            redirect('users.php');
            break;
    }
}

// Get filter
$role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : 'all';

// Get users
if ($role_filter == 'admin') {
    $users = $conn->query("SELECT * FROM users WHERE is_admin = 1 ORDER BY created_at DESC");
} elseif ($role_filter == 'customer') {
    $users = $conn->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
} else {
    $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
}

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$stats['admins'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1")->fetch_assoc()['count'];
$stats['customers'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];

include 'includes/header.php';
?>

<h2 style="margin-bottom: 2rem;">Users Management</h2>

<!-- User Stats -->
<div class="admin-stats" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['admins']; ?></h3>
            <p>Administrators</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-user"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['customers']; ?></h3>
            <p>Customers</p>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="background: white; padding: 1rem; border-radius: 12px 12px 0 0; display: flex; gap: 1rem; border-bottom: 2px solid #f0f0f0;">
    <a href="users.php?role=all" class="<?php echo $role_filter == 'all' ? 'badge badge-info' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        All Users (<?php echo $stats['total']; ?>)
    </a>
    <a href="users.php?role=admin" class="<?php echo $role_filter == 'admin' ? 'badge badge-primary' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Admins (<?php echo $stats['admins']; ?>)
    </a>
    <a href="users.php?role=customer" class="<?php echo $role_filter == 'customer' ? 'badge badge-success' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Customers (<?php echo $stats['customers']; ?>)
    </a>
</div>

<!-- Users Table -->
<div style="background: white; border-radius: 0 0 12px 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Orders</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while($user = $users->fetch_assoc()): ?>
                <?php
                // Get user's order count
                $order_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = " . $user['id'])->fetch_assoc()['count'];
                ?>
                <tr>
                    <td>
                        <strong><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></strong>
                        <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                        <span class="badge badge-info" style="margin-left: 0.5rem;">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone'] ?? '-'; ?></td>
                    <td>
                        <?php if ($user['is_admin']): ?>
                        <span class="badge badge-danger">
                            <i class="fas fa-shield-alt"></i> Administrator
                        </span>
                        <?php else: ?>
                        <span class="badge badge-success">
                            <i class="fas fa-user"></i> Customer
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($order_count > 0): ?>
                        <span class="badge badge-info"><?php echo $order_count; ?> orders</span>
                        <?php else: ?>
                        <span style="color: #999;">No orders</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Toggle admin role for this user?');">
                            <input type="hidden" name="action" value="toggle_admin">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn-admin btn-admin-secondary" 
                                    style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-user-cog"></i> Toggle Role
                            </button>
                        </form>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn-admin btn-admin-danger" 
                                    style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
