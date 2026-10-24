# Product Variations Implementation Guide

## Overview
This guide explains how to implement and use the product variations feature for RoseClay e-commerce website. This feature allows you to add different weight options (250gms, 500gms, 1kg, etc.) with different prices for each product.

## Files Created

### 1. Database Migration
**File:** `add_variations.sql`
- Creates `product_variations` table
- Adds variation support to `order_items` table
- Includes sample variations for existing products

### 2. Admin Page
**File:** `admin/product-variations.php`
- Dedicated page for managing product variations
- Add, edit, delete variations
- Set default variations
- Manage stock and pricing per variation

### 3. Updated Files
- `admin/products.php` - Added "Manage Variations" button
- `admin/admin-style.css` - Added info button styling

## Installation Steps

### Step 1: Run Database Migration
1. Open phpMyAdmin
2. Select your `roseclay_db` database
3. Go to the SQL tab
4. Copy and paste the content from `add_variations.sql`
5. Click "Go" to execute

### Step 2: Verify Installation
1. Log in to admin panel
2. Go to Products page
3. You should see a blue "Variations" button for each product
4. Click it to manage variations

## How to Use

### Adding Variations to a Product

1. **Navigate to Products**
   - Go to Admin Panel → Products

2. **Click "Variations" Button**
   - Find the product you want to add variations to
   - Click the blue "Variations" button

3. **Add New Variation**
   - Click "Add New Variation" button
   - Fill in the form:
     * **Variation Name**: e.g., "Small - 250gms", "Medium - 500gms", "Large - 1kg"
     * **Weight Value**: e.g., "250gms", "500gms", "1kg"
     * **Price**: Regular price for this variation
     * **Sale Price**: (Optional) Discounted price
     * **Stock**: Available quantity
     * **SKU**: Unique identifier (e.g., RC-PLT-001-250)
     * **Default**: Check if this should be the default selection

4. **Save**
   - Click "Save Variation"

### Example Variations

#### Example 1: Clay Pot
- **Small - 250gms**: $35.00 (Sale: $25.00)
- **Medium - 500gms**: $49.00 (Sale: $35.00)
- **Large - 1kg**: $89.00 (Sale: $65.00)

#### Example 2: Clay Dinnerware Set
- **2-Piece Set - 250gms**: $45.00
- **4-Piece Set - 500gms**: $85.00
- **6-Piece Set - 1kg**: $125.00

### Managing Variations

#### Edit a Variation
1. Click the edit (pencil) icon
2. Update the fields
3. Click "Save Variation"

#### Delete a Variation
1. Click the delete (trash) icon
2. Confirm deletion

#### Toggle Status
- Click the status badge to activate/deactivate a variation
- Inactive variations won't show on the frontend

#### Set Default Variation
- Check the "Set as Default Variation" checkbox
- Only one variation can be default per product
- The default variation is pre-selected when customers view the product

## Database Structure

### product_variations Table
```sql
- id: Primary key
- product_id: Foreign key to products table
- variation_name: Display name (e.g., "Small - 250gms")
- variation_value: Weight value (e.g., "250gms")
- price: Regular price
- sale_price: Discounted price (nullable)
- stock: Available quantity
- sku: Unique SKU for this variation
- is_default: 1 if default, 0 otherwise
- status: 1 for active, 0 for inactive
- created_at: Timestamp
- updated_at: Timestamp
```

## Frontend Integration (Next Steps)

To display variations on the frontend product pages, you'll need to:

### 1. Update Product Display Page
```php
// Fetch variations for a product
$variations_query = $conn->prepare("
    SELECT * FROM product_variations 
    WHERE product_id = ? AND status = 1 
    ORDER BY variation_value ASC
");
$variations_query->bind_param("i", $product_id);
$variations_query->execute();
$variations = $variations_query->get_result();
```

### 2. Display Variation Selector
```html
<div class="product-variations">
    <label>Select Weight:</label>
    <select name="variation_id" id="variationSelect" required>
        <?php while($variation = $variations->fetch_assoc()): ?>
            <option value="<?php echo $variation['id']; ?>" 
                    data-price="<?php echo $variation['price']; ?>"
                    data-sale-price="<?php echo $variation['sale_price']; ?>"
                    <?php echo $variation['is_default'] ? 'selected' : ''; ?>>
                <?php echo $variation['variation_name']; ?> - 
                $<?php echo number_format($variation['sale_price'] ?? $variation['price'], 2); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>
```

### 3. Update Price Display with JavaScript
```javascript
document.getElementById('variationSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.dataset.price;
    const salePrice = selectedOption.dataset.salePrice;
    
    // Update price display
    if (salePrice) {
        document.getElementById('productPrice').innerHTML = 
            `<span class="old-price">$${price}</span> 
             <span class="sale-price">$${salePrice}</span>`;
    } else {
        document.getElementById('productPrice').innerHTML = `$${price}`;
    }
});
```

### 4. Update Add to Cart
```php
// When adding to cart, include variation_id
$variation_id = (int)$_POST['variation_id'];

// Get variation details
$var_query = $conn->prepare("SELECT * FROM product_variations WHERE id = ?");
$var_query->bind_param("i", $variation_id);
$var_query->execute();
$variation = $var_query->get_result()->fetch_assoc();

// Add to cart with variation info
$_SESSION['cart'][] = [
    'product_id' => $product_id,
    'variation_id' => $variation_id,
    'variation_name' => $variation['variation_name'],
    'price' => $variation['sale_price'] ?? $variation['price'],
    'quantity' => $quantity
];
```

## Best Practices

### 1. SKU Naming Convention
Use a consistent format: `PRODUCT-SKU-WEIGHT`
- Example: `RC-PLT-001-250` (Product RC-PLT-001, 250gms)
- Example: `RC-DIN-002-500` (Product RC-DIN-002, 500gms)

### 2. Variation Names
Be descriptive and consistent:
- ✅ Good: "Small - 250gms", "Medium - 500gms", "Large - 1kg"
- ✅ Good: "250gms Pack", "500gms Pack", "1kg Pack"
- ❌ Bad: "S", "M", "L" (not descriptive enough)

### 3. Pricing Strategy
- Set competitive prices for each variation
- Larger quantities should have better per-gram pricing
- Example:
  * 250gms: $0.14/gm ($35 total)
  * 500gms: $0.10/gm ($50 total) - Better value
  * 1kg: $0.09/gm ($90 total) - Best value

### 4. Stock Management
- Keep accurate stock levels for each variation
- Set low stock alerts (when stock < 10)
- Update stock after each order

### 5. Default Variation
- Always set one variation as default
- Usually the most popular or mid-range option
- Helps customers make quick decisions

## Common Weight Options

### For Clay Products:
- 250gms (Small/Sample size)
- 500gms (Medium/Regular size)
- 1kg (Large/Bulk size)
- 2kg (Extra Large/Family size)
- 5kg (Wholesale/Commercial size)

### For Dinnerware Sets:
- 2-Piece Set
- 4-Piece Set
- 6-Piece Set
- 8-Piece Set
- 12-Piece Set

## Troubleshooting

### Issue: "Variations" button not showing
**Solution:** Clear browser cache and refresh the admin products page

### Issue: Can't add variations
**Solution:** Make sure you ran the `add_variations.sql` migration

### Issue: Variations not saving
**Solution:** Check database permissions and error logs

### Issue: Default variation not working
**Solution:** Only one variation can be default. The system automatically unsets others when you set a new default.

## Support

For additional help or custom modifications, refer to:
- Database schema in `database.sql`
- Admin products page in `admin/products.php`
- Variations page in `admin/product-variations.php`

---

**Status:** ✅ READY TO USE

The variations system is fully functional in the admin panel. Frontend integration can be added based on your specific design requirements.
