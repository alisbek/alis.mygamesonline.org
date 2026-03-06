# Feltee Handcraft Studio - E-commerce Website

A multilingual e-commerce website for selling handcrafted felted slippers, boots, and accessories.

## Features

- **Multilingual**: Russian, English, Polish
- **Products**: Slippers, boots, bags, backpacks, accessories
- **Shopping cart**: Client-side (localStorage)
- **Order management**: Admin panel
- **SEO optimized**: Meta tags, sitemap, hreflang
- **Responsive design**: Mobile-friendly

## Tech Stack

- PHP 7.4+ (or PHP 8.2 recommended)
- MySQL 8.0
- Vanilla JavaScript
- CSS3 with CSS Variables

## Installation

### 1. Upload Files

Upload all files to your web hosting directory (e.g., `/www/alis.mygamesonline.org/`)

### 2. Create Database

1. Log in to your hosting control panel
2. Create a new MySQL database (if not already created)
3. Import `database.sql` to create tables and add sample data

### 3. Configure Database Connection

Edit `config/config.php` and update the database credentials:

```php
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');  // Add your password
```

### 4. Set Folder Permissions

Make sure `uploads/products/` folder is writable:
- Right-click folder → Permissions → 755 or 777

### 5. Add Product Images

Download your product images from Pakamera and upload them to `uploads/products/` folder.

Image filenames in database.sql are placeholders. Update them in the admin panel after uploading.

### 6. Default Admin Login

- **URL**: `https://alis.mygamesonline.org/admin/login.php`
- **Username**: `asel`
- **Password**: `password`

⚠️ **Change the password immediately after first login in Admin -> Settings.**

## File Structure

```
/
├── index.php              # Homepage
├── products.php           # Product catalog
├── product.php            # Product detail page
├── cart.php               # Shopping cart
├── checkout.php           # Checkout page
├── order-success.php      # Order confirmation
├── about.php              # About page
├── contact.php            # Contact page
├── .htaccess              # Apache config
├── robots.txt             # SEO
├── sitemap.xml            # SEO
├── database.sql           # Database schema & data
│
├── config/
│   ├── config.php         # Site configuration
│   └── db.php             # Database connection
│
├── includes/
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   └── functions.php      # Helper functions
│
├── lang/
│   ├── ru.php             # Russian translations
│   ├── en.php             # English translations
│   └── pl.php             # Polish translations
│
├── admin/
│   ├── index.php          # Dashboard
│   ├── login.php          # Admin login
│   ├── logout.php         # Logout
│   ├── products.php       # Manage products
│   ├── orders.php         # Manage orders
│   ├── categories.php     # Manage categories
│   └── admin.css          # Admin styles
│
├── assets/
│   ├── css/style.css      # Main styles
│   └── js/main.js         # JavaScript
│
└── uploads/
    └── products/          # Product images
```

## Product Images

Your Pakamera product images to download:

1. **Slippers Natural** - `kapcie-natural-1.jpg`
2. **Slippers Grey** - `kapcie-grey-1.jpg`
3. **Slippers Decorated** - `kapcie-decorated-1.jpg`
4. **Slippers Premium** - `kapcie-premium-1.jpg`
5. **Boots** - `botki-1.jpg`
6. **Crossbody Bag** - `torba-crossbody-1.jpg`
7. **Backpack** - `plecak-1.jpg`
8. **Laptop Organizer** - `organizer-1.jpg`

## Currency

All prices are in **PLN (Polish Złoty)**.

## Payment Methods

- Cash on delivery
- Bank transfer

## Delivery Methods

- Pickup (free)
- Courier
- Postal service

## Customization

### Change Site Name
Edit `config/config.php`:
```php
define('SITE_NAME', 'Your Brand Name');
```

### Change Currency
Edit `config/config.php`:
```php
define('CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');
```

### Add Languages
1. Create `lang/XX.php` (copy from existing)
2. Add to `config/config.php`:
```php
define('LANGUAGES', ['ru' => 'Русский', 'en' => 'English', 'pl' => 'Polski', 'XX' => 'Language Name']);
```

## Support

For issues or questions, contact the developer.

---

© 2025 Feltee Handcraft Studio. All rights reserved.