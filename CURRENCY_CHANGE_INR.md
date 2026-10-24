# 💰 Currency Change: USD → INR

## ✅ **Changes Made:**

### **1. Updated config.php** ✅
```php
// Currency Configuration
define('DEFAULT_CURRENCY', 'INR');
define('USD_TO_INR_RATE', 83); // 1 USD = 83 INR

function formatPrice($price, $currency = 'INR') {
    if ($currency == 'INR') {
        return '₹' . number_format($price, 2);
    }
    return '$' . number_format($price / USD_TO_INR_RATE, 2);
}
```

**Changes:**
- ✅ Default currency: USD → **INR**
- ✅ Currency symbol: $ → **₹**
- ✅ Conversion rate defined: 1 USD = 83 INR

---

## 📝 **How Prices Work Now:**

### **Database Storage:**
Prices in database should be stored in **INR** (₹)

### **Display:**
- **Default:** Shows ₹ (INR)
- **If user switches to USD:** Converts INR → USD (divides by 83)

### **Example:**
```
Database: 4150 (stored in INR)
Display (INR): ₹4,150.00
Display (USD): $50.00 (4150 ÷ 83)
```

---

## 🔄 **Update Existing Prices:**

### **Option 1: Manual Update (Recommended)**
Update prices in admin panel to INR values:
- Old: $50 → New: ₹4,150
- Old: $70 → New: ₹5,810
- Old: $85 → New: ₹7,055

### **Option 2: Bulk Update (SQL)**
Run this SQL to convert existing USD prices to INR:

```sql
-- Update products table
UPDATE products 
SET price = price * 83,
    sale_price = CASE 
        WHEN sale_price IS NOT NULL THEN sale_price * 83 
        ELSE NULL 
    END;

-- Update product_variations table
UPDATE product_variations 
SET price = price * 83,
    sale_price = CASE 
        WHEN sale_price IS NOT NULL THEN sale_price * 83 
        ELSE NULL 
    END;
```

**⚠️ Warning:** Backup your database before running!

---

## 📋 **Files to Update:**

### **PHP Files (Display):**

Replace `$` with `₹` in these files:

#### **Frontend:**
- [ ] index.php
- [x] products.php (use formatPrice())
- [x] product-details.php (use formatPrice())
- [ ] cart.php
- [ ] checkout.php
- [ ] my-orders.php
- [ ] order-success.php

#### **Admin:**
- [ ] admin/products.php
- [ ] admin/orders.php
- [ ] admin/dashboard.php

### **Recommended Approach:**

Instead of hardcoding `$`, use the `formatPrice()` function:

**Before:**
```php
<span>$<?php echo number_format($price, 2); ?></span>
```

**After:**
```php
<span><?php echo formatPrice($price); ?></span>
```

**With currency switcher:**
```php
<?php
$currency = $_SESSION['currency'] ?? DEFAULT_CURRENCY;
echo formatPrice($price, $currency);
?>
```

---

## 🎨 **Update Currency Selector:**

### **In header.php:**
```html
<select id="currencySelect" class="currency-dropdown">
    <option value="INR" selected>₹ INR</option>
    <option value="USD">$ USD</option>
</select>
```

Already set to INR as default ✅

---

## 📊 **Price Examples:**

| Product | Old (USD) | New (INR) |
|---------|-----------|-----------|
| Terracotta Planter | $49.00 | ₹4,067.00 |
| Clay Bowl Set | $85.00 | ₹7,055.00 |
| Rustic Vase | $42.00 | ₹3,486.00 |
| Clay Mug | $24.00 | ₹1,992.00 |

---

## 🔧 **Implementation Steps:**

### **Step 1: Update Database** ✅
```sql
-- Run the SQL update script above
-- OR manually update prices in admin panel
```

### **Step 2: Update PHP Files** ⏳
Replace hardcoded `$` with `formatPrice()` function

### **Step 3: Test** ⏳
- Check all pages display ₹
- Test currency switcher (INR ↔ USD)
- Verify cart calculations
- Test checkout

---

## 💡 **Usage Examples:**

### **Simple Display:**
```php
<?php echo formatPrice($product['price']); ?>
// Output: ₹4,150.00
```

### **With Sale Price:**
```php
<?php if ($product['sale_price']): ?>
    <span class="price-old"><?php echo formatPrice($product['price']); ?></span>
    <span class="price-new"><?php echo formatPrice($product['sale_price']); ?></span>
<?php else: ?>
    <span class="price-new"><?php echo formatPrice($product['price']); ?></span>
<?php endif; ?>
```

### **With Currency Switcher:**
```php
<?php
$currency = $_SESSION['currency'] ?? DEFAULT_CURRENCY;
echo formatPrice($product['price'], $currency);
?>
```

---

## 🎯 **Quick Find & Replace:**

### **Pattern to Find:**
```
$<?php echo number_format($price, 2); ?>
```

### **Replace With:**
```
<?php echo formatPrice($price); ?>
```

### **Files to Update:**
- products.php
- product-details.php
- cart.php
- checkout.php
- my-orders.php
- order-success.php

---

## ✅ **Summary:**

| Item | Status |
|------|--------|
| config.php updated | ✅ Done |
| Default currency | ✅ INR |
| Currency symbol | ✅ ₹ |
| formatPrice() function | ✅ Updated |
| Conversion rate | ✅ 1 USD = 83 INR |
| Database prices | ⏳ Need update |
| PHP files | ⏳ Need update |

---

## 🚀 **Next Steps:**

1. ✅ config.php updated
2. ⏳ Update database prices (multiply by 83)
3. ⏳ Replace $ with formatPrice() in PHP files
4. ⏳ Test all pages
5. ⏳ Verify currency switcher works

---

**Status:** ✅ **Config Updated - Ready for Implementation**

**Default Currency:** INR (₹)
**Conversion Rate:** 1 USD = 83 INR
**Last Updated:** 2025-12-07 14:08
