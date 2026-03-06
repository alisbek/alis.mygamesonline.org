-- Feltee E-commerce Database Schema
-- Run this SQL to create the database tables

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name_ru` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100) NOT NULL,
    `name_pl` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT,
    `name_ru` VARCHAR(200) NOT NULL,
    `name_en` VARCHAR(200) NOT NULL,
    `name_pl` VARCHAR(200) NOT NULL,
    `description_ru` TEXT,
    `description_en` TEXT,
    `description_pl` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `sizes` JSON,
    `colors` JSON,
    `image` VARCHAR(255),
    `gallery` JSON,
    `stock` INT DEFAULT 0,
    `featured` BOOLEAN DEFAULT 0,
    `seo_title_ru` VARCHAR(200),
    `seo_title_en` VARCHAR(200),
    `seo_title_pl` VARCHAR(200),
    `seo_desc_ru` TEXT,
    `seo_desc_en` TEXT,
    `seo_desc_pl` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100),
    `address` TEXT,
    `city` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `delivery_method` ENUM('pickup','courier','post') NOT NULL,
    `payment_method` ENUM('cash','bank_transfer') NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `notes` TEXT,
    `status` ENUM('new','processing','shipped','completed','cancelled') DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(200),
    `size` VARCHAR(10),
    `color` VARCHAR(50),
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('site_name', 'Feltee Handcraft Studio'),
    ('site_email', 'contact@feltee.com'),
    ('site_phone', '+48 XXX XXX XXX'),
    ('currency', 'PLN'),
    ('currency_symbol', 'zł');

-- Insert categories
INSERT INTO `categories` (`name_ru`, `name_en`, `name_pl`, `slug`) VALUES
    ('Тапочки', 'Slippers', 'Kapcie', 'slippers'),
    ('Ботинки', 'Boots', 'Botki', 'boots'),
    ('Сумки', 'Bags', 'Torby', 'bags'),
    ('Рюкзаки', 'Backpacks', 'Plecaki', 'backpacks'),
    ('Аксессуары', 'Accessories', 'Akcesoria', 'accessories');

-- Insert products
INSERT INTO `products` (`category_id`, `name_ru`, `name_en`, `name_pl`, `description_ru`, `description_en`, `description_pl`, `price`, `sizes`, `colors`, `image`, `stock`, `featured`) VALUES
-- Slippers
(1, 'Фетровые тапочки Natual', 'Felted Slippers Natural', 'Kapcie filcowe Natural', 'Теплые бесшовные тапочки из 100% натуральной овечьей шерсти. Естественная терморегуляция - тепло зимой, прохлада летом.', 'Warm seamless slippers made from 100% natural sheep wool. Natural thermoregulation - warm in winter, cool in summer.', 'Ciepłe bezszwowe kapcie ze 100% naturalnej owczej wełny. Naturalna termoregulacja - ciepło zimą, chłód latem.', 295.00, '["36","37","38","39","40","41","42","43","44","45"]', '[{"name":"Natural","code":"#F5E6D3"},{"name":"Grey","code":"#808080"},{"name":"Brown","code":"#8B4513"}]', 'kapcie-natural-1.jpg', 50, 1),

(1, 'Фетровые тапочки Grey', 'Felted Slippers Grey', 'Kapcie filcowe Grey', 'Серые бесшовные тапочки из натуральной шерсти. Идеально облегают ногу, без швов для максимального комфорта.', 'Grey seamless slippers made from natural wool. Perfectly mold to your foot, seamless for maximum comfort.', 'Szare bezszwowe kapcie z naturalnej wełny. Idealnie dopasowują się do stopy, bez szwów dla maksymalnego komfortu.', 295.00, '["36","37","38","39","40","41","42","43","44","45"]', '[{"name":"Grey","code":"#808080"}]', 'kapcie-grey-1.jpg', 40, 1),

(1, 'Фетровые тапочки Decorated', 'Felted Slippers Decorated', 'Kapcie filcowe Dekorowane', 'Украшенные тапочки из фетра с традиционными кыргызскими узорами. Ручная работа, каждый узор уникален.', 'Decorated felt slippers with traditional Kyrgyz patterns. Handmade, each pattern is unique.', 'Dekorowane kapcie filcowe z tradycyjnymi kirgiskimi wzorami. Ręcznie robione, każdy wzór jest unikalny.', 357.00, '["36","37","38","39","40","41","42","43","44","45"]', '[{"name":"Natural with patterns","code":"#F5E6D3"},{"name":"Grey with patterns","code":"#808080"}]', 'kapcie-decorated-1.jpg', 30, 1),

(1, 'Фетровые тапочки Premium', 'Felted Slippers Premium', 'Kapcie filcowe Premium', 'Премиум тапочки из высококачественной мериносовой шерсти. Усиленная подошва, декоративная отделка.', 'Premium slippers made from high-quality merino wool. Reinforced sole, decorative trim.', 'Kapcie premium z wysokiej jakości wełny merynosowej. Wzmocniona podeszwa, dekoracyjne wykończenie.', 321.00, '["36","37","38","39","40","41","42","43","44","45"]', '[{"name":"Natural","code":"#F5E6D3"},{"name":"Grey","code":"#808080"},{"name":"Brown","code":"#8B4513"}]', 'kapcie-premium-1.jpg', 25, 0),

-- Boots
(2, 'Фетровые ботинки', 'Felted Boots', 'Botki filcowe', 'Теплые зимние ботинки из фетра. Высокая посадка, натуральная шерсть, идеальны для холодных дней.', 'Warm winter felted boots. High cut, natural wool, perfect for cold days.', 'Ciepłe zimowe botki filcowe. Wysokie, z naturalnej wełny, idealne na zimowe dni.', 360.00, '["36","37","38","39","40","41","42","43"]', '[{"name":"Natural","code":"#F5E6D3"},{"name":"Grey","code":"#808080"}]', 'botki-1.jpg', 20, 1),

-- Bags
(3, 'Сумка Crossbody', 'Crossbody Bag', 'Torba crossbody', 'Стильная сумка через плечо из войлока. Натуральная шерсть, компактный размер, регулируемый ремень.', 'Stylish crossbody bag made of felt. Natural wool, compact size, adjustable strap.', 'Stylowa torebka listonoszka z filcu. Naturalna wełna, kompaktowy rozmiar, regulowany pasek.', 185.00, '[]', '[{"name":"Natural","code":"#F5E6D3"},{"name":"Grey","code":"#808080"},{"name":"Brown","code":"#8B4513"}]', 'torba-crossbody-1.jpg', 15, 1),

-- Backpacks
(4, 'Стильный рюкзак из фетра', 'Stylish Felt Backpack', 'Stylowy plecak z filcu', 'Ручная работа из 100% фетра. Вместительный, прочный, с карманами. Уникальный дизайн.', 'Handmade from 100% felt. Spacious, durable, with pockets. Unique design.', 'Ręcznie robiony ze 100% filcu. Pojemny, trwały, z kieszeniami. Unikalny design.', 1200.00, '[]', '[{"name":"Natural","code":"#F5E6D3"},{"name":"Grey","code":"#808080"}]', 'plecak-1.jpg', 10, 0),

-- Accessories
(5, 'Органайзер для ноутбука', 'Laptop Organizer', 'Organizer na laptopa', 'Органайзер для ноутбука с подкладкой для стола. Натуральная шерсть, защита от царапин.', 'Laptop organizer with desk pad. Natural wool, scratch protection.', 'Organizer na laptopa z podkładką biurkową. Naturalna wełna, ochrona przed zarysowaniem.', 320.00, '[]', '[{"name":"Grey","code":"#808080"},{"name":"Natural","code":"#F5E6D3"}]', 'organizer-1.jpg', 20, 0);

-- Insert admin user (password: admin123 - change immediately!)
INSERT INTO `admin_users` (`username`, `password_hash`, `email`) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@feltee.com');

SET FOREIGN_KEY_CHECKS = 1;