-- Add Product Variations Feature
-- Run this SQL script in phpMyAdmin to add variations support

USE roseclay_db;

-- Create Product Variations Table
CREATE TABLE IF NOT EXISTS product_variations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variation_name VARCHAR(100) NOT NULL COMMENT 'e.g., 250gms, 500gms, 1kg',
    variation_value VARCHAR(100) NOT NULL COMMENT 'Weight value',
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2) NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    is_default TINYINT(1) DEFAULT 0 COMMENT '1 if this is the default variation',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Update Order Items table to support variations
ALTER TABLE order_items 
ADD COLUMN variation_id INT NULL AFTER product_id,
ADD COLUMN variation_name VARCHAR(100) NULL AFTER product_name,
ADD FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL;

-- Add sample variations for existing products
-- Example: Terracotta Planter Set with different sizes
INSERT INTO product_variations (product_id, variation_name, variation_value, price, sale_price, stock, sku, is_default) VALUES
(1, 'Small - 250gms', '250gms', 49.00, 35.00, 30, 'RC-PLT-001-250', 1),
(1, 'Medium - 500gms', '500gms', 70.00, 49.00, 25, 'RC-PLT-001-500', 0),
(1, 'Large - 1kg', '1kg', 120.00, 89.00, 15, 'RC-PLT-001-1000', 0);

-- Example: Handmade Clay Bowl Set
INSERT INTO product_variations (product_id, variation_name, variation_value, price, stock, sku, is_default) VALUES
(2, 'Small Set - 250gms', '250gms', 65.00, 20, 'RC-DIN-001-250', 1),
(2, 'Medium Set - 500gms', '500gms', 85.00, 15, 'RC-DIN-001-500', 0),
(2, 'Large Set - 1kg', '1kg', 125.00, 10, 'RC-DIN-001-1000', 0);

-- Example: Artisan Clay Mug
INSERT INTO product_variations (product_id, variation_name, variation_value, price, sale_price, stock, sku, is_default) VALUES
(4, '250gms', '250gms', 24.00, 18.00, 60, 'RC-MUG-001-250', 1),
(4, '500gms', '500gms', 32.00, 24.00, 50, 'RC-MUG-001-500', 0),
(4, '1kg', '1kg', 55.00, 42.00, 30, 'RC-MUG-001-1000', 0);
