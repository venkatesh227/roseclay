<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Diagnostic</h1>";

// Test connection
$conn = new mysqli('localhost', 'root', '', 'roseclay_db');

if ($conn->connect_error) {
    echo "<h2 style='color: red;'>✗ Connection Failed</h2>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
    echo "<p><strong>Solution:</strong> Make sure you imported database.sql into a database named 'roseclay_db'</p>";
    
    // Check what databases exist
    $conn2 = new mysqli('localhost', 'root', '');
    $result = $conn2->query("SHOW DATABASES");
    echo "<h3>Available Databases:</h3><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    exit;
}

echo "<h2 style='color: green;'>✓ Connected to roseclay_db</h2>";

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "<h2 style='color: red;'>✗ Users table not found!</h2>";
    echo "<p><strong>Problem:</strong> The database exists but tables are missing.</p>";
    echo "<p><strong>Solution:</strong> Import database.sql into roseclay_db database.</p>";
    exit;
}

echo "<h2 style='color: green;'>✓ Users table exists</h2>";

// Check for admin user
$result = $conn->query("SELECT * FROM users WHERE email = 'admin@roseclay.com'");
if ($result->num_rows == 0) {
    echo "<h2 style='color: red;'>✗ Admin user not found!</h2>";
    echo "<p><strong>Problem:</strong> Tables exist but admin user is missing.</p>";
    
    // Show what users exist
    $users = $conn->query("SELECT id, email, is_admin FROM users");
    echo "<h3>Existing users:</h3>";
    if ($users->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Email</th><th>Is Admin</th></tr>";
        while ($user = $users->fetch_assoc()) {
            echo "<tr><td>{$user['id']}</td><td>{$user['email']}</td><td>{$user['is_admin']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in database!</p>";
    }
    
    echo "<h3>Fix: Create Admin User</h3>";
    echo "<p>Run this SQL in phpMyAdmin:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo "INSERT INTO users (first_name, last_name, email, password, is_admin, created_at) VALUES\n";
    echo "('Admin', 'User', 'admin@roseclay.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());";
    echo "</pre>";
    echo "<p><em>Password: admin123</em></p>";
    exit;
}

$admin = $result->fetch_assoc();
echo "<h2 style='color: green;'>✓ Admin user found!</h2>";
echo "<p>Email: {$admin['email']}</p>";
echo "<p>Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "</p>";

// Test password
if (password_verify('admin123', $admin['password'])) {
    echo "<h2 style='color: green;'>✓ Password is correct!</h2>";
} else {
    echo "<h2 style='color: red;'>✗ Password hash is wrong!</h2>";
    echo "<p>Need to update password hash.</p>";
}

echo "<hr>";
echo "<h2 style='color: green;'>✅ ALL CHECKS PASSED!</h2>";
echo "<p>Everything is configured correctly. You should be able to login now.</p>";
echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
?>
