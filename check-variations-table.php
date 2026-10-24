<?php
require_once 'config.php';

// Check if product_variations table exists
$result = $conn->query("SHOW TABLES LIKE 'product_variations'");

if ($result->num_rows > 0) {
    echo "✅ Table EXISTS\n\n";
    
    // Check how many variations exist
    $count = $conn->query("SELECT COUNT(*) as count FROM product_variations")->fetch_assoc();
    echo "Total variations: " . $count['count'] . "\n\n";
    
    // Show sample variations
    $variations = $conn->query("SELECT pv.*, p.name as product_name 
                                FROM product_variations pv 
                                JOIN products p ON pv.product_id = p.id 
                                LIMIT 5");
    
    if ($variations->num_rows > 0) {
        echo "Sample variations:\n";
        while ($v = $variations->fetch_assoc()) {
            echo "- {$v['product_name']}: {$v['variation_name']} - \${$v['price']}\n";
        }
    }
} else {
    echo "❌ Table DOES NOT EXIST\n";
    echo "\nYou need to run: http://localhost/roseclay/ecomm/setup-variations.php\n";
}
?>
