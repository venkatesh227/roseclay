# 📁 Header & Footer Includes - Implementation Guide

## ✅ **Created Include Files:**

All include files are located in the `includes/` directory:

```
includes/
├── head.php      - Common meta tags, CSS, fonts
├── header.php    - Top bar + navigation header
├── footer.php    - Footer with all sections
└── scripts.php   - Common JavaScript files
```

---

## 🎯 **How to Use in PHP Pages:**

### **Basic Page Structure:**

```php
<?php
session_start();
require_once 'config.php';

// Your page-specific PHP code here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Page Title - RoseClay</title>
    <meta name="description" content="Page description">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb (if needed) -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Page Name</span>
        </div>
    </div>

    <!-- Your page content here -->
    <section class="your-section">
        <div class="container">
            <!-- Content -->
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <?php include 'includes/scripts.php'; ?>
    <!-- Page-specific scripts here -->
</body>
</html>
```

---

## 📝 **Include Files Details:**

### **1. head.php**
Contains:
- ✅ Meta charset & viewport
- ✅ style.css
- ✅ pages-styles.css
- ✅ Google Fonts (Inter, Playfair Display)
- ✅ Font Awesome icons

**Usage:**
```php
<head>
    <?php include 'includes/head.php'; ?>
    <title>Your Page Title - RoseClay</title>
</head>
```

### **2. header.php**
Contains:
- ✅ Top bar (email, location, currency, hours, social)
- ✅ Header with logo
- ✅ Navigation menu
- ✅ Search, wishlist, cart buttons
- ✅ Mobile menu button
- ✅ **Active state detection** (automatically highlights current page)

**Features:**
- Detects current page and adds `class="active"`
- Shows "My Account" if logged in, "Login" if not
- Responsive mobile menu

**Usage:**
```php
<body>
    <?php include 'includes/header.php'; ?>
    <!-- Rest of page -->
```

### **3. footer.php**
Contains:
- ✅ 4-column footer layout
- ✅ About section with social links
- ✅ Quick Links
- ✅ Customer Service links
- ✅ Contact Info
- ✅ Copyright with dynamic year

**Usage:**
```php
    <?php include 'includes/footer.php'; ?>
</body>
```

### **4. scripts.php**
Contains:
- ✅ script.js
- ✅ cart.js

**Usage:**
```php
    <?php include 'includes/scripts.php'; ?>
    <!-- Page-specific scripts -->
</body>
```

---

## 🔄 **Updating Existing Pages:**

### **Example: Update index.php**

**Before:**
```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <!-- All CSS links -->
</head>
<body>
    <!-- Top Bar HTML -->
    <!-- Header HTML -->
    
    <!-- Content -->
    
    <!-- Footer HTML -->
    <script src="script.js"></script>
</body>
</html>
```

**After:**
```php
<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include 'includes/head.php'; ?>
    <title>Home - RoseClay</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Content -->
    
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
```

---

## 📋 **Pages to Update:**

### **Frontend Pages:**
- [ ] index.php
- [x] products.php (already updated)
- [x] product-details.php (already updated)
- [ ] cart.php
- [ ] checkout.php
- [ ] login.php
- [ ] register.php
- [ ] my-account.php
- [ ] my-profile.php
- [ ] my-orders.php
- [ ] order-success.php
- [ ] contact.php

---

## ✨ **Benefits:**

### **1. Consistency**
- ✅ Same header/footer on all pages
- ✅ Same navigation
- ✅ Same styling

### **2. Easy Maintenance**
- ✅ Update header once, changes everywhere
- ✅ Update footer once, changes everywhere
- ✅ No duplicate code

### **3. Active State**
- ✅ Automatically highlights current page in nav
- ✅ No manual class assignment needed

### **4. Clean Code**
- ✅ Shorter page files
- ✅ Easier to read
- ✅ Better organization

---

## 🎨 **Active State Detection:**

The header automatically detects the current page and adds `class="active"`:

```php
<li>
    <a href="products.php" 
       <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'class="active"' : ''; ?>>
        All Products
    </a>
</li>
```

**Highlights:**
- Home → index.php
- All Products → products.php, product-details.php
- My Account → my-account.php, my-profile.php, my-orders.php
- Login → login.php, register.php

---

## 🔧 **Customization:**

### **To Add a New Menu Item:**

Edit `includes/header.php`:
```php
<li>
    <a href="new-page.php" 
       <?php echo (basename($_SERVER['PHP_SELF']) == 'new-page.php') ? 'class="active"' : ''; ?>>
        New Page
    </a>
</li>
```

### **To Update Footer Links:**

Edit `includes/footer.php`:
```php
<div class="footer-col">
    <h3>Your Section</h3>
    <ul>
        <li><a href="#">Link 1</a></li>
        <li><a href="#">Link 2</a></li>
    </ul>
</div>
```

### **To Add CSS:**

Edit `includes/head.php`:
```php
<link rel="stylesheet" href="your-custom.css">
```

### **To Add JavaScript:**

Edit `includes/scripts.php`:
```php
<script src="your-script.js"></script>
```

---

## 📝 **Quick Reference:**

### **Minimal Page Template:**

```php
<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Page Title - RoseClay</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="page-section">
        <div class="container">
            <h1>Page Content</h1>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
```

---

## ✅ **Summary:**

| Include File | Purpose | Usage |
|--------------|---------|-------|
| `head.php` | Meta tags, CSS, fonts | In `<head>` |
| `header.php` | Top bar + navigation | After `<body>` |
| `footer.php` | Footer sections | Before `</body>` |
| `scripts.php` | JavaScript files | Before `</body>` |

---

## 🎯 **Next Steps:**

1. ✅ Include files created
2. ⏳ Update existing PHP pages to use includes
3. ⏳ Test all pages for consistency
4. ⏳ Verify active states work correctly

---

**Created:** 2025-12-07 13:45
**Location:** `includes/` directory
**Status:** ✅ Ready to use
