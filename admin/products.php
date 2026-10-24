<?php
session_start();
require_once '../config.php';
requireAdmin();

$page_title = 'Manage Products';

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $category_id = (int)$_POST['category_id'];
                $name = sanitize($_POST['name']);
                $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['name'])));
                $description = sanitize($_POST['description']);
                $short_description = sanitize($_POST['short_description']);
                $price = (float)$_POST['price'];
                $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : NULL;
                $stock = (int)$_POST['stock'];
                $sku = sanitize($_POST['sku']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_new = isset($_POST['is_new']) ? 1 : 0;
                
                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../uploads/products/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
                }

                // Handle gallery upload
                $gallery = [];
                if (isset($_FILES['gallery'])) {
                    $target_dir = "../uploads/products/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $total = count($_FILES['gallery']['name']);
                    for ($i = 0; $i < $total; $i++) {
                        if ($_FILES['gallery']['error'][$i] == 0) {
                            $gallery_img = time() . '_' . $i . '_' . basename($_FILES['gallery']['name'][$i]);
                            if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $target_dir . $gallery_img)) {
                                $gallery[] = $gallery_img;
                            }
                        }
                    }
                }
                $gallery_json = !empty($gallery) ? json_encode($gallery) : '';
                
                $sql = "INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, stock, sku, image, gallery, is_featured, is_new, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssddisssii", $category_id, $name, $slug, $description, $short_description, $price, $sale_price, $stock, $sku, $image, $gallery_json, $is_featured, $is_new);
                
                if ($stmt->execute()) {
                    setMessage('Product added successfully', 'success');
                } else {
                    setMessage('Error adding product', 'error');
                }
                redirect('products.php');
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $category_id = (int)$_POST['category_id'];
                $name = sanitize($_POST['name']);
                $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['name'])));
                $description = sanitize($_POST['description']);
                $short_description = sanitize($_POST['short_description']);
                $price = (float)$_POST['price'];
                $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : NULL;
                $stock = (int)$_POST['stock'];
                $sku = sanitize($_POST['sku']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_new = isset($_POST['is_new']) ? 1 : 0;
                
                // Handle image upload
                $target_dir = "../uploads/products/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $image_sql = "";
                $types = "issssddisiii";
                $params = [$category_id, $name, $slug, $description, $short_description, $price, $sale_price, $stock, $sku, $is_featured, $is_new, $id];

                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $image = time() . '_' . basename($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
                    $image_sql = ", image=?";
                    $types = "issssddissiii"; // Added 's' for image
                    // Insert image into params at correct position (before is_featured)
                    array_splice($params, 9, 0, $image);
                }

                // Handle gallery upload (Append to existing or Replace? For now, if uploaded, it replaces or adds. 
                // To allow appending, we'd need to fetch existing first. Let's fetch existing.)
                $gallery_sql = "";
                $gallery = [];
                if (isset($_FILES['gallery']) && array_filter($_FILES['gallery']['name'])) {
                    // Fetch existing gallery
                    $curr_res = $conn->query("SELECT gallery FROM products WHERE id=$id");
                    $curr_row = $curr_res->fetch_assoc();
                    $existing_gallery = $curr_row['gallery'] ? json_decode($curr_row['gallery'], true) : [];
                    if (!is_array($existing_gallery)) $existing_gallery = [];

                    $total = count($_FILES['gallery']['name']);
                    for ($i = 0; $i < $total; $i++) {
                        if ($_FILES['gallery']['error'][$i] == 0) {
                            $gallery_img = time() . '_' . $i . '_' . basename($_FILES['gallery']['name'][$i]);
                            if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $target_dir . $gallery_img)) {
                                $existing_gallery[] = $gallery_img;
                            }
                        }
                    }
                    $gallery_json = json_encode($existing_gallery);
                    $gallery_sql = ", gallery=?";
                    
                    // Adjust params for gallery
                    // Current params structure depends on if image was added.
                    // If image added: cat, name, slug, desc, short, price, sale, stock, sku, image, is_feat, is_new, id
                    // If no image: cat, name, slug, desc, short, price, sale, stock, sku, is_feat, is_new, id
                    
                    // We need to inject gallery before is_featured.
                    // Find index of is_featured (it's always 3rd from last: feat, new, id at end)
                    $insert_pos = count($params) - 3; 
                    array_splice($params, $insert_pos, 0, $gallery_json);
                    
                    // Add 's' to types string at corresponding position
                    // Types string length is currently 12 (no image) or 13 (image).
                    // is_featured is at index 9 (no image) or 10 (image).
                    // We need to insert 's' at that index.
                    $type_insert_pos = count($params) - 4; // -1 because we just added one param, so array is longer. -3 for the end vars.
                    // Wait, easy way:
                    // If image was added, types has '...siii'. We want '...ssiii'.
                    // If no image, types has '...siii'. We want '...ssiii'. 
                    // Basically just add 's' before the last 3 chars.
                    $types = substr($types, 0, -3) . 's' . substr($types, -3);
                }

                $sql = "UPDATE products SET category_id=?, name=?, slug=?, description=?, short_description=?, price=?, sale_price=?, stock=?, sku=?, is_featured=?, is_new=?" . $image_sql . $gallery_sql . " WHERE id=?";
                // Re-order SQL to match params? No, params order matches injection.
                // Original SQL ends with `is_featured=?, is_new=? WHERE id=?`.
                // We are injecting columns into the SET clause.
                // The correct SQL structure must be built carefully.
                // Base: ...sku=?, is_featured=?, is_new=?
                // If image: ...sku=?, is_featured=?, is_new=?, image=? (No, order matters for bind_param)
                
                // Let's rebuild SQL strictly based on what we have.
                $sql_base = "UPDATE products SET category_id=?, name=?, slug=?, description=?, short_description=?, price=?, sale_price=?, stock=?, sku=?";
                $sql_end = ", is_featured=?, is_new=? WHERE id=?";
                
                // We need to maintain the param array order: 
                // [cat, name, slug, desc, short, price, sale, stock, sku, (image?), (gallery?), feat, new, id]
                
                $sql_middle = "";
                if ($image_sql) $sql_middle .= ", image=?";
                if ($gallery_sql) $sql_middle .= ", gallery=?";
                
                $sql = $sql_base . $sql_middle . $sql_end;
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);

                
                if ($stmt->execute()) {
                    setMessage('Product updated successfully', 'success');
                } else {
                    setMessage('Error updating product', 'error');
                }
                redirect('products.php');
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM products WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    setMessage('Product deleted successfully', 'success');
                } else {
                    setMessage('Error deleting product', 'error');
                }
                redirect('products.php');
                break;
                
            case 'toggle_status':
                $id = (int)$_POST['id'];
                $sql = "UPDATE products SET status = NOT status WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                redirect('products.php');
                break;
        }
    }
}

// Get all products
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name");

include 'includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="margin: 0;">Products</h2>
    <button class="btn-admin btn-admin-primary" onclick="showAddModal()">
        <i class="fas fa-plus"></i> Add New Product
    </button>
</div>

<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Sale Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products->num_rows > 0): ?>
                <?php while($product = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if($product['image']): ?>
                        <img src="../uploads/products/<?php echo $product['image']; ?>" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                        <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-image" style="color: #ccc;"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo $product['name']; ?></strong><br>
                        <small style="color: #999;">SKU: <?php echo $product['sku']; ?></small>
                    </td>
                    <td><?php echo $product['category_name'] ?? 'Uncategorized'; ?></td>
                    <td><?php echo formatPrice($product['price']); ?></td>
                    <td>
                        <?php if($product['sale_price']): ?>
                        <span style="color: #27ae60; font-weight: 600;"><?php echo formatPrice($product['sale_price']); ?></span>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($product['stock'] > 10): ?>
                        <span class="badge badge-success"><?php echo $product['stock']; ?></span>
                        <?php elseif($product['stock'] > 0): ?>
                        <span class="badge badge-warning"><?php echo $product['stock']; ?></span>
                        <?php else: ?>
                        <span class="badge badge-danger">Out of Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="badge <?php echo $product['status'] ? 'badge-success' : 'badge-danger'; ?>" 
                                    style="border: none; cursor: pointer;">
                                <?php echo $product['status'] ? 'Active' : 'Inactive'; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <?php if($product['is_featured']): ?>
                        <i class="fas fa-star" style="color: #f39c12;"></i>
                        <?php endif; ?>
                        <?php if($product['is_new']): ?>
                        <span class="badge badge-info">New</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="product-variations.php?product_id=<?php echo $product['id']; ?>" 
                           class="btn-admin btn-admin-info" 
                           style="font-size: 0.875rem; padding: 0.375rem 0.75rem; text-decoration: none; display: inline-block;">
                            <i class="fas fa-weight"></i> Variations
                        </a>
                        <button class="btn-admin btn-admin-secondary" onclick='editProduct(<?php echo json_encode($product); ?>)' 
                                style="font-size: 0.875rem; padding: 0.375rem 0.75rem;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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

<!-- Add/Edit Product Modal -->
<div id="productModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 800px; margin: 2rem auto; background: white; border-radius: 16px; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 id="modalTitle" style="margin: 0;">Add Product</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="productId">
            
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" id="productName" required>
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" id="productCategory" required>
                    <option value="">Select Category</option>
                    <?php 
                    $categories->data_seek(0);
                    while($cat = $categories->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Short Description</label>
                <input type="text" name="short_description" id="productShortDesc" maxlength="500">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="productDesc" rows="4"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Regular Price *</label>
                    <input type="number" name="price" id="productPrice" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Sale Price</label>
                    <input type="number" name="sale_price" id="productSalePrice" step="0.01">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" id="productStock" required>
                </div>
                
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" id="productSku" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label>Gallery Images (Multiple)</label>
                <input type="file" name="gallery[]" accept="image/*" multiple>
            </div>
            
            <div style="display: flex; gap: 2rem; margin: 1rem 0;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_featured" id="productFeatured">
                    <span>Featured Product</span>
                </label>
                
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_new" id="productNew">
                    <span>New Product</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn-admin btn-admin-primary">
                    <i class="fas fa-save"></i> Save Product
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
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('formAction').value = 'add';
    document.getElementById('productModal').style.display = 'block';
    document.querySelector('.admin-form').reset();
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category_id;
    document.getElementById('productShortDesc').value = product.short_description || '';
    document.getElementById('productDesc').value = product.description || '';
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productSalePrice').value = product.sale_price || '';
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productSku').value = product.sku;
    document.getElementById('productFeatured').checked = product.is_featured == 1;
    document.getElementById('productNew').checked = product.is_new == 1;
    document.getElementById('productModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('productModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
