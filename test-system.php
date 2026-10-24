<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>RoseClay System Check</h1>";

// Check 1: PHP Version
echo "<h2>✓ PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// Check 2: Database Connection
echo "<h2>Database Connection</h2>";
require_once 'config.php';

if ($conn) {
    echo "✓ Database connected successfully!<br>";
    echo "Database: " . DB_NAME . "<br>";
    
    // Check tables
    $tables = ['users', 'products', 'categories', 'orders'];
    echo "<h3>Tables Check:</h3>";
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✓ Table '$table' exists<br>";
            
            // Count records
            $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
            echo "&nbsp;&nbsp;Records: $count<br>";
        } else {
            echo "✗ Table '$table' NOT found<br>";
        }
    }
    
    // Check products
    echo "<h3>Sample Products:</h3>";
    $products = $conn->query("SELECT id, name, price FROM products LIMIT 5");
    if ($products->num_rows > 0) {
        echo "<ul>";
        while ($product = $products->fetch_assoc()) {
            echo "<li>{$product['name']} - \${$product['price']}</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠ No products found in database<br>";
    }
    
} else {
    echo "✗ Database connection failed!<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
}

// Check 3: Uploads folder
echo "<h2>Uploads Folder</h2>";
$uploads_dir = 'uploads/products/';
if (file_exists($uploads_dir)) {
    echo "✓ Uploads folder exists<br>";
    $files = scandir($uploads_dir);
    $image_count = count($files) - 2; // Exclude . and ..
    echo "Images found: $image_count<br>";
} else {
    echo "✗ Uploads folder NOT found<br>";
    echo "⚠ Please create: $uploads_dir<br>";
}

// Check 4: Session
echo "<h2>Session</h2>";
session_start();
if (isset($_SESSION)) {
    echo "✓ Session working<br>";
} else {
    echo "✗ Session not working<br>";
}

// Check 5: Required Functions
echo "<h2>Helper Functions</h2>";
$functions = ['sanitize', 'redirect', 'isLoggedIn', 'isAdmin'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✓ Function '$func' exists<br>";
    } else {
        echo "✗ Function '$func' NOT found<br>";
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all checks show ✓, your system is ready!</p>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/login.php'>Go to Admin</a></p>";
?>
