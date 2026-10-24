<?php
require_once 'config.php';

// Get the products that are showing in the screenshot
$products = $conn->query("SELECT id, name FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 4");

echo "Products on page:\n\n";
while ($product = $products->fetch_assoc()) {
    $product_id = $product['id'];
    $variations = $conn->query("SELECT COUNT(*) as count FROM product_variations WHERE product_id = $product_id AND status = 1");
    $var_count = $variations->fetch_assoc()['count'];
    
    echo "Product ID {$product_id}: {$product['name']}\n";
    echo "  Variations: {$var_count}\n";
    
    if ($var_count > 0) {
        $vars = $conn->query("SELECT variation_name, price FROM product_variations WHERE product_id = $product_id AND status = 1");
        while ($v = $vars->fetch_assoc()) {
            echo "    - {$v['variation_name']}: \${$v['price']}\n";
        }
    }
    echo "\n";
}
?>
