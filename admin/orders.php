<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Manage Orders';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $order_id = (int)$_POST['order_id'];
        $status = sanitize($_POST['status']);
        
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            setMessage('Order status updated successfully', 'success');
        } else {
            setMessage('Error updating order status', 'error');
        }
        redirect('orders.php');
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';

// Get orders
if ($status_filter == 'all') {
    $orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
} else {
    $orders = $conn->query("SELECT * FROM orders WHERE status = '$status_filter' ORDER BY created_at DESC");
}

// Get order statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['pending'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$stats['processing'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$stats['shipped'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'")->fetch_assoc()['count'];
$stats['delivered'] = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];

include 'includes/header.php';
?>

<h2 style="margin-bottom: 2rem;">Orders Management</h2>

<!-- Order Stats -->
<div class="admin-stats" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $stats['pending']; ?></h3>
            <p>Pending</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $stats['processing']; ?></h3>
            <p>Processing</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $stats['shipped']; ?></h3>
            <p>Shipped</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <h3><?php echo $stats['delivered']; ?></h3>
            <p>Delivered</p>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="background: white; padding: 1rem; border-radius: 12px 12px 0 0; display: flex; gap: 1rem; border-bottom: 2px solid #f0f0f0;">
    <a href="orders.php?status=all" class="<?php echo $status_filter == 'all' ? 'badge badge-info' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        All Orders (<?php echo $stats['total']; ?>)
    </a>
    <a href="orders.php?status=pending" class="<?php echo $status_filter == 'pending' ? 'badge badge-warning' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Pending (<?php echo $stats['pending']; ?>)
    </a>
    <a href="orders.php?status=processing" class="<?php echo $status_filter == 'processing' ? 'badge badge-info' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Processing (<?php echo $stats['processing']; ?>)
    </a>
    <a href="orders.php?status=shipped" class="<?php echo $status_filter == 'shipped' ? 'badge badge-info' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Shipped (<?php echo $stats['shipped']; ?>)
    </a>
    <a href="orders.php?status=delivered" class="<?php echo $status_filter == 'delivered' ? 'badge badge-success' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Delivered (<?php echo $stats['delivered']; ?>)
    </a>
</div>

<!-- Orders Table -->
<div style="background: white; border-radius: 0 0 12px 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders->num_rows > 0): ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                <?php
                // Get order items count
                $items_count = $conn->query("SELECT COUNT(*) as count FROM order_items WHERE order_id = " . $order['id'])->fetch_assoc()['count'];
                ?>
                <tr>
                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                    <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                    <td><?php echo $order['email']; ?></td>
                    <td><?php echo $items_count; ?> items</td>
                    <td><strong><?php echo formatPrice($order['total']); ?></strong></td>
                    <td><?php echo ucfirst($order['payment_method']); ?></td>
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
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td>
                        <button class="btn-admin btn-admin-secondary" onclick='viewOrder(<?php echo $order['id']; ?>)' 
                                style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-admin btn-admin-primary" onclick='updateStatus(<?php echo $order['id']; ?>, "<?php echo $order['status']; ?>")' 
                                style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- View Order Modal -->
<div id="viewOrderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 800px; margin: 2rem auto; background: white; border-radius: 16px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="margin: 0;">Order Details</h2>
            <button onclick="closeViewModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="orderDetails"></div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="max-width: 500px; margin: 4rem auto; background: white; border-radius: 16px; padding: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">Update Order Status</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" id="statusOrderId">
            
            <div class="form-group">
                <label>Select Status</label>
                <select name="status" id="orderStatus" class="admin-form" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 8px;">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn-admin btn-admin-primary">Update Status</button>
                <button type="button" onclick="closeStatusModal()" class="btn-admin btn-admin-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function viewOrder(orderId) {
    fetch('../api/get-order-details.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                        <h3>Order #${data.order.order_number}</h3>
                        <p><strong>Customer:</strong> ${data.order.first_name} ${data.order.last_name}</p>
                        <p><strong>Email:</strong> ${data.order.email}</p>
                        <p><strong>Phone:</strong> ${data.order.phone}</p>
                        <p><strong>Status:</strong> <span class="badge badge-info">${data.order.status}</span></p>
                    </div>
                    
                    <h3>Shipping Address</h3>
                    <p>${data.order.address}<br>
                    ${data.order.apartment ? data.order.apartment + '<br>' : ''}
                    ${data.order.city}, ${data.order.state} ${data.order.zip_code}<br>
                    ${data.order.country}</p>
                    
                    <h3>Order Items</h3>
                    <table style="width: 100%; margin-top: 1rem;">
                        ${data.items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>Qty: ${item.quantity}</td>
                                <td style="text-align: right;">₹${parseFloat(item.total).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                        <tr style="border-top: 2px solid #dee2e6; font-weight: bold;">
                            <td colspan="2">Total</td>
                            <td style="text-align: right;">₹${parseFloat(data.order.total).toFixed(2)}</td>
                        </tr>
                    </table>
                `;
                document.getElementById('orderDetails').innerHTML = html;
                document.getElementById('viewOrderModal').style.display = 'block';
            }
        });
}

function closeViewModal() {
    document.getElementById('viewOrderModal').style.display = 'none';
}

function updateStatus(orderId, currentStatus) {
    document.getElementById('statusOrderId').value = orderId;
    document.getElementById('orderStatus').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
