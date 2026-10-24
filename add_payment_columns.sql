ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) DEFAULT 'Pending' AFTER status;
ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL AFTER payment_status;
ALTER TABLE orders ADD COLUMN currency VARCHAR(10) DEFAULT 'INR' AFTER total;
