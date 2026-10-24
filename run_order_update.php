<?php
require_once 'config.php';
$sql = file_get_contents('update_orders_table.sql');
if ($conn->query($sql)) { echo "Order table updated."; } else { echo "Error: " . $conn->error; }
?>
