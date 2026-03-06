-- InPost Migration: Add product dimensions and InPost order columns
-- Run this on the LIVE database to add the new columns
-- Safe to run multiple times (uses IF NOT EXISTS / column checks)

-- =====================================================
-- 1. Add dimension columns to products table
-- =====================================================

-- Check and add weight_grams
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'weight_grams');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE products ADD COLUMN weight_grams INT DEFAULT NULL AFTER featured', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add length_mm
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'length_mm');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE products ADD COLUMN length_mm INT DEFAULT NULL AFTER weight_grams', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add width_mm
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'width_mm');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE products ADD COLUMN width_mm INT DEFAULT NULL AFTER length_mm', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add height_mm
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'height_mm');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE products ADD COLUMN height_mm INT DEFAULT NULL AFTER width_mm', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 2. Add InPost columns to orders table
-- =====================================================

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'shipping_cost');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(10,2) DEFAULT 0.00 AFTER total', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'inpost_point_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN inpost_point_id VARCHAR(50) DEFAULT NULL AFTER shipping_cost', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'inpost_point_name');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN inpost_point_name VARCHAR(200) DEFAULT NULL AFTER inpost_point_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'inpost_shipment_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN inpost_shipment_id VARCHAR(100) DEFAULT NULL AFTER inpost_point_name', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'inpost_tracking_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN inpost_tracking_number VARCHAR(100) DEFAULT NULL AFTER inpost_shipment_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'inpost_status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE orders ADD COLUMN inpost_status VARCHAR(50) DEFAULT NULL AFTER inpost_tracking_number', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Expand delivery_method ENUM to include 'inpost'
-- This is safe: if 'inpost' already exists in the ENUM, MySQL will just re-apply the same definition
ALTER TABLE orders MODIFY COLUMN delivery_method ENUM('pickup','courier','post','inpost') NOT NULL;

-- =====================================================
-- 3. Populate existing products with default dimensions by category
-- =====================================================
-- Category mapping: 1=Slippers, 2=Boots, 3=Bags, 4=Backpacks, 5=Accessories

-- Slippers: ~300g, 300x200x80mm (fits InPost small/A locker)
UPDATE products SET weight_grams = 300, length_mm = 300, width_mm = 200, height_mm = 80
WHERE category_id = 1 AND (weight_grams IS NULL OR weight_grams = 0);

-- Boots: ~410g, 350x250x150mm (fits InPost medium/B locker)
UPDATE products SET weight_grams = 410, length_mm = 350, width_mm = 250, height_mm = 150
WHERE category_id = 2 AND (weight_grams IS NULL OR weight_grams = 0);

-- Bags: ~300g, 260x145x125mm (fits InPost small/A locker)
UPDATE products SET weight_grams = 300, length_mm = 260, width_mm = 145, height_mm = 125
WHERE category_id = 3 AND (weight_grams IS NULL OR weight_grams = 0);

-- Backpacks: ~880g, 420x300x120mm (fits InPost medium/B locker)
UPDATE products SET weight_grams = 880, length_mm = 420, width_mm = 300, height_mm = 120
WHERE category_id = 4 AND (weight_grams IS NULL OR weight_grams = 0);

-- Accessories: ~500g, 380x300x10mm (fits InPost small/A locker — flat items)
UPDATE products SET weight_grams = 500, length_mm = 380, width_mm = 300, height_mm = 10
WHERE category_id = 5 AND (weight_grams IS NULL OR weight_grams = 0);

-- Any uncategorized products: default medium size
UPDATE products SET weight_grams = 400, length_mm = 350, width_mm = 250, height_mm = 100
WHERE category_id IS NULL AND (weight_grams IS NULL OR weight_grams = 0);
