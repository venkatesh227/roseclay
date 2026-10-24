<?php
/**
 * Product Variations Database Setup Script
 * Run this file once to create the product_variations table
 */

require_once 'config.php';

echo "<h2>Product Variations Database Setup</h2>";
echo "<p>Creating product_variations table...</p>";

// Create Product Variations Table
$sql_create_table = "CREATE TABLE IF NOT EXISTS product_variations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variation_name VARCHAR(100) NOT NULL COMMENT 'e.g., 250gms, 500gms, 1kg',
    variation_value VARCHAR(100) NOT NULL COMMENT 'Weight value',
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2) NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    is_default TINYINT(1) DEFAULT 0 COMMENT '1 if this is the default variation',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_status (status)
) ENGINE=InnoDB";

if ($conn->query($sql_create_table)) {
    echo "<p style='color: green;'>✅ Table 'product_variations' created successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
    exit;
}

// Check if order_items table needs variation columns
$check_columns = $conn->query("SHOW COLUMNS FROM order_items LIKE 'variation_id'");
if ($check_columns->num_rows == 0) {
    echo "<p>Adding variation support to order_items table...</p>";
    
    $sql_alter_1 = "ALTER TABLE order_items ADD COLUMN variation_id INT NULL AFTER product_id";
    $sql_alter_2 = "ALTER TABLE order_items ADD COLUMN variation_name VARCHAR(100) NULL AFTER product_name";
    $sql_alter_3 = "ALTER TABLE order_items ADD FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL";
    
    if ($conn->query($sql_alter_1) && $conn->query($sql_alter_2) && $conn->query($sql_alter_3)) {
        echo "<p style='color: green;'>✅ Order items table updated successfully!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Warning: Could not update order_items table. This is optional.</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Order items table already has variation support.</p>";
}

// Insert sample variations
echo "<p>Adding sample variations...</p>";

$sample_variations = [
    // Product 1: Terracotta Planter Set
    [1, 'Small - 250gms', '250gms', 49.00, 35.00, 30, 'RC-PLT-001-250', 1],
    [1, 'Medium - 500gms', '500gms', 70.00, 49.00, 25, 'RC-PLT-001-500', 0],
    [1, 'Large - 1kg', '1kg', 120.00, 89.00, 15, 'RC-PLT-001-1000', 0],
    
    // Product 2: Handmade Clay Bowl Set
    [2, 'Small Set - 250gms', '250gms', 65.00, null, 20, 'RC-DIN-001-250', 1],
    [2, 'Medium Set - 500gms', '500gms', 85.00, null, 15, 'RC-DIN-001-500', 0],
    [2, 'Large Set - 1kg', '1kg', 125.00, null, 10, 'RC-DIN-001-1000', 0],
    
    // Product 4: Artisan Clay Mug
    [4, '250gms', '250gms', 24.00, 18.00, 60, 'RC-MUG-001-250', 1],
    [4, '500gms', '500gms', 32.00, 24.00, 50, 'RC-MUG-001-500', 0],
    [4, '1kg', '1kg', 55.00, 42.00, 30, 'RC-MUG-001-1000', 0]
];

$insert_sql = "INSERT INTO product_variations (product_id, variation_name, variation_value, price, sale_price, stock, sku, is_default) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);

$success_count = 0;
foreach ($sample_variations as $variation) {
    $stmt->bind_param("issddiis", 
        $variation[0], // product_id
        $variation[1], // variation_name
        $variation[2], // variation_value
        $variation[3], // price
        $variation[4], // sale_price
        $variation[5], // stock
        $variation[6], // sku
        $variation[7]  // is_default
    );
    
    if ($stmt->execute()) {
        $success_count++;
    }
}

echo "<p style='color: green;'>✅ Added $success_count sample variations!</p>";

echo "<hr>";
echo "<h3 style='color: green;'>✅ Setup Complete!</h3>";
echo "<p><strong>What's Next?</strong></p>";
echo "<ul>";
echo "<li>Go to <a href='admin/products.php'>Admin Products Page</a></li>";
echo "<li>Click the blue 'Variations' button on any product</li>";
echo "<li>You'll see the sample variations already added</li>";
echo "<li>You can add more variations or edit existing ones</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Sample Variations Added:</strong></p>";
echo "<ul>";
echo "<li>Product 1 (Terracotta Planter Set): 3 variations (250gms, 500gms, 1kg)</li>";
echo "<li>Product 2 (Handmade Clay Bowl Set): 3 variations (250gms, 500gms, 1kg)</li>";
echo "<li>Product 4 (Artisan Clay Mug): 3 variations (250gms, 500gms, 1kg)</li>";
echo "</ul>";

echo "<p style='margin-top: 2rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107;'>";
echo "<strong>⚠️ Important:</strong> You can delete this file (setup-variations.php) after running it once.";
echo "</p>";

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Variations Setup Complete</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        h2 { color: #2c2c2c; }
        h3 { color: #27ae60; }
        a {
            color: #F790B2;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
</body>
</html>
