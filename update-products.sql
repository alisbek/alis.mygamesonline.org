-- Add missing columns to products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS weight_grams INT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS length_mm INT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS width_mm INT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS height_mm INT NULL;

-- Add missing columns to orders table
ALTER TABLE orders ADD COLUMN IF NOT EXISTS locker_id VARCHAR(32) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS locker_name VARCHAR(255) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_type VARCHAR(32) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(64) NULL;

-- Update product dimensions
UPDATE products SET weight_grams=300, length_mm=300, width_mm=200, height_mm=80 WHERE id BETWEEN 1 AND 20;
