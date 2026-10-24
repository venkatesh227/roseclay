-- ================================================
-- Currency Conversion: USD to INR
-- Conversion Rate: 1 USD = 83 INR
-- ================================================

-- IMPORTANT: Backup your database before running this script!

-- Step 1: Update products table
UPDATE products 
SET 
    price = ROUND(price * 83, 2),
    sale_price = CASE 
        WHEN sale_price IS NOT NULL AND sale_price > 0 
        THEN ROUND(sale_price * 83, 2)
        ELSE NULL 
    END
WHERE status = 1;

-- Step 2: Update product_variations table
UPDATE product_variations 
SET 
    price = ROUND(price * 83, 2),
    sale_price = CASE 
        WHEN sale_price IS NOT NULL AND sale_price > 0 
        THEN ROUND(sale_price * 83, 2)
        ELSE NULL 
    END
WHERE status = 1;

-- Step 3: Verify the changes
SELECT 
    id,
    name,
    price as price_inr,
    sale_price as sale_price_inr,
    ROUND(price / 83, 2) as price_usd_equivalent
FROM products 
WHERE status = 1
LIMIT 10;

-- Step 4: Check variations
SELECT 
    pv.id,
    p.name as product_name,
    pv.variation_name,
    pv.price as price_inr,
    pv.sale_price as sale_price_inr
FROM product_variations pv
JOIN products p ON pv.product_id = p.id
WHERE pv.status = 1
LIMIT 10;

-- ================================================
-- ROLLBACK (if needed)
-- ================================================
-- If you need to revert back to USD:
-- 
-- UPDATE products 
-- SET 
--     price = ROUND(price / 83, 2),
--     sale_price = CASE 
--         WHEN sale_price IS NOT NULL 
--         THEN ROUND(sale_price / 83, 2)
--         ELSE NULL 
--     END;
--
-- UPDATE product_variations 
-- SET 
--     price = ROUND(price / 83, 2),
--     sale_price = CASE 
--         WHEN sale_price IS NOT NULL 
--         THEN ROUND(sale_price / 83, 2)
--         ELSE NULL 
--     END;
-- ================================================
