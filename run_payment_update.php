<?php
require_once 'config.php';
$sql = file_get_contents('add_payment_columns.sql');
// Split by semicolon to run multiple queries if needed, though simple query might handle it differently.
// For safety, let's run them one by one.
$queries = explode(';', $sql);
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if ($conn->query($query)) {
            echo "Executed: " . substr($query, 0, 50) . "...\n";
        } else {
            // Ignore "Duplicate column name" error if I run it twice
            if ($conn->errno == 1060) {
                echo "Column already exists.\n";
            } else {
                echo "Error: " . $conn->error . "\n";
            }
        }
    }
}
?>
