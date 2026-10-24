<!DOCTYPE html>
<html>
<head>
    <title>Variation Selector Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { 
            border: 2px solid #F790B2; 
            padding: 20px; 
            margin: 20px 0;
            background: #fff;
        }
        .variation-select {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid #F9D0DE;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
        }
        label { 
            display: block; 
            font-size: 0.875rem; 
            color: #666; 
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <h1>🧪 Variation Selector Test</h1>
    
    <div class="test-box">
        <h2>Test 1: Basic Selector</h2>
        <label>
            <i class="fas fa-weight"></i> Select Weight:
        </label>
        <select class="variation-select">
            <option>Small - 250gms - $35.00</option>
            <option>Medium - 500gms - $49.00</option>
            <option>Large - 1kg - $89.00</option>
        </select>
    </div>
    
    <div class="test-box">
        <h2>Test 2: With PHP Data</h2>
        <?php
        require_once 'config.php';
        $product_id = 1; // Terracotta Planter Set
        $variations = $conn->query("SELECT * FROM product_variations WHERE product_id = $product_id AND status = 1");
        
        if ($variations->num_rows > 0):
        ?>
            <label>
                <i class="fas fa-weight"></i> Select Weight:
            </label>
            <select class="variation-select">
                <?php while($v = $variations->fetch_assoc()): ?>
                <option value="<?php echo $v['id']; ?>">
                    <?php echo $v['variation_name']; ?> - $<?php echo number_format($v['price'], 2); ?>
                </option>
                <?php endwhile; ?>
            </select>
            <p style="color: green; margin-top: 10px;">✅ Variations loaded successfully!</p>
        <?php else: ?>
            <p style="color: red;">❌ No variations found</p>
        <?php endif; ?>
    </div>
    
    <div class="test-box">
        <h2>Test 3: Full Product Card</h2>
        <?php
        $product = $conn->query("SELECT * FROM products WHERE id = 1")->fetch_assoc();
        $variations = $conn->query("SELECT * FROM product_variations WHERE product_id = 1 AND status = 1");
        $has_variations = $variations->num_rows > 0;
        ?>
        
        <h3><?php echo $product['name']; ?></h3>
        
        <?php if ($has_variations): ?>
            <div class="product-variations" style="margin: 0.75rem 0;">
                <label>
                    <i class="fas fa-weight"></i> Select Weight:
                </label>
                <select class="variation-select">
                    <?php 
                    $variations->data_seek(0);
                    while($variation = $variations->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $variation['id']; ?>">
                        <?php echo $variation['variation_name']; ?> - $<?php echo number_format($variation['price'], 2); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <p style="color: green;">✅ This is how it should look on products.php</p>
        <?php else: ?>
            <p style="color: red;">❌ No variations</p>
        <?php endif; ?>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
