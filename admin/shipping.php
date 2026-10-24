<?php
session_start();
require_once '../config.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle Form Submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_method'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $cost = floatval($_POST['cost']);
        $threshold = !empty($_POST['threshold']) ? floatval($_POST['threshold']) : NULL;
        $status = sanitize($_POST['status']);
        
        $stmt = $conn->prepare("INSERT INTO shipping_methods (name, description, cost, free_shipping_threshold, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdds", $name, $description, $cost, $threshold, $status);
        
        if ($stmt->execute()) {
            $message = "Shipping method added successfully!";
        } else {
            $error = "Error adding method: " . $conn->error;
        }
    }
    elseif (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        if ($conn->query("DELETE FROM shipping_methods WHERE id = $id")) {
            $message = "Shipping method deleted successfully!";
        } else {
            $error = "Error deleting method.";
        }
    }
    elseif (isset($_POST['toggle_status_id'])) {
        $id = intval($_POST['toggle_status_id']);
        if ($conn->query("UPDATE shipping_methods SET status = IF(status='active', 'inactive', 'active') WHERE id = $id")) {
            $message = "Status updated!";
        } else {
            $error = "Error updating status.";
        }
    }
}

$page_title = 'Shipping Methods';
include 'includes/header.php';
?>

<div class="main-content">
    <div class="header-actions">
        <h1>Manage Shipping Methods</h1>
        <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='block'">
            <i class="fas fa-plus"></i> Add New Method
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Free Shipping Threshold</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $result = $conn->query("SELECT * FROM shipping_methods ORDER BY id ASC");
                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>₹<?php echo number_format($row['cost'], 2); ?></td>
                        <td>
                            <?php echo $row['free_shipping_threshold'] ? '₹' . number_format($row['free_shipping_threshold'], 2) : '-'; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $row['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="toggle_status_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-icon" title="Toggle Status">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this method?');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-icon delete-btn" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No shipping methods found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="modal-content" style="background:white; width:500px; margin: 10% auto; padding:2rem; border-radius:8px; position:relative;">
        <span onclick="document.getElementById('addModal').style.display='none'" style="position:absolute; right:1rem; top:1rem; cursor:pointer; font-size:1.5rem;">&times;</span>
        <h2>Add Shipping Method</h2>
        <form method="POST">
            <div class="form-group">
                <label>Method Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Express Delivery">
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" placeholder="e.g. 1-2 Business Days">
            </div>
            <div class="form-group">
                <label>Cost (₹)</label>
                <input type="number" name="cost" step="0.01" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Free Shipping Threshold (₹) (Optional)</label>
                <input type="number" name="threshold" step="0.01" class="form-control" placeholder="Leave empty if never free">
                <small>Order total above this amount will have free shipping.</small>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="form-actions" style="margin-top:1rem; text-align:right;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" name="add_method" class="btn btn-primary">Add Method</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
