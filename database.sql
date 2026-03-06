-- Feltee E-commerce Database Schema
-- Run this SQL to create the database tables

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name_pl` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100) NOT NULL,
    `name_ru` VARCHAR(100) NOT NULL,
    `name_de` VARCHAR(100) DEFAULT '',
    `name_fr` VARCHAR(100) DEFAULT '',
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT,
    `name_pl` VARCHAR(200) NOT NULL,
    `name_en` VARCHAR(200) NOT NULL,
    `name_ru` VARCHAR(200) NOT NULL,
    `name_de` VARCHAR(200) DEFAULT '',
    `name_fr` VARCHAR(200) DEFAULT '',
    `description_pl` TEXT,
    `description_en` TEXT,
    `description_ru` TEXT,
    `description_de` TEXT,
    `description_fr` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `sizes` JSON,
    `colors` JSON,
    `image` VARCHAR(255),
    `gallery` JSON,
    `stock` INT DEFAULT 0,
    `featured` BOOLEAN DEFAULT 0,
    `seo_title_pl` VARCHAR(200),
    `seo_title_en` VARCHAR(200),
    `seo_title_ru` VARCHAR(200),
    `seo_title_de` VARCHAR(200),
    `seo_title_fr` VARCHAR(200),
    `seo_desc_pl` TEXT,
    `seo_desc_en` TEXT,
    `seo_desc_ru` TEXT,
    `seo_desc_de` TEXT,
    `seo_desc_fr` TEXT,
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
    `payment_method` ENUM('cash','bank_transfer','payu') NOT NULL,
    `payment_status` VARCHAR(20) DEFAULT 'pending',
    `payu_order_id` VARCHAR(255) DEFAULT NULL,
    `transaction_id` VARCHAR(255) DEFAULT NULL,
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
    ('currency_symbol', 'zl');

-- Insert categories (5 languages)
INSERT INTO `categories` (`name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `slug`) VALUES
    ('Kapcie', 'Slippers', 'Тапочки', 'Hausschuhe', 'Chaussons', 'slippers'),
    ('Botki', 'Boots', 'Ботинки', 'Stiefeletten', 'Bottines', 'boots'),
    ('Torby', 'Bags', 'Сумки', 'Taschen', 'Sacs', 'bags'),
    ('Plecaki', 'Backpacks', 'Рюкзаки', 'Rucksäcke', 'Sacs à dos', 'backpacks'),
    ('Akcesoria', 'Accessories', 'Аксессуары', 'Zubehör', 'Accessoires', 'accessories');

-- =====================================================
-- PRODUCTS: 20 real products from Pakamera.pl
-- All descriptions are original translations per language
-- =====================================================

-- Product 1: Botki filcowe (nr4168655) - 360 PLN, 8 color variants
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(2,
'Botki filcowe z naturalnej owczej wełny',
'Felted Ankle Boots from Natural Sheep Wool',
'Войлочные ботинки из натуральной овечьей шерсти',
'Filzstiefeletten aus natürlicher Schafwolle',
'Bottines en feutre de laine de mouton naturelle',
'Ręcznie filcowane botki z naturalnej owczej wełny. Podeszwa z naturalnej skóry cielęcej. Wełna ma właściwości termoregulacyjne – rozgrzewa zimą i daje przyjemny chłód latem. Właściwości antybakteryjne, ergonomiczny kształt dopasowujący się do stopy. Oddychająca struktura, brak szwów. Waga: ok. 410 g.',
'Handmade felted ankle boots from natural sheep wool. Sole made of natural calf leather. Wool has thermoregulatory properties - warms in winter and keeps cool in summer. Antibacterial properties, ergonomic shape that molds to the foot. Breathable structure, no seams. Weight: approx. 410 g.',
'Войлочные ботинки ручной работы из натуральной овечьей шерсти. Подошва из натуральной телячьей кожи. Шерсть обладает терморегулирующими свойствами – согревает зимой и дарит приятную прохладу летом. Антибактериальные свойства, эргономичная форма, повторяющая контуры стопы. Дышащая структура, без швов. Вес: ок. 410 г.',
'Handgefilzte Stiefeletten aus natürlicher Schafwolle. Sohle aus natürlichem Kalbsleder. Wolle hat thermoregulierende Eigenschaften – wärmt im Winter und kühlt angenehm im Sommer. Antibakterielle Eigenschaften, ergonomische Form, die sich dem Fuß anpasst. Atmungsaktive Struktur, keine Nähte. Gewicht: ca. 410 g.',
'Bottines feutrées à la main en laine de mouton naturelle. Semelle en cuir de veau naturel. La laine possède des propriétés thermorégulatrices – elle réchauffe en hiver et offre une fraîcheur agréable en été. Propriétés antibactériennes, forme ergonomique épousant le pied. Structure respirante, sans coutures. Poids : env. 410 g.',
360.00,
'["36","37","38","39","40","41","42","43"]',
'[{"name":"Yellow","code":"#D4A017"},{"name":"Red","code":"#B85450"},{"name":"Dark Purple","code":"#4B0082"},{"name":"Green","code":"#6B8E6B"},{"name":"Brown","code":"#8B4513"},{"name":"Black","code":"#222222"},{"name":"Purple","code":"#6B3FA0"},{"name":"Blue","code":"#4A6FA5"}]',
'botki-filcowe-01.jpg',
'["botki-filcowe-02.jpg","botki-filcowe-03.jpg","botki-filcowe-04.jpg","botki-filcowe-05.jpg","botki-filcowe-06.jpg","botki-filcowe-07.jpg","botki-filcowe-08.jpg","botki-filcowe-09.jpg","botki-filcowe-10.jpg"]',
20, 1);

-- Product 2: Kapcie otwarte kolorowe (nr4168577) - 310 PLN, 8 colors
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe otwarte z naturalnej owczej wełny',
'Open Felted Slippers from Natural Sheep Wool',
'Открытые войлочные тапочки из натуральной овечьей шерсти',
'Offene Filzhausschuhe aus natürlicher Schafwolle',
'Chaussons ouverts en feutre de laine de mouton naturelle',
'Otwarte kapcie filcowe ręcznie wykonane z naturalnej owczej wełny. Podeszwa z naturalnej skóry. Bezszwowa konstrukcja idealnie dopasowująca się do stopy. Termoregulacja, oddychalność, właściwości antybakteryjne. Lekkie i wygodne na co dzień.',
'Open felted slippers handmade from natural sheep wool. Natural leather sole. Seamless construction that perfectly molds to the foot. Thermoregulation, breathability, antibacterial properties. Lightweight and comfortable for everyday wear.',
'Открытые войлочные тапочки ручной работы из натуральной овечьей шерсти. Подошва из натуральной кожи. Бесшовная конструкция, идеально повторяющая форму стопы. Терморегуляция, воздухопроницаемость, антибактериальные свойства. Лёгкие и удобные для повседневной носки.',
'Offene Filzhausschuhe, handgefertigt aus natürlicher Schafwolle. Sohle aus Naturleder. Nahtlose Konstruktion, die sich perfekt dem Fuß anpasst. Thermoregulierung, Atmungsaktivität, antibakterielle Eigenschaften. Leicht und bequem für den Alltag.',
'Chaussons ouverts en feutre faits main en laine de mouton naturelle. Semelle en cuir naturel. Construction sans coutures épousant parfaitement le pied. Thermorégulation, respirabilité, propriétés antibactériennes. Légers et confortables au quotidien.',
310.00,
'["36","37","38","39","40","41","42","43","44","45"]',
'[{"name":"Yellow","code":"#D4A017"},{"name":"Red","code":"#B85450"},{"name":"Dark Purple","code":"#4B0082"},{"name":"Green","code":"#6B8E6B"},{"name":"Brown","code":"#8B4513"},{"name":"Black","code":"#222222"},{"name":"Purple","code":"#6B3FA0"},{"name":"Blue","code":"#4A6FA5"}]',
'kapcie-otwarte-01.jpg',
'["kapcie-otwarte-02.jpg","kapcie-otwarte-03.jpg","kapcie-otwarte-04.jpg","kapcie-otwarte-05.jpg","kapcie-otwarte-06.jpg","kapcie-otwarte-07.jpg","kapcie-otwarte-08.jpg","kapcie-otwarte-09.jpg","kapcie-otwarte-10.jpg"]',
40, 1);

-- Product 3: Kapcie dekorowane (nr3949872) - 357 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe dekorowane z owczej wełny',
'Decorated Felted Slippers from Sheep Wool',
'Декорированные войлочные тапочки из овечьей шерсти',
'Dekorierte Filzhausschuhe aus Schafwolle',
'Chaussons en feutre décorés en laine de mouton',
'Pięknie dekorowane kapcie filcowe z naturalnej owczej wełny. Ręczne zdobienia nadają każdej parze unikalny charakter. Podeszwa z naturalnej skóry, bezszwowa konstrukcja. Termoregulacja i oddychalność.',
'Beautifully decorated felted slippers from natural sheep wool. Hand decorations give each pair a unique character. Natural leather sole, seamless construction. Thermoregulation and breathability.',
'Красиво декорированные войлочные тапочки из натуральной овечьей шерсти. Ручные украшения придают каждой паре уникальный характер. Подошва из натуральной кожи, бесшовная конструкция. Терморегуляция и воздухопроницаемость.',
'Wunderschön dekorierte Filzhausschuhe aus natürlicher Schafwolle. Handverzierungen verleihen jedem Paar einen einzigartigen Charakter. Naturledersohle, nahtlose Konstruktion. Thermoregulierung und Atmungsaktivität.',
'Chaussons en feutre magnifiquement décorés en laine de mouton naturelle. Les décorations manuelles confèrent à chaque paire un caractère unique. Semelle en cuir naturel, construction sans coutures. Thermorégulation et respirabilité.',
357.00,
'["38"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-dekorowane-01.jpg',
'["kapcie-dekorowane-02.jpg","kapcie-dekorowane-03.jpg","kapcie-dekorowane-04.jpg","kapcie-dekorowane-05.jpg"]',
5, 1);

-- Product 4: Kapcie męskie brązowe (nr3920892) - 295 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe męskie z owczej wełny',
'Men''s Felted Slippers from Sheep Wool',
'Мужские войлочные тапочки из овечьей шерсти',
'Herren-Filzhausschuhe aus Schafwolle',
'Chaussons en feutre pour homme en laine de mouton',
'Męskie kapcie filcowe ręcznie wykonane z naturalnej owczej wełny. Podeszwa z naturalnej skóry, bezszwowy krój. Idealne dopasowanie do stopy, termoregulacja, właściwości antybakteryjne. Wygoda i ciepło na co dzień.',
'Men''s felted slippers handmade from natural sheep wool. Natural leather sole, seamless cut. Perfect fit to the foot, thermoregulation, antibacterial properties. Comfort and warmth every day.',
'Мужские войлочные тапочки ручной работы из натуральной овечьей шерсти. Подошва из натуральной кожи, бесшовный крой. Идеальная посадка по ноге, терморегуляция, антибактериальные свойства. Комфорт и тепло на каждый день.',
'Herren-Filzhausschuhe, handgefertigt aus natürlicher Schafwolle. Naturledersohle, nahtloser Schnitt. Perfekte Passform, Thermoregulierung, antibakterielle Eigenschaften. Komfort und Wärme für jeden Tag.',
'Chaussons en feutre pour homme faits main en laine de mouton naturelle. Semelle en cuir naturel, coupe sans coutures. Ajustement parfait au pied, thermorégulation, propriétés antibactériennes. Confort et chaleur au quotidien.',
295.00,
'["44"]',
'[{"name":"Brown","code":"#8B4513"}]',
'kapcie-meskie-brazowe-01.jpg',
'["kapcie-meskie-brazowe-02.jpg","kapcie-meskie-brazowe-03.jpg","kapcie-meskie-brazowe-04.jpg"]',
5, 0);

-- Product 5: Kapcie damskie szare (nr3920889) - 295 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe damskie szare',
'Women''s Grey Felted Slippers',
'Женские серые войлочные тапочки',
'Damen-Filzhausschuhe in Grau',
'Chaussons en feutre gris pour femme',
'Damskie kapcie filcowe w kolorze szarym z naturalnej owczej wełny. Bezszwowa technologia filcowania zapewnia idealny komfort. Podeszwa z naturalnej skóry. Oddychające, ciepłe, antybakteryjne.',
'Women''s grey felted slippers from natural sheep wool. Seamless felting technology ensures ideal comfort. Natural leather sole. Breathable, warm, antibacterial.',
'Женские серые войлочные тапочки из натуральной овечьей шерсти. Бесшовная технология валяния обеспечивает идеальный комфорт. Подошва из натуральной кожи. Дышащие, тёплые, антибактериальные.',
'Damen-Filzhausschuhe in Grau aus natürlicher Schafwolle. Nahtlose Filztechnik sorgt für idealen Komfort. Naturledersohle. Atmungsaktiv, warm, antibakteriell.',
'Chaussons en feutre gris pour femme en laine de mouton naturelle. La technique de feutrage sans coutures assure un confort idéal. Semelle en cuir naturel. Respirants, chauds, antibactériens.',
295.00,
'["38","39"]',
'[{"name":"Grey","code":"#808080"}]',
'kapcie-damskie-szare-01.jpg',
'["kapcie-damskie-szare-02.jpg","kapcie-damskie-szare-03.jpg"]',
10, 0);

-- Product 6: Kapcie męskie szare (nr3920887) - 295 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe męskie szare',
'Men''s Grey Felted Slippers',
'Мужские серые войлочные тапочки',
'Herren-Filzhausschuhe in Grau',
'Chaussons en feutre gris pour homme',
'Męskie kapcie filcowe w szarym kolorze. Wykonane z naturalnej owczej wełny techniką bezszwowego filcowania. Podeszwa z naturalnej skóry. Termoregulacja, oddychalność, antybakteryjna ochrona.',
'Men''s grey felted slippers. Made from natural sheep wool using seamless felting technique. Natural leather sole. Thermoregulation, breathability, antibacterial protection.',
'Мужские серые войлочные тапочки. Изготовлены из натуральной овечьей шерсти методом бесшовного валяния. Подошва из натуральной кожи. Терморегуляция, воздухопроницаемость, антибактериальная защита.',
'Herren-Filzhausschuhe in Grau. Aus natürlicher Schafwolle in nahtloser Filztechnik gefertigt. Naturledersohle. Thermoregulierung, Atmungsaktivität, antibakterieller Schutz.',
'Chaussons en feutre gris pour homme. Fabriqués en laine de mouton naturelle par technique de feutrage sans coutures. Semelle en cuir naturel. Thermorégulation, respirabilité, protection antibactérienne.',
295.00,
'["41","43"]',
'[{"name":"Grey","code":"#808080"}]',
'kapcie-meskie-szare-01.jpg',
'["kapcie-meskie-szare-02.jpg","kapcie-meskie-szare-03.jpg","kapcie-meskie-szare-04.jpg"]',
10, 0);

-- Product 7: Kapcie beżowe (nr3920883) - 295 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe beżowe z owczej wełny',
'Beige Felted Slippers from Sheep Wool',
'Бежевые войлочные тапочки из овечьей шерсти',
'Beige Filzhausschuhe aus Schafwolle',
'Chaussons en feutre beige en laine de mouton',
'Kapcie filcowe w kolorze beżowym z naturalnej owczej wełny. Podeszwa z naturalnej skóry. Bezszwowa konstrukcja, termoregulacja, oddychalność. Ręczne wykonanie gwarantuje najwyższą jakość.',
'Beige felted slippers from natural sheep wool. Natural leather sole. Seamless construction, thermoregulation, breathability. Handmade craftsmanship guarantees the highest quality.',
'Бежевые войлочные тапочки из натуральной овечьей шерсти. Подошва из натуральной кожи. Бесшовная конструкция, терморегуляция, воздухопроницаемость. Ручная работа гарантирует высочайшее качество.',
'Beige Filzhausschuhe aus natürlicher Schafwolle. Naturledersohle. Nahtlose Konstruktion, Thermoregulierung, Atmungsaktivität. Handarbeit garantiert höchste Qualität.',
'Chaussons en feutre beige en laine de mouton naturelle. Semelle en cuir naturel. Construction sans coutures, thermorégulation, respirabilité. La fabrication artisanale garantit la plus haute qualité.',
295.00,
'["38","39","43"]',
'[{"name":"Beige","code":"#D4B896"}]',
'kapcie-bezowe-01.jpg',
'["kapcie-bezowe-02.jpg","kapcie-bezowe-03.jpg","kapcie-bezowe-04.jpg"]',
15, 1);

-- Product 8: Kapcie męskie ciemne (nr3920872) - 295 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe męskie ciemne',
'Men''s Dark Felted Slippers',
'Мужские тёмные войлочные тапочки',
'Dunkle Herren-Filzhausschuhe',
'Chaussons en feutre foncés pour homme',
'Ciemne kapcie filcowe z naturalnej owczej wełny. Bezszwowa konstrukcja, podeszwa z naturalnej skóry. Wygodne, ciepłe, oddychające. Idealne na co dzień i na prezent.',
'Dark felted slippers from natural sheep wool. Seamless construction, natural leather sole. Comfortable, warm, breathable. Perfect for everyday wear and as a gift.',
'Тёмные войлочные тапочки из натуральной овечьей шерсти. Бесшовная конструкция, подошва из натуральной кожи. Удобные, тёплые, дышащие. Идеальны для повседневной носки и в подарок.',
'Dunkle Filzhausschuhe aus natürlicher Schafwolle. Nahtlose Konstruktion, Naturledersohle. Bequem, warm, atmungsaktiv. Perfekt für den Alltag und als Geschenk.',
'Chaussons en feutre foncés en laine de mouton naturelle. Construction sans coutures, semelle en cuir naturel. Confortables, chauds, respirants. Parfaits au quotidien et en cadeau.',
295.00,
'["37","38","39","40","41"]',
'[{"name":"Brown","code":"#8B4513"}]',
'kapcie-meskie-ciemne-01.jpg',
'["kapcie-meskie-ciemne-02.jpg","kapcie-meskie-ciemne-03.jpg","kapcie-meskie-ciemne-04.jpg"]',
20, 0);

-- Product 9: Kapcie męskie naturalne (nr3920861) - 295 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe męskie naturalne',
'Men''s Natural Felted Slippers',
'Мужские натуральные войлочные тапочки',
'Herren-Filzhausschuhe in Naturfarbe',
'Chaussons en feutre naturels pour homme',
'Męskie kapcie filcowe w naturalnym kolorze wełny. Bezszwowa technologia filcowania z owczej wełny. Podeszwa ze skóry naturalnej. Ergonomiczny kształt, termoregulacja, lekkość.',
'Men''s felted slippers in natural wool color. Seamless felting technology from sheep wool. Natural leather sole. Ergonomic shape, thermoregulation, lightweight.',
'Мужские войлочные тапочки натурального цвета шерсти. Бесшовная технология валяния из овечьей шерсти. Подошва из натуральной кожи. Эргономичная форма, терморегуляция, лёгкость.',
'Herren-Filzhausschuhe in natürlicher Wollfarbe. Nahtlose Filztechnik aus Schafwolle. Naturledersohle. Ergonomische Form, Thermoregulierung, Leichtigkeit.',
'Chaussons en feutre pour homme couleur laine naturelle. Technique de feutrage sans coutures en laine de mouton. Semelle en cuir naturel. Forme ergonomique, thermorégulation, légèreté.',
295.00,
'["35","38","39","40"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-meskie-naturalne-01.jpg',
'["kapcie-meskie-naturalne-02.jpg","kapcie-meskie-naturalne-03.jpg","kapcie-meskie-naturalne-04.jpg","kapcie-meskie-naturalne-05.jpg","kapcie-meskie-naturalne-06.jpg"]',
15, 0);

-- Product 10: Kapcie dekorowane premium (nr3884434) - 357 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe dekorowane premium',
'Premium Decorated Felted Slippers',
'Декорированные войлочные тапочки премиум',
'Premium dekorierte Filzhausschuhe',
'Chaussons en feutre décorés premium',
'Ekskluzywne kapcie filcowe z bogato zdobioną powierzchnią. Ręczne dekoracje inspirowane tradycyjnymi kirgiskimi motywami. Naturalna owcza wełna, skórzana podeszwa, bezszwowa konstrukcja.',
'Exclusive felted slippers with richly decorated surface. Hand decorations inspired by traditional Kyrgyz motifs. Natural sheep wool, leather sole, seamless construction.',
'Эксклюзивные войлочные тапочки с богато декорированной поверхностью. Ручные украшения, вдохновлённые традиционными кыргызскими мотивами. Натуральная овечья шерсть, кожаная подошва, бесшовная конструкция.',
'Exklusive Filzhausschuhe mit reich verzierter Oberfläche. Handverzierungen inspiriert von traditionellen kirgisischen Motiven. Natürliche Schafwolle, Ledersohle, nahtlose Konstruktion.',
'Chaussons en feutre exclusifs à la surface richement décorée. Décorations manuelles inspirées des motifs traditionnels kirghiz. Laine de mouton naturelle, semelle en cuir, construction sans coutures.',
357.00,
'["39","41"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-dekorowane-premium-01.jpg',
'["kapcie-dekorowane-premium-02.jpg","kapcie-dekorowane-premium-03.jpg","kapcie-dekorowane-premium-04.jpg","kapcie-dekorowane-premium-05.jpg","kapcie-dekorowane-premium-06.jpg","kapcie-dekorowane-premium-07.jpg"]',
10, 1);

-- Product 11: Torba crossbody (nr3865317) - 185 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(3,
'Torba crossbody z filcu',
'Felt Crossbody Bag',
'Войлочная сумка через плечо',
'Filz-Umhängetasche',
'Sac bandoulière en feutre',
'Elegancka torba crossbody wykonana ze 100% wełny z elementami ze skóry naturalnej wyprawianej roślinnie. Wymiary: 26 x 14.5 x 12.5 cm. Waga: 0.3 kg. Klasyczna czerń, idealna na co dzień i na wyjścia.',
'Elegant crossbody bag made from 100% wool with vegetable-tanned natural leather elements. Dimensions: 26 x 14.5 x 12.5 cm. Weight: 0.3 kg. Classic black, perfect for everyday and outings.',
'Элегантная сумка через плечо из 100% шерсти с элементами натуральной кожи растительного дубления. Размеры: 26 x 14.5 x 12.5 см. Вес: 0.3 кг. Классический чёрный цвет, идеальна для повседневности и выходов.',
'Elegante Umhängetasche aus 100% Wolle mit Elementen aus pflanzlich gegerbtem Naturleder. Maße: 26 x 14,5 x 12,5 cm. Gewicht: 0,3 kg. Klassisches Schwarz, perfekt für den Alltag und Ausgänge.',
'Sac bandoulière élégant en 100% laine avec des éléments en cuir naturel tanné végétal. Dimensions : 26 x 14,5 x 12,5 cm. Poids : 0,3 kg. Noir classique, parfait au quotidien et en sortie.',
185.00,
'[]',
'[{"name":"Black","code":"#222222"}]',
'torba-crossbody-01.jpg',
'["torba-crossbody-02.jpg","torba-crossbody-03.jpg"]',
15, 1);

-- Product 12: Kapcie Ala Too (nr3852849) - 357 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe Ala Too z owczej wełny',
'Felted Slippers Ala Too from Sheep Wool',
'Войлочные тапочки Ала Тоо из овечьей шерсти',
'Filzhausschuhe Ala Too aus Schafwolle',
'Chaussons en feutre Ala Too en laine de mouton',
'Kapcie filcowe z kolekcji Ala Too. Nazwa nawiązuje do pasma górskiego w Kirgistanie. Ręcznie filcowane z naturalnej owczej wełny, podeszwa ze skóry naturalnej. Dekoracyjne wzory inspirowane górskim krajobrazem.',
'Felted slippers from the Ala Too collection. Named after a mountain range in Kyrgyzstan. Handmade felted from natural sheep wool, natural leather sole. Decorative patterns inspired by mountain landscapes.',
'Войлочные тапочки из коллекции Ала Тоо. Название отсылает к горному хребту в Кыргызстане. Ручное валяние из натуральной овечьей шерсти, подошва из натуральной кожи. Декоративные узоры, вдохновлённые горными пейзажами.',
'Filzhausschuhe aus der Kollektion Ala Too. Benannt nach einer Bergkette in Kirgisistan. Handgefilzt aus natürlicher Schafwolle, Naturledersohle. Dekorative Muster inspiriert von Berglandschaften.',
'Chaussons en feutre de la collection Ala Too. Nommés d''après une chaîne de montagnes du Kirghizistan. Feutrés à la main en laine de mouton naturelle, semelle en cuir naturel. Motifs décoratifs inspirés des paysages montagneux.',
357.00,
'["40"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-ala-too-01.jpg',
'["kapcie-ala-too-02.jpg","kapcie-ala-too-03.jpg"]',
5, 0);

-- Product 13: Kapcie Pamir (nr3852828) - 357 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe Pamir z owczej wełny',
'Felted Slippers Pamir from Sheep Wool',
'Войлочные тапочки Памир из овечьей шерсти',
'Filzhausschuhe Pamir aus Schafwolle',
'Chaussons en feutre Pamir en laine de mouton',
'Kapcie filcowe z kolekcji Pamir. Inspirowane majestatycznymi górami Pamiru. Ręczne filcowanie z owczej wełny, skórzana podeszwa. Bezszwowa konstrukcja, bogata dekoracja.',
'Felted slippers from the Pamir collection. Inspired by the majestic Pamir mountains. Hand-felted from sheep wool, leather sole. Seamless construction, rich decoration.',
'Войлочные тапочки из коллекции Памир. Вдохновлены величественными горами Памира. Ручное валяние из овечьей шерсти, кожаная подошва. Бесшовная конструкция, богатая декорация.',
'Filzhausschuhe aus der Kollektion Pamir. Inspiriert von den majestätischen Pamir-Bergen. Handgefilzt aus Schafwolle, Ledersohle. Nahtlose Konstruktion, reiche Dekoration.',
'Chaussons en feutre de la collection Pamir. Inspirés par les majestueuses montagnes du Pamir. Feutrés à la main en laine de mouton, semelle en cuir. Construction sans coutures, riche décoration.',
357.00,
'["36"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-pamir-01.jpg',
'["kapcie-pamir-02.jpg","kapcie-pamir-03.jpg","kapcie-pamir-04.jpg"]',
5, 0);

-- Product 14: Organizer na laptopa (nr3852801) - 320 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(5,
'Organizer na laptopa z podkładką biurkową',
'Laptop Organizer with Desk Pad',
'Органайзер для ноутбука с настольным ковриком',
'Laptop-Organizer mit Schreibtischunterlage',
'Organiseur pour ordinateur portable avec sous-main',
'Organizer na laptopa z podkładką biurkową. 90% wełna, 10% poliester z elementami ze skóry naturalnej w kolorze beżowym. Trzyczęściowa konstrukcja 83x38 cm. Chroni laptop, porządkuje biurko.',
'Laptop organizer with desk pad. 90% wool, 10% polyester with natural beige leather elements. Three-part construction 83x38 cm. Protects laptop, organizes desk.',
'Органайзер для ноутбука с настольным ковриком. 90% шерсть, 10% полиэстер с элементами из натуральной бежевой кожи. Трёхчастная конструкция 83x38 см. Защищает ноутбук, организует рабочий стол.',
'Laptop-Organizer mit Schreibtischunterlage. 90% Wolle, 10% Polyester mit Elementen aus natürlichem beigem Leder. Dreiteilige Konstruktion 83x38 cm. Schützt den Laptop, organisiert den Schreibtisch.',
'Organiseur pour ordinateur portable avec sous-main. 90% laine, 10% polyester avec des éléments en cuir naturel beige. Construction en trois parties 83x38 cm. Protège l''ordinateur, organise le bureau.',
320.00,
'[]',
'[{"name":"Grey","code":"#808080"}]',
'organizer-laptopa-01.jpg',
'["organizer-laptopa-02.jpg","organizer-laptopa-03.jpg","organizer-laptopa-04.jpg"]',
20, 1);

-- Product 15: Stylowy plecak (nr3852043) - 1200 PLN
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(4,
'Stylowy plecak z kolekcji akcesoriów biznesowych',
'Stylish Backpack from Business Accessories Collection',
'Стильный рюкзак из коллекции бизнес-аксессуаров',
'Stilvoller Rucksack aus der Business-Accessoires-Kollektion',
'Sac à dos élégant de la collection accessoires business',
'Stylowy plecak ze 100% wełny (3.5-4mm) z elementami ze skóry naturalnej wyprawianej. Przegroda na ultrabooka, 2 kieszenie. Wymiary: 42x30x12 cm. Waga: 0.880 kg. Wielokomorowy, idealne wykończenie.',
'Stylish backpack from 100% wool (3.5-4mm) with tanned natural leather elements. Ultrabook compartment, 2 pockets. Dimensions: 42x30x12 cm. Weight: 0.880 kg. Multi-compartment, impeccable finish.',
'Стильный рюкзак из 100% шерсти (3.5-4мм) с элементами натуральной выделанной кожи. Отделение для ультрабука, 2 кармана. Размеры: 42x30x12 см. Вес: 0.880 кг. Многосекционный, безупречная отделка.',
'Stilvoller Rucksack aus 100% Wolle (3,5-4mm) mit Elementen aus gegerbtem Naturleder. Ultrabook-Fach, 2 Taschen. Maße: 42x30x12 cm. Gewicht: 0,880 kg. Mehrere Fächer, makellose Verarbeitung.',
'Sac à dos élégant en 100% laine (3,5-4mm) avec des éléments en cuir naturel tanné. Compartiment ultrabook, 2 poches. Dimensions : 42x30x12 cm. Poids : 0,880 kg. Multi-compartiments, finition impeccable.',
1200.00,
'[]',
'[{"name":"Grey","code":"#808080"}]',
'plecak-biznesowy-01.jpg',
'["plecak-biznesowy-02.jpg","plecak-biznesowy-03.jpg","plecak-biznesowy-04.jpg","plecak-biznesowy-05.jpg"]',
10, 1);

-- Product 16: Kapcie Pamir 2 (nr3850534) - 321 PLN (sold out)
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe Pamir z dekoracją',
'Felted Slippers Pamir with Decoration',
'Войлочные тапочки Памир с декором',
'Filzhausschuhe Pamir mit Dekoration',
'Chaussons en feutre Pamir avec décoration',
'Kapcie filcowe z kolekcji Pamir z delikatną dekoracją. Ręcznie wykonane z naturalnej owczej wełny, skórzana podeszwa. Wzory inspirowane kirgiskimi tradycjami.',
'Felted slippers from the Pamir collection with delicate decoration. Handmade from natural sheep wool, leather sole. Patterns inspired by Kyrgyz traditions.',
'Войлочные тапочки из коллекции Памир с деликатным декором. Ручная работа из натуральной овечьей шерсти, кожаная подошва. Узоры вдохновлены кыргызскими традициями.',
'Filzhausschuhe aus der Kollektion Pamir mit feiner Dekoration. Handgefertigt aus natürlicher Schafwolle, Ledersohle. Muster inspiriert von kirgisischen Traditionen.',
'Chaussons en feutre de la collection Pamir avec une décoration délicate. Faits main en laine de mouton naturelle, semelle en cuir. Motifs inspirés des traditions kirghizes.',
321.00,
'["40"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-pamir-deko-01.jpg',
'["kapcie-pamir-deko-02.jpg","kapcie-pamir-deko-03.jpg","kapcie-pamir-deko-04.jpg","kapcie-pamir-deko-05.jpg"]',
0, 0);

-- Product 17: Kapcie męskie filcowe (nr3884412) - 357 PLN (sold out)
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe męskie klasyczne',
'Classic Men''s Felted Slippers',
'Классические мужские войлочные тапочки',
'Klassische Herren-Filzhausschuhe',
'Chaussons en feutre classiques pour homme',
'Klasyczne męskie kapcie filcowe z naturalnej owczej wełny. Minimalistyczny design, bezszwowa konstrukcja, skórzana podeszwa. Termoregulacja i komfort na co dzień.',
'Classic men''s felted slippers from natural sheep wool. Minimalist design, seamless construction, leather sole. Thermoregulation and everyday comfort.',
'Классические мужские войлочные тапочки из натуральной овечьей шерсти. Минималистичный дизайн, бесшовная конструкция, кожаная подошва. Терморегуляция и комфорт на каждый день.',
'Klassische Herren-Filzhausschuhe aus natürlicher Schafwolle. Minimalistisches Design, nahtlose Konstruktion, Ledersohle. Thermoregulierung und Komfort für jeden Tag.',
'Chaussons en feutre classiques pour homme en laine de mouton naturelle. Design minimaliste, construction sans coutures, semelle en cuir. Thermorégulation et confort au quotidien.',
357.00,
'["41"]',
'[{"name":"Grey","code":"#808080"}]',
'kapcie-meskie-klasyczne-01.jpg',
'["kapcie-meskie-klasyczne-02.jpg","kapcie-meskie-klasyczne-03.jpg","kapcie-meskie-klasyczne-04.jpg"]',
0, 0);

-- Product 18: Kapcie zdobione (nr3892247) - 357 PLN (sold out)
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe artystycznie zdobione',
'Artistically Decorated Felted Slippers',
'Художественно украшенные войлочные тапочки',
'Künstlerisch verzierte Filzhausschuhe',
'Chaussons en feutre artistiquement décorés',
'Artystycznie zdobione kapcie filcowe z naturalnej owczej wełny. Każda para to unikatowe dzieło rzemiosła. Bezszwowa konstrukcja, skórzana podeszwa, termoregulacja.',
'Artistically decorated felted slippers from natural sheep wool. Each pair is a unique work of craftsmanship. Seamless construction, leather sole, thermoregulation.',
'Художественно украшенные войлочные тапочки из натуральной овечьей шерсти. Каждая пара — уникальное произведение ремесла. Бесшовная конструкция, кожаная подошва, терморегуляция.',
'Künstlerisch verzierte Filzhausschuhe aus natürlicher Schafwolle. Jedes Paar ist ein einzigartiges Handwerkskunstwerk. Nahtlose Konstruktion, Ledersohle, Thermoregulierung.',
'Chaussons en feutre artistiquement décorés en laine de mouton naturelle. Chaque paire est une œuvre artisanale unique. Construction sans coutures, semelle en cuir, thermorégulation.',
357.00,
'["42"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-artystyczne-01.jpg',
'["kapcie-artystyczne-02.jpg","kapcie-artystyczne-03.jpg"]',
0, 0);

-- Product 19: Kapcie premium wzorzyste (nr3852906) - 360 PLN (sold out)
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe premium z wzorami',
'Premium Patterned Felted Slippers',
'Войлочные тапочки премиум с узорами',
'Premium-Filzhausschuhe mit Mustern',
'Chaussons en feutre premium à motifs',
'Kapcie filcowe premium z ręcznie naniesionymi wzorami. Naturalna owcza wełna, skórzana podeszwa, bezszwowa technologia. Unikalne wzory inspirowane koczowniczą tradycją.',
'Premium felted slippers with hand-applied patterns. Natural sheep wool, leather sole, seamless technology. Unique patterns inspired by nomadic tradition.',
'Войлочные тапочки премиум с нанесёнными вручную узорами. Натуральная овечья шерсть, кожаная подошва, бесшовная технология. Уникальные узоры, вдохновлённые кочевой традицией.',
'Premium-Filzhausschuhe mit handaufgetragenen Mustern. Natürliche Schafwolle, Ledersohle, nahtlose Technologie. Einzigartige Muster inspiriert von Nomadentraditionen.',
'Chaussons en feutre premium avec des motifs appliqués à la main. Laine de mouton naturelle, semelle en cuir, technologie sans coutures. Motifs uniques inspirés de la tradition nomade.',
360.00,
'["41"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-premium-wzory-01.jpg',
'["kapcie-premium-wzory-02.jpg","kapcie-premium-wzory-03.jpg","kapcie-premium-wzory-04.jpg","kapcie-premium-wzory-05.jpg"]',
0, 0);

-- Product 20: Kapcie z haftem (nr3852948) - 360 PLN (sold out)
INSERT INTO `products` (`category_id`, `name_pl`, `name_en`, `name_ru`, `name_de`, `name_fr`, `description_pl`, `description_en`, `description_ru`, `description_de`, `description_fr`, `price`, `sizes`, `colors`, `image`, `gallery`, `stock`, `featured`) VALUES
(1,
'Kapcie filcowe z haftem dekoracyjnym',
'Felted Slippers with Decorative Embroidery',
'Войлочные тапочки с декоративной вышивкой',
'Filzhausschuhe mit dekorativer Stickerei',
'Chaussons en feutre avec broderie décorative',
'Kapcie filcowe z delikatnym haftem dekoracyjnym. Naturalna owcza wełna, skórzana podeszwa. Haft nawiązuje do tradycyjnych kirgiskich motywów. Bezszwowa konstrukcja.',
'Felted slippers with delicate decorative embroidery. Natural sheep wool, leather sole. Embroidery references traditional Kyrgyz motifs. Seamless construction.',
'Войлочные тапочки с деликатной декоративной вышивкой. Натуральная овечья шерсть, кожаная подошва. Вышивка отсылает к традиционным кыргызским мотивам. Бесшовная конструкция.',
'Filzhausschuhe mit feiner dekorativer Stickerei. Natürliche Schafwolle, Ledersohle. Die Stickerei verweist auf traditionelle kirgisische Motive. Nahtlose Konstruktion.',
'Chaussons en feutre avec une broderie décorative délicate. Laine de mouton naturelle, semelle en cuir. La broderie fait référence aux motifs traditionnels kirghiz. Construction sans coutures.',
360.00,
'["39"]',
'[{"name":"Natural","code":"#F5E6D3"}]',
'kapcie-haft-01.jpg',
'["kapcie-haft-02.jpg","kapcie-haft-03.jpg"]',
0, 0);

-- Insert admin user (password: admin123 - change immediately!)
INSERT INTO `admin_users` (`username`, `password_hash`, `email`) VALUES
    ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@feltee.com');

SET FOREIGN_KEY_CHECKS = 1;
