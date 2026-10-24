<?php
require_once 'config.php';

$sql = file_get_contents('setup_shipping.sql');

if ($conn->multi_query($sql)) {
    echo "Shipping table created successfully!";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
