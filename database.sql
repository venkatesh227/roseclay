-- RoseClay E-Commerce Database Schema
-- Run this SQL script in phpMyAdmin to create the database

CREATE DATABASE IF NOT EXISTS roseclay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE roseclay_db;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    stock INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    image VARCHAR(255),
    gallery TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0,
    reviews_count INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB;

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    apartment VARCHAR(100),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    shipping_method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- Reviews Table
CREATE TABLE reviews (
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
) ENGINE=InnoDB;

-- Wishlist Table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Contact Messages Table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert Default Admin User (password: admin123)
INSERT INTO users (first_name, last_name, email, phone, password, is_admin) 
VALUES ('Admin', 'User', 'admin@roseclay.com', '+1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert Default Categories
INSERT INTO categories (name, slug, description, status) VALUES
('Planters & Pots', 'planters-pots', 'Beautiful handcrafted planters and pots for your plants', 1),
('Dinnerware', 'dinnerware', 'Elegant clay dinnerware sets for your dining table', 1),
('Home Decor', 'home-decor', 'Decorative clay items to beautify your home', 1),
('Mugs & Cups', 'mugs-cups', 'Artisan clay mugs and cups for your beverages', 1),
('Wellness', 'wellness', 'Clay products for health and wellness', 1),
('Gift Sets', 'gift-sets', 'Curated gift sets for special occasions', 1);

-- Insert Sample Products
INSERT INTO products (category_id, name, slug, description, short_description, price, sale_price, stock, sku, is_featured, is_new, rating, reviews_count, status) VALUES
(1, 'Terracotta Planter Set', 'terracotta-planter-set', 'Beautiful set of handcrafted terracotta planters perfect for indoor and outdoor plants. Each planter is uniquely crafted by skilled artisans.', 'Handcrafted terracotta planters - Set of 3', 70.00, 49.00, 25, 'RC-PLT-001', 1, 0, 4.5, 28, 1),
(2, 'Handmade Clay Bowl Set', 'handmade-clay-bowl-set', 'Elegant set of 4 handmade clay bowls with glazed finish. Perfect for serving salads, soups, and more.', 'Set of 4 glazed clay bowls', 85.00, NULL, 15, 'RC-DIN-001', 1, 1, 5.0, 42, 1),
(3, 'Rustic Clay Vase', 'rustic-clay-vase', 'A stunning rustic clay vase that adds charm to any room. Perfect for fresh or dried flowers.', 'Handcrafted rustic vase', 42.00, NULL, 30, 'RC-DEC-001', 0, 0, 4.0, 15, 1),
(4, 'Artisan Clay Mug', 'artisan-clay-mug', 'Handcrafted clay mug with unique design. Holds 350ml and is perfect for your morning coffee or tea.', '350ml handcrafted mug', 32.00, 24.00, 50, 'RC-MUG-001', 1, 0, 4.6, 35, 1),
(2, 'Ceramic Plate Collection', 'ceramic-plate-collection', 'Beautiful collection of ceramic plates in various sizes. Perfect for everyday dining or special occasions.', 'Set of 6 ceramic plates', 95.00, NULL, 20, 'RC-DIN-002', 1, 0, 5.0, 18, 1),
(3, 'Decorative Clay Sculpture', 'decorative-clay-sculpture', 'Unique decorative clay sculpture handcrafted by master artisans. A perfect centerpiece for your home.', 'Handcrafted clay sculpture', 68.00, NULL, 10, 'RC-DEC-002', 0, 1, 4.7, 12, 1),
(2, 'Traditional Clay Cookware', 'traditional-clay-cookware', 'Traditional clay cookware set perfect for healthy cooking. Retains heat and enhances flavors.', 'Clay cookware set', 120.00, 96.00, 12, 'RC-DIN-003', 0, 0, 4.2, 22, 1),
(3, 'Clay Candle Holder Set', 'clay-candle-holder-set', 'Set of 3 elegant clay candle holders. Creates a warm and cozy ambiance in any room.', 'Set of 3 candle holders', 38.00, NULL, 40, 'RC-DEC-003', 1, 0, 4.9, 31, 1);

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'RoseClay'),
('site_email', 'info@roseclay.com'),
('site_phone', '+1 (555) 123-4567'),
('site_address', '123 Clay Street, California, USA 90001'),
('currency', 'USD'),
('tax_rate', '10'),
('shipping_cost', '0'),
('free_shipping_threshold', '50');
