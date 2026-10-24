<?php
require_once 'config.php';
$result = $conn->query("SELECT id, email, is_admin FROM users");
echo "Users in DB:\n";
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Email: " . $row['email'] . " | Is Admin: " . $row['is_admin'] . "\n";
}

echo "\nCurrent Session:\n";
print_r($_SESSION);
?>
