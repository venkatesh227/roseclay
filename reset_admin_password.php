<?php
require_once 'config.php';

$email = 'admin@roseclay.com';
$new_password = 'password123'; // Setting a known password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    echo "Password for $email has been reset to: $new_password";
} else {
    echo "Error updating password: " . $conn->error;
}
?>
