# RoseClay E-Commerce - Backend Implementation Guide

## 🎯 Overview

This guide will help you convert the static RoseClay website into a fully dynamic PHP/MySQL e-commerce platform with an admin panel.

## 📦 What's Included

### ✅ Already Created:
1. **database.sql** - Complete MySQL database schema
2. **config.php** - Database configuration and helper functions

### 📋 Files to Create:

## 🚀 STEP 1: Database Setup

### 1.1 Import Database
1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Click "New" to create a database
3. Click "Import" tab
4. Choose `database.sql` file
5. Click "Go"

**Default Admin Login:**
- Email: `admin@roseclay.com`
- Password: `admin123`

---

## 🔧 STEP 2: Update Configuration

### 2.1 Edit `config.php`
Update these lines if needed:
```php
define('DB_HOST', 'localhost');      // Your database host
define('DB_USER', 'root');           // Your database username
define('DB_PASS', '');               // Your database password
define('DB_NAME', 'roseclay_db');    // Database name
define('SITE_URL', 'http://localhost/roseclay/ecomm');  // Your site URL
```

---

## 📁 STEP 3: File Structure

Create this folder structure:
```
roseclay/ecomm/
├── admin/                  ← Admin panel folder
│   ├── index.php          ← Dashboard
│   ├── login.php          ← Admin login
│   ├── products.php       ← Manage products
│   ├── orders.php         ← Manage orders
│   ├── users.php          ← Manage users
│   ├── categories.php     ← Manage categories
│   ├── settings.php       ← Site settings
│   └── logout.php         ← Logout
├── includes/              ← Reusable components
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── uploads/               ← Product images
│   └── products/
├── config.php             ← Database config ✓
├── database.sql           ← Database schema ✓
├── index.php              ← Homepage (convert from .html)
├── products.php           ← Products page
├── product-detail.php     ← Single product
├── cart.php               ← Shopping cart
├── checkout.php           ← Checkout
├── login.php              ← User login
├── register.php           ← User registration
├── my-account.php         ← User dashboard
├── my-profile.php         ← User profile
├── my-orders.php          ← User orders
├── contact.php            ← Contact form
└── faqs.php               ← FAQ page
```

---

## 💻 STEP 4: Key PHP Files to Create

### 4.1 Admin Panel - Login (`admin/login.php`)

```php
<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND is_admin = 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['is_admin'] = 1;
            redirect(ADMIN_URL . '/index.php');
        }
    }
    $error = "Invalid credentials";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - RoseClay</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-login">
        <h1>Admin Login</h1>
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
```

### 4.2 Admin Dashboard (`admin/index.php`)

```php
<?php
require_once '../config.php';
requireAdmin();

// Get statistics
$stats = [];
$stats['total_products'] = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$stats['total_orders'] = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
$stats['total_revenue'] = $conn->query("SELECT SUM(total) as revenue FROM orders WHERE status != 'cancelled'")->fetch_assoc()['revenue'];

// Get recent orders
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - RoseClay</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../pages-styles.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-container">
        <h1>Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Total Products</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_orders']; ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
        
        <h2>Recent Orders</h2>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['order_number']; ?></td>
                    <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                    <td><span class="order-status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
```

### 4.3 Products Management (`admin/products.php`)

```php
<?php
require_once '../config.php';
requireAdmin();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add product logic
                $name = sanitize($_POST['name']);
                $price = sanitize($_POST['price']);
                $category_id = sanitize($_POST['category_id']);
                // ... more fields
                
                $sql = "INSERT INTO products (name, price, category_id, ...) VALUES (?, ?, ?, ...)";
                // Execute query
                setMessage('Product added successfully');
                break;
                
            case 'edit':
                // Edit product logic
                break;
                
            case 'delete':
                // Delete product logic
                break;
        }
    }
}

// Get all products
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - RoseClay Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-container">
        <h1>Manage Products</h1>
        <button onclick="showAddProductModal()">Add New Product</button>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($product = $products->fetch_assoc()): ?>
                <tr>
                    <td><img src="../uploads/products/<?php echo $product['image']; ?>" width="50"></td>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo $product['category_name']; ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['stock']; ?></td>
                    <td><?php echo $product['status'] ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <button onclick="editProduct(<?php echo $product['id']; ?>)">Edit</button>
                        <button onclick="deleteProduct(<?php echo $product['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
```

---

## 🎨 STEP 5: Convert HTML to PHP

### 5.1 Homepage (`index.php`)

Convert `index.html` to `index.php` and add dynamic product loading:

```php
<?php
require_once 'config.php';

// Get featured products
$featured_products = $conn->query("SELECT * FROM products WHERE is_featured = 1 AND status = 1 LIMIT 8");
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Keep existing head content -->
</head>
<body>
    <!-- Keep existing header -->
    
    <!-- Products Section - Make Dynamic -->
    <section class="featured-section">
        <div class="products-grid">
            <?php while($product = $featured_products->fetch_assoc()): ?>
            <div class="product-card">
                <?php if($product['sale_price']): ?>
                <div class="product-badge sale">
                    -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                </div>
                <?php endif; ?>
                
                <div class="product-image">
                    <img src="uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                </div>
                
                <div class="product-info">
                    <h3><?php echo $product['name']; ?></h3>
                    <div class="product-rating">
                        <!-- Rating stars -->
                        <span>(<?php echo $product['rating']; ?>)</span>
                    </div>
                    <div class="product-price">
                        <?php if($product['sale_price']): ?>
                        <span class="price-old">$<?php echo number_format($product['price'], 2); ?></span>
                        <span class="price-new">$<?php echo number_format($product['sale_price'], 2); ?></span>
                        <?php else: ?>
                        <span class="price-new">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
</body>
</html>
```

---

## 🛒 STEP 6: Shopping Cart & Checkout

### 6.1 Add to Cart (AJAX - `add-to-cart.php`)

```php
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Get product details
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['sale_price'] ?? $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}
?>
```

### 6.2 Checkout Process (`checkout-process.php`)

```php
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    // ... more fields
    
    // Calculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.10;
    $total = $subtotal + $tax;
    
    // Generate order number
    $order_number = generateOrderNumber();
    
    // Insert order
    $sql = "INSERT INTO orders (user_id, order_number, first_name, last_name, email, ..., subtotal, tax, total, status) 
            VALUES (?, ?, ?, ?, ?, ..., ?, ?, ?, 'pending')";
    // Execute query
    
    $order_id = $conn->insert_id;
    
    // Insert order items
    foreach ($_SESSION['cart'] as $item) {
        $sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) 
                VALUES (?, ?, ?, ?, ?, ?)";
        // Execute query
    }
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect to success page
    redirect('my-orders.php?order=' . $order_number);
}
?>
```

---

## 📧 STEP 7: Contact Form

### 7.1 Contact Form Processing (`contact-process.php`)

```php
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, subject, message) 
            VALUES ('$first_name', '$last_name', '$email', '$phone', '$subject', '$message')";
    
    if ($conn->query($sql)) {
        setMessage('Thank you! Your message has been sent successfully.', 'success');
    } else {
        setMessage('Error sending message. Please try again.', 'error');
    }
    
    redirect('contact.php');
}
?>
```

---

## 🔐 STEP 8: User Authentication

### 8.1 Login Process (`login-process.php`)

```php
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            if ($user['is_admin']) {
                redirect(ADMIN_URL . '/index.php');
            } else {
                redirect('my-account.php');
            }
        }
    }
    
    setMessage('Invalid email or password', 'error');
    redirect('login.php');
}
?>
```

### 8.2 Registration Process (`register-process.php`)

```php
<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        setMessage('Email already exists', 'error');
        redirect('register.php');
    }
    
    $sql = "INSERT INTO users (first_name, last_name, email, phone, password) 
            VALUES ('$first_name', '$last_name', '$email', '$phone', '$password')";
    
    if ($conn->query($sql)) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $_SESSION['user_email'] = $email;
        
        redirect('my-account.php');
    }
}
?>
```

---

## 📊 STEP 9: Admin Panel Features

### Key Admin Features to Implement:

1. **Dashboard** - Statistics and recent orders
2. **Products Management** - Add, edit, delete products
3. **Orders Management** - View, update order status
4. **Users Management** - View, manage users
5. **Categories Management** - Add, edit categories
6. **Contact Messages** - View and respond to messages
7. **Settings** - Update site settings

---

## 🎯 STEP 10: Testing

### 10.1 Test Admin Panel
1. Go to `http://localhost/roseclay/ecomm/admin/login.php`
2. Login with: `admin@roseclay.com` / `admin123`
3. Test all admin features

### 10.2 Test Frontend
1. Browse products
2. Add to cart
3. Register new user
4. Complete checkout
5. View orders

---

## 📝 Additional Files Needed

Create these files for complete functionality:

1. `includes/header.php` - Reusable header
2. `includes/footer.php` - Reusable footer
3. `admin/includes/admin-header.php` - Admin navigation
4. `logout.php` - Logout functionality
5. `admin/logout.php` - Admin logout
6. `api/` folder - For AJAX requests

---

## 🚀 Quick Start Commands

```bash
# 1. Import database
mysql -u root -p roseclay_db < database.sql

# 2. Set permissions (if on Linux)
chmod 755 uploads/
chmod 755 uploads/products/

# 3. Access the site
http://localhost/roseclay/ecomm/

# 4. Access admin panel
http://localhost/roseclay/ecomm/admin/
```

---

## 🔒 Security Recommendations

1. Use prepared statements for all database queries
2. Validate and sanitize all user inputs
3. Use HTTPS in production
4. Implement CSRF protection
5. Add rate limiting for login attempts
6. Regular security audits

---

## 📚 Next Steps

1. Import `database.sql` in phpMyAdmin
2. Update `config.php` with your database credentials
3. Convert HTML files to PHP (start with index.php)
4. Create admin panel files
5. Test all functionality
6. Deploy to production server

---

**Need help with specific files? Let me know which part you'd like me to create in detail!**
