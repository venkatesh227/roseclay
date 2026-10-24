<?php
require_once 'config.php';

$email = 'admin@roseclay.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE email = ? AND is_admin = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Password updated successfully for $email<br>";
    } else {
        echo "No rows updated. User might not exist or password is same.<br>";
        
        // Check if user exists
        $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
        if ($check->num_rows == 0) {
            echo "User does not exist. Creating...<br>";
             $insert = "INSERT INTO users (first_name, last_name, email, phone, password, is_admin) 
                        VALUES ('Admin', 'User', '$email', '1234567890', '$hash', 1)";
             if ($conn->query($insert)) {
                 echo "Admin user created.<br>";
             } else {
                 echo "Error creating user: " . $conn->error . "<br>";
             }
        } else {
            echo "User exists but update failed (maybe same password).<br>";
        }
    }
} else {
    echo "Error updating password: " . $stmt->error;
}

// Verify login
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (password_verify($password, $user['password'])) {
    echo "Login check: SUCCESS (Password matches hash)<br>";
} else {
    echo "Login check: FAILED (Password does not match hash)<br>";
}

// Output session info for debugging
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";

?>
