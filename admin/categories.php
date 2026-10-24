<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Manage Categories';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitize($_POST['name']);
                $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['name'])));
                $description = sanitize($_POST['description']);
                
                $sql = "INSERT INTO categories (name, slug, description, status) VALUES (?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $name, $slug, $description);
                
                if ($stmt->execute()) {
                    setMessage('Category added successfully', 'success');
                } else {
                    setMessage('Error adding category', 'error');
                }
                redirect('categories.php');
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = sanitize($_POST['name']);
                $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['name'])));
                $description = sanitize($_POST['description']);
                
                $sql = "UPDATE categories SET name=?, slug=?, description=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $name, $slug, $description, $id);
                
                if ($stmt->execute()) {
                    setMessage('Category updated successfully', 'success');
                } else {
                    setMessage('Error updating category', 'error');
                }
                redirect('categories.php');
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Check if category has products
                $check = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = $id");
                $result = $check->fetch_assoc();
                
                if ($result['count'] > 0) {
                    setMessage('Cannot delete category with products', 'error');
                } else {
                    $sql = "DELETE FROM categories WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    
                    if ($stmt->execute()) {
                        setMessage('Category deleted successfully', 'success');
                    } else {
                        setMessage('Error deleting category', 'error');
                    }
                }
                redirect('categories.php');
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                $sql = "UPDATE categories SET status = NOT status WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                redirect('categories.php');
                break;
        }
    }
}

// Get all categories with product count
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count 
                            FROM categories c 
                            LEFT JOIN products p ON c.id = p.category_id 
                            GROUP BY c.id 
                            ORDER BY c.created_at DESC");

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Categories</h2>
    <button class="btn-admin btn-admin-primary" onclick="showAddModal()">
        <i class="fas fa-plus"></i> Add New Category
    </button>
</div>

<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Description</th>
                <th>Products</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($categories->num_rows > 0): ?>
                <?php while($category = $categories->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $category['name']; ?></strong></td>
                    <td><code><?php echo $category['slug']; ?></code></td>
                    <td><?php echo substr($category['description'] ?? '', 0, 50); ?><?php echo strlen($category['description'] ?? '') > 50 ? '...' : ''; ?></td>
                    <td>
                        <span class="badge badge-info"><?php echo $category['product_count']; ?> products</span>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <button type="submit" class="badge <?php echo $category['status'] ? 'badge-success' : 'badge-danger'; ?>" 
                                    style="border: none; cursor: pointer;">
                                <?php echo $category['status'] ? 'Active' : 'Inactive'; ?>
                            </button>
                        </form>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                    <td>
                        <button class="btn-admin btn-admin-secondary" onclick='editCategory(<?php echo json_encode($category); ?>)' 
                                style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
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

<!-- Add/Edit Category Modal -->
<div id="categoryModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="max-width: 600px; margin: 4rem auto; background: white; border-radius: 16px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 id="modalTitle" style="margin: 0;">Add Category</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="categoryId">
            
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" id="categoryName" required>
                <small style="color: #999;">Slug will be auto-generated from name</small>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="categoryDesc" rows="4"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="fas fa-save"></i> Save Category
                </button>
                <button type="button" onclick="closeModal()" class="btn-admin btn-admin-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('formAction').value = 'add';
    document.getElementById('categoryModal').style.display = 'block';
    document.querySelector('.admin-form').reset();
}

function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDesc').value = category.description || '';
    document.getElementById('categoryModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('categoryModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
