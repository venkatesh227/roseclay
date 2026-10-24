<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Contact Messages';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'mark_read':
            $message_id = (int)$_POST['message_id'];
            $sql = "UPDATE contact_messages SET status = 'read' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $message_id);
            $stmt->execute();
            redirect('messages.php');
            break;
            
        case 'mark_replied':
            $message_id = (int)$_POST['message_id'];
            $sql = "UPDATE contact_messages SET status = 'replied' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $message_id);
            $stmt->execute();
            setMessage('Message marked as replied', 'success');
            redirect('messages.php');
            break;
            
        case 'delete':
            $message_id = (int)$_POST['message_id'];
            $sql = "DELETE FROM contact_messages WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $message_id);
            
            if ($stmt->execute()) {
                setMessage('Message deleted successfully', 'success');
            } else {
                setMessage('Error deleting message', 'error');
            }
            redirect('messages.php');
            break;
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';

// Get messages
if ($status_filter == 'all') {
    $messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
} else {
    $messages = $conn->query("SELECT * FROM contact_messages WHERE status = '$status_filter' ORDER BY created_at DESC");
}

// Get statistics
$stats = [];
$stats['total'] = $conn->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count'];
$stats['new'] = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")->fetch_assoc()['count'];
$stats['read'] = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'")->fetch_assoc()['count'];
$stats['replied'] = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'")->fetch_assoc()['count'];

include 'includes/header.php';
?>

<h2 style="margin-bottom: 2rem;">Contact Messages</h2>

<!-- Message Stats -->
<div class="admin-stats" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['total']; ?></h3>
            <p>Total Messages</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-envelope-open"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['new']; ?></h3>
            <p>New Messages</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['read']; ?></h3>
            <p>Read</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['replied']; ?></h3>
            <p>Replied</p>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="background: white; padding: 1rem; border-radius: 12px 12px 0 0; display: flex; gap: 1rem; border-bottom: 2px solid #f0f0f0;">
    <a href="messages.php?status=all" class="<?php echo $status_filter == 'all' ? 'badge badge-info' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        All (<?php echo $stats['total']; ?>)
    </a>
    <a href="messages.php?status=new" class="<?php echo $status_filter == 'new' ? 'badge badge-warning' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        New (<?php echo $stats['new']; ?>)
    </a>
    <a href="messages.php?status=read" class="<?php echo $status_filter == 'read' ? 'badge badge-info' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Read (<?php echo $stats['read']; ?>)
    </a>
    <a href="messages.php?status=replied" class="<?php echo $status_filter == 'replied' ? 'badge badge-success' : ''; ?>" 
       style="padding: 0.5rem 1rem; text-decoration: none; color: #666;">
        Replied (<?php echo $stats['replied']; ?>)
    </a>
</div>

<!-- Messages Table -->
<div style="background: white; border-radius: 0 0 12px 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($messages->num_rows > 0): ?>
                <?php while($msg = $messages->fetch_assoc()): ?>
                <tr style="<?php echo $msg['status'] == 'new' ? 'background: #fffbf0;' : ''; ?>">
                    <td>
                        <strong><?php echo $msg['first_name'] . ' ' . $msg['last_name']; ?></strong>
                        <?php if ($msg['status'] == 'new'): ?>
                        <span class="badge badge-warning" style="margin-left: 0.5rem;">New</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $msg['email']; ?></td>
                    <td><?php echo $msg['phone'] ?? '-'; ?></td>
                    <td><strong><?php echo $msg['subject']; ?></strong></td>
                    <td>
                        <?php 
                        $preview = substr($msg['message'], 0, 50);
                        echo $preview . (strlen($msg['message']) > 50 ? '...' : '');
                        ?>
                        <button onclick='viewMessage(<?php echo json_encode($msg); ?>)' 
                                style="background: none; border: none; color: #F790B2; cursor: pointer; text-decoration: underline;">
                            View Full
                        </button>
                    </td>
                    <td>
                        <?php
                        $badge_class = 'warning';
                        if ($msg['status'] == 'read') $badge_class = 'info';
                        elseif ($msg['status'] == 'replied') $badge_class = 'success';
                        ?>
                        <span class="badge badge-<?php echo $badge_class; ?>">
                            <?php echo ucfirst($msg['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                    <td>
                        <?php if ($msg['status'] == 'new'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn-admin btn-admin-secondary" 
                                    style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-eye"></i> Mark Read
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if ($msg['status'] != 'replied'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_replied">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn-admin btn-admin-success" 
                                    style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-check"></i> Mark Replied
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this message?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn-admin btn-admin-danger" 
                                    style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- View Message Modal -->
<div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="max-width: 700px; margin: 4rem auto; background: white; border-radius: 16px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="margin: 0;">Message Details</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div id="messageContent"></div>
        
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #dee2e6;">
            <a href="#" id="emailLink" class="btn-admin btn-admin-primary" target="_blank">
                <i class="fas fa-reply"></i> Reply via Email
            </a>
            <button onclick="closeModal()" class="btn-admin btn-admin-secondary">Close</button>
        </div>
    </div>
</div>

<script>
function viewMessage(msg) {
    const content = `
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
            <p><strong>From:</strong> ${msg.first_name} ${msg.last_name}</p>
            <p><strong>Email:</strong> ${msg.email}</p>
            <p><strong>Phone:</strong> ${msg.phone || 'Not provided'}</p>
            <p><strong>Subject:</strong> ${msg.subject}</p>
            <p><strong>Date:</strong> ${new Date(msg.created_at).toLocaleString()}</p>
        </div>
        
        <h3>Message:</h3>
        <div style="background: white; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 8px; white-space: pre-wrap;">
${msg.message}
        </div>
    `;
    
    document.getElementById('messageContent').innerHTML = content;
    document.getElementById('emailLink').href = `mailto:${msg.email}?subject=Re: ${encodeURIComponent(msg.subject)}`;
    document.getElementById('messageModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('messageModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
