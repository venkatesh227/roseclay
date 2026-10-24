<?php
require_once 'config.php';
$result = $conn->query("DESCRIBE order_items");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
