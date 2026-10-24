# 🔧 HOMEPAGE FIX GUIDE

## Issue: Frontend Homepage Disturbed

### **Possible Causes:**
1. Database not imported
2. No products in database
3. Missing product images
4. PHP errors

---

## ✅ QUICK FIX STEPS

### **Step 1: Import Database**
```sql
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: roseclay_db
3. Click "Import" tab
4. Choose file: database.sql
5. Click "Go"
```

### **Step 2: Create Uploads Folder**
```
Create folder: uploads/products/
Path: c:\Workspace\8.2.12\htdocs\roseclay\ecomm\uploads\products\
```

### **Step 3: Check PHP Errors**
```
1. Open: http://localhost/roseclay/ecomm/index.php
2. If you see errors, check:
   - config.php database credentials
   - PHP is running
   - MySQL is running
```

### **Step 4: Verify Database Connection**
Update config.php if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password
define('DB_NAME', 'roseclay_db');
```

---

## 🎯 ALTERNATIVE: Use index.html Instead

If you want to see the static version first:
```
URL: http://localhost/roseclay/ecomm/index.html
```

This will show the homepage without database connection.

---

## 🔍 DEBUGGING

### **Check if database is connected:**
Create a test file: `test.php`
```php
<?php
require_once 'config.php';
echo "Database connected!";
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$count = $result->fetch_assoc()['count'];
echo "<br>Total products: " . $count;
?>
```

### **Check PHP errors:**
Add to top of index.php:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
```

---

## 📋 CHECKLIST

- [ ] Database imported (roseclay_db)
- [ ] uploads/products/ folder created
- [ ] config.php has correct credentials
- [ ] PHP is running (check phpinfo)
- [ ] MySQL is running
- [ ] No PHP errors displayed

---

## 🆘 COMMON ERRORS

### **Error: "Table doesn't exist"**
**Solution:** Import database.sql

### **Error: "Access denied for user"**
**Solution:** Update config.php with correct MySQL password

### **Error: "No such file or directory"**
**Solution:** Create uploads/products/ folder

### **Error: "Call to undefined function"**
**Solution:** Check PHP extensions are enabled

---

## ✅ EXPECTED RESULT

After fixing, you should see:
- ✅ Homepage loads without errors
- ✅ Featured products displayed (if database has products)
- ✅ Navigation menu works
- ✅ Add to cart buttons visible

---

## 📞 NEXT STEPS

1. Follow the Quick Fix Steps above
2. Refresh: http://localhost/roseclay/ecomm/index.php
3. Check if homepage displays correctly
4. If still issues, share the error message

---

*Last Updated: December 7, 2024*
