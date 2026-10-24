<?php
require_once 'config.php';

// Check if reviews table exists
$result = $conn->query("SHOW TABLES LIKE 'reviews'");
if ($result->num_rows == 0) {
    echo "Reviews table does not exist. Creating it...\n";
    $sql = "CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        title VARCHAR(255),
        comment TEXT,
        status TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB;";
    
    if ($conn->query($sql) === TRUE) {
        echo "Reviews table created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
} else {
    echo "Reviews table already exists.\n";
    
    // Check columns
    $columns = $conn->query("SHOW COLUMNS FROM reviews");
    while ($col = $columns->fetch_assoc()) {
        echo "Column: " . $col['Field'] . " - " . $col['Type'] . "\n";
    }
}
?>
