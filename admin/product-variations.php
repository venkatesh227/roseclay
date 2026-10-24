<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Manage Product Variations';

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Get product details
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product = $product_query->get_result()->fetch_assoc();

if (!$product) {
    setMessage('Product not found', 'error');
    redirect('products.php');
}

// Handle Add/Edit/Delete Variations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_variation':
                $variation_name = sanitize($_POST['variation_name']);
                $variation_value = sanitize($_POST['variation_value']);
                $price = (float)$_POST['price'];
                $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : NULL;
                $stock = (int)$_POST['stock'];
                $sku = sanitize($_POST['sku']);
                $is_default = isset($_POST['is_default']) ? 1 : 0;
                
                // If this is set as default, unset other defaults
                if ($is_default) {
                    $conn->query("UPDATE product_variations SET is_default = 0 WHERE product_id = $product_id");
                }
                
                $sql = "INSERT INTO product_variations (product_id, variation_name, variation_value, price, sale_price, stock, sku, is_default, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issddiis", $product_id, $variation_name, $variation_value, $price, $sale_price, $stock, $sku, $is_default);
                
                if ($stmt->execute()) {
                    setMessage('Variation added successfully', 'success');
                } else {
                    setMessage('Error adding variation: ' . $conn->error, 'error');
                }
                redirect("product-variations.php?product_id=$product_id");
                break;
                
            case 'edit_variation':
                $id = (int)$_POST['id'];
                $variation_name = sanitize($_POST['variation_name']);
                $variation_value = sanitize($_POST['variation_value']);
                $price = (float)$_POST['price'];
                $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : NULL;
                $stock = (int)$_POST['stock'];
                $sku = sanitize($_POST['sku']);
                $is_default = isset($_POST['is_default']) ? 1 : 0;
                
                // If this is set as default, unset other defaults
                if ($is_default) {
                    $conn->query("UPDATE product_variations SET is_default = 0 WHERE product_id = $product_id AND id != $id");
                }
                
                $sql = "UPDATE product_variations SET variation_name=?, variation_value=?, price=?, sale_price=?, stock=?, sku=?, is_default=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssddisii", $variation_name, $variation_value, $price, $sale_price, $stock, $sku, $is_default, $id);
                
                if ($stmt->execute()) {
                    setMessage('Variation updated successfully', 'success');
                } else {
                    setMessage('Error updating variation', 'error');
                }
                redirect("product-variations.php?product_id=$product_id");
                break;
                
            case 'delete_variation':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM product_variations WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    setMessage('Variation deleted successfully', 'success');
                } else {
                    setMessage('Error deleting variation', 'error');
                }
                redirect("product-variations.php?product_id=$product_id");
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                $sql = "UPDATE product_variations SET status = NOT status WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                redirect("product-variations.php?product_id=$product_id");
                break;
        }
    }
}

// Get all variations for this product
$variations = $conn->query("SELECT * FROM product_variations WHERE product_id = $product_id ORDER BY variation_value ASC");

include 'includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="products.php" style="color: #F790B2; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

<div style="background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="margin: 0 0 0.5rem 0; color: #2c2c2c;">Product: <?php echo $product['name']; ?></h3>
    <p style="margin: 0; color: #666;">SKU: <?php echo $product['sku']; ?> | Base Price: <?php echo formatPrice($product['price']); ?></p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Product Variations (Weight Options)</h2>
    <button class="btn-admin btn-admin-primary" onclick="showAddModal()">
        <i class="fas fa-plus"></i> Add New Variation
    </button>
</div>

<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Variation Name</th>
                <th>Weight</th>
                <th>Price</th>
                <th>Sale Price</th>
                <th>Stock</th>
                <th>SKU</th>
                <th>Default</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($variations->num_rows > 0): ?>
                <?php while($variation = $variations->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $variation['variation_name']; ?></strong></td>
                    <td><?php echo $variation['variation_value']; ?></td>
                    <td><?php echo formatPrice($variation['price']); ?></td>
                    <td>
                        <?php if($variation['sale_price']): ?>
                        <span style="color: #27ae60; font-weight: 600;"><?php echo formatPrice($variation['sale_price']); ?></span>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($variation['stock'] > 10): ?>
                        <span class="badge badge-success"><?php echo $variation['stock']; ?></span>
                        <?php elseif($variation['stock'] > 0): ?>
                        <span class="badge badge-warning"><?php echo $variation['stock']; ?></span>
                        <?php else: ?>
                        <span class="badge badge-danger">Out of Stock</span>
                        <?php endif; ?>
                    </td>
                    <td><small style="color: #999;"><?php echo $variation['sku']; ?></small></td>
                    <td>
                        <?php if($variation['is_default']): ?>
                        <span class="badge badge-info">Default</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?php echo $variation['id']; ?>">
                            <button type="submit" class="badge <?php echo $variation['status'] ? 'badge-success' : 'badge-danger'; ?>" 
                                    style="border: none; cursor: pointer;">
                                <?php echo $variation['status'] ? 'Active' : 'Inactive'; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <button class="btn-admin btn-admin-secondary" onclick='editVariation(<?php echo json_encode($variation); ?>)' 
                                style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this variation?');">
                            <input type="hidden" name="action" value="delete_variation">
                            <input type="hidden" name="id" value="<?php echo $variation['id']; ?>">
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

<!-- Add/Edit Variation Modal -->
<div id="variationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 600px; margin: 2rem auto; background: white; border-radius: 16px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 id="modalTitle" style="margin: 0;">Add Variation</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" id="formAction" value="add_variation">
            <input type="hidden" name="id" id="variationId">
            
            <div class="form-group">
                <label>Variation Name * <small>(e.g., "Small - 250gms", "Medium - 500gms")</small></label>
                <input type="text" name="variation_name" id="variationName" placeholder="e.g., Small - 250gms" required>
            </div>
            
            <div class="form-group">
                <label>Weight Value * <small>(e.g., "250gms", "500gms", "1kg")</small></label>
                <input type="text" name="variation_value" id="variationValue" placeholder="e.g., 250gms" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" name="price" id="variationPrice" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Sale Price</label>
                    <input type="number" name="sale_price" id="variationSalePrice" step="0.01">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" id="variationStock" required>
                </div>
                
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" id="variationSku" placeholder="e.g., RC-PLT-001-250" required>
                </div>
            </div>
            
            <div style="margin: 1rem 0;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_default" id="variationDefault">
                    <span>Set as Default Variation <small>(This will be selected by default)</small></span>
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="fas fa-save"></i> Save Variation
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
    document.getElementById('modalTitle').textContent = 'Add Variation';
    document.getElementById('formAction').value = 'add_variation';
    document.getElementById('variationModal').style.display = 'block';
    document.querySelector('.admin-form').reset();
}

function editVariation(variation) {
    document.getElementById('modalTitle').textContent = 'Edit Variation';
    document.getElementById('formAction').value = 'edit_variation';
    document.getElementById('variationId').value = variation.id;
    document.getElementById('variationName').value = variation.variation_name;
    document.getElementById('variationValue').value = variation.variation_value;
    document.getElementById('variationPrice').value = variation.price;
    document.getElementById('variationSalePrice').value = variation.sale_price || '';
    document.getElementById('variationStock').value = variation.stock;
    document.getElementById('variationSku').value = variation.sku;
    document.getElementById('variationDefault').checked = variation.is_default == 1;
    document.getElementById('variationModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('variationModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('variationModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
