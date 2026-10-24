# 🎨 RoseClay E-Commerce - Setup Guide

## 📋 SETUP INSTRUCTIONS (5 Minutes)

### **Step 1: Import Database** ⭐ IMPORTANT
```
1. Start XAMPP (Apache + MySQL)
2. Open: http://localhost/phpmyadmin
3. Click "New" to create database
4. Database name: roseclay_db
5. Click "Import" tab
6. Choose file: database.sql (from this folder)
7. Click "Go"
8. Wait for success message
```

### **Step 2: Create Uploads Folder**
```
Create this folder structure:
uploads/
  └── products/

Full path: c:\Workspace\8.2.12\htdocs\roseclay\ecomm\uploads\products\
```

### **Step 3: Verify Configuration**
Open `config.php` and check:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password (usually empty)
define('DB_NAME', 'roseclay_db');
```

### **Step 4: Test System**
```
Open: http://localhost/roseclay/ecomm/test-system.php

This will check:
✓ Database connection
✓ Tables exist
✓ Products loaded
✓ Uploads folder
✓ All functions working
```

### **Step 5: Access Website**
```
Homepage: http://localhost/roseclay/ecomm/index.php
Admin Panel: http://localhost/roseclay/ecomm/admin/login.php
```

---

## 🔐 DEFAULT CREDENTIALS

### **Admin Login:**
```
Email: admin@roseclay.com
Password: admin123
```

### **Test User:**
Register a new account via: `register.php`

---

## 🎯 WHAT'S INCLUDED

### **Database (roseclay_db):**
- ✅ 1 Admin account
- ✅ 6 Categories
- ✅ 8 Sample products
- ✅ All tables ready

### **Admin Panel:**
- ✅ Dashboard with statistics
- ✅ Products management (add/edit/delete/images)
- ✅ Categories management
- ✅ Orders management
- ✅ Users management
- ✅ Contact messages
- ✅ Site settings

### **Frontend:**
- ✅ Dynamic homepage
- ✅ Product listing with filters
- ✅ Shopping cart
- ✅ Checkout process
- ✅ User authentication
- ✅ User dashboard
- ✅ Order history
- ✅ Contact form

---

## 🚀 QUICK TEST

### **Test Admin Panel:**
```
1. Go to: http://localhost/roseclay/ecomm/admin/login.php
2. Login with admin credentials
3. Click "Products" - you should see 8 products
4. Click "Dashboard" - you should see statistics
```

### **Test Frontend:**
```
1. Go to: http://localhost/roseclay/ecomm/index.php
2. You should see featured products
3. Click "All Products" - you should see product listing
4. Click "Add to Cart" - item should be added
5. Click cart icon - you should see cart page
```

---

## 🔧 TROUBLESHOOTING

### **Issue: "Table doesn't exist"**
**Solution:** Import database.sql in phpMyAdmin

### **Issue: "No products showing"**
**Solution:** 
1. Check database imported correctly
2. Run: http://localhost/roseclay/ecomm/test-system.php
3. Verify products exist in database

### **Issue: "Access denied"**
**Solution:** Update config.php with correct MySQL password

### **Issue: "Page not loading"**
**Solution:**
1. Check XAMPP is running
2. Check Apache is started
3. Check MySQL is started
4. Check URL is correct

### **Issue: "Images not showing"**
**Solution:**
1. Create uploads/products/ folder
2. Upload product images via admin panel
3. Or use placeholder images

---

## ✅ VERIFICATION CHECKLIST

Before using the website, verify:

- [ ] XAMPP is running
- [ ] Apache is started
- [ ] MySQL is started
- [ ] Database 'roseclay_db' created
- [ ] database.sql imported successfully
- [ ] uploads/products/ folder exists
- [ ] config.php has correct credentials
- [ ] test-system.php shows all ✓ checks
- [ ] Admin panel accessible
- [ ] Homepage loads without errors

---

## 🎉 YOU'RE READY!

Once all steps are complete:

1. **Admin Panel:** http://localhost/roseclay/ecomm/admin/login.php
2. **Website:** http://localhost/roseclay/ecomm/index.php
3. **System Test:** http://localhost/roseclay/ecomm/test-system.php

---

*Setup Time: ~5 minutes*
*Last Updated: December 7, 2024*
*RoseClay E-Commerce v1.0*
