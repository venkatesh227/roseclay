<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Password Fix</h1>";

$conn = new mysqli('localhost', 'root', '', 'roseclay_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate correct password hash for 'admin123'
$correct_hash = password_hash('admin123', PASSWORD_DEFAULT);

// Update admin password
$sql = "UPDATE users SET password = ? WHERE email = 'admin@roseclay.com'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correct_hash);

if ($stmt->execute()) {
    echo "<h2 style='color: green;'>✓ Password Updated Successfully!</h2>";
    echo "<p>Admin password has been set to: <strong>admin123</strong></p>";
    
    // Verify it works
    $result = $conn->query("SELECT password FROM users WHERE email = 'admin@roseclay.com'");
    $user = $result->fetch_assoc();
    
    if (password_verify('admin123', $user['password'])) {
        echo "<h2 style='color: green;'>✓ Password Verified!</h2>";
        echo "<p>You can now login with:</p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> admin@roseclay.com</li>";
        echo "<li><strong>Password:</strong> admin123</li>";
        echo "</ul>";
        echo "<p><a href='admin/login.php' style='display: inline-block; padding: 10px 20px; background: #F790B2; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    } else {
        echo "<h2 style='color: red;'>✗ Verification Failed</h2>";
    }
} else {
    echo "<h2 style='color: red;'>✗ Update Failed</h2>";
    echo "<p>Error: " . $conn->error . "</p>";
}

$conn->close();
?>
