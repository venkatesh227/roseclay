CREATE TABLE IF NOT EXISTS shipping_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    free_shipping_threshold DECIMAL(10,2) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO shipping_methods (name, description, cost, free_shipping_threshold) VALUES
('Standard Delivery', 'Delivery in 5-7 business days', 100.00, 4000.00),
('Express Delivery', 'Delivery in 1-2 business days', 250.00, NULL);
