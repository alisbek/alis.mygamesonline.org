# Feltee E-Commerce Store — Project Documentation

## Overview

Feltee is a handmade felt products e-commerce store based in Poland, selling slippers, boots, bags, backpacks, and accessories made from 100% natural Kyrgyz sheep wool. The store supports 5 languages and integrates with PayU.pl for online payments.

## Architecture

### Tech Stack
- **Backend**: Plain PHP 8.2 (no framework)
- **Database**: MariaDB 10.11 (MySQL-compatible)
- **Web Server**: Apache 2.4 with mod_rewrite
- **Hosting**: GCP e2-micro VM (Debian 12) — free tier
- **CI/CD**: GitHub Actions → rsync over SSH
- **Payments**: PayU.pl (sandbox → production)

### File Structure
```
/
├── config/
│   ├── config.php          # Constants from .env (DB, SITE_URL, PayU, currency)
│   └── db.php              # PDO connection
├── includes/
│   ├── header.php          # HTML header, nav, language switcher
│   ├── footer.php          # HTML footer
│   ├── functions.php       # Core helpers: url(), __(), formatPrice(), getCart(), csrf
│   └── payu.php            # PayU API: OAuth, create order, verify signature
├── lang/
│   ├── pl.php              # Polish (default)
│   ├── en.php              # English
│   ├── ru.php              # Russian
│   ├── de.php              # German
│   └── fr.php              # French
├── admin/
│   ├── index.php           # Dashboard
│   ├── products.php        # Product management
│   ├── categories.php      # Category management
│   ├── orders.php          # Order management (payment status, PayU order ID)
│   ├── login.php / logout.php
│   └── admin.css
├── assets/
│   ├── css/style.css       # Main stylesheet
│   └── js/main.js          # Frontend JS (cart, mobile menu)
├── uploads/products/       # 98 product images (~18.4 MB)
├── index.php               # Homepage
├── products.php            # Product listing with category filter
├── product.php             # Single product page
├── cart.php                # Cart page
├── cart-api.php            # Cart AJAX API
├── checkout.php            # Checkout form + PayU order creation
├── order-success.php       # Post-payment/order confirmation page
├── payu-notify.php         # PayU webhook endpoint
├── about.php / contact.php # Static pages
├── database.sql            # Full schema with seed data (20 products, 5 categories)
├── .htaccess               # Rewrite rules, security headers, caching
├── .env                    # Credentials (gitignored)
├── .env.example            # Template
└── .github/workflows/deploy.yml  # CI/CD pipeline
```

### Database Schema (6 tables)
- `categories` — 5 categories with multilingual names
- `products` — 20 products with multilingual names/descriptions, prices in PLN, JSON sizes/colors
- `orders` — Customer orders with payment_method, payment_status, payu_order_id
- `order_items` — Line items for each order
- `admin_users` — Admin authentication
- `settings` — Site settings (key-value)

### URL Routing
- `.htaccess` rewrites `/en/products.php` → `products.php?lang=en`
- Default language (Polish) has no prefix: `/products.php`
- Other languages: `/en/`, `/ru/`, `/de/`, `/fr/`
- `getCurrentLang()` reads `?lang=` param, stores in session
- `url()` function generates language-prefixed URLs

### Multilingual System
- 5 languages: Polish (default), English, Russian, German, French
- Translation files in `lang/*.php` return associative arrays
- `__('key.subkey')` looks up translations with dot notation
- Admin panel is English-only

### Payment Flow (PayU)
1. Customer fills checkout form, selects "PayU" payment
2. Server creates order in DB (status: `pending`)
3. Server calls PayU API to create payment order
4. Customer redirected to PayU hosted payment page
5. Customer completes payment on PayU
6. PayU sends webhook notification to `/payu-notify.php`
7. Webhook verifies signature, updates order `payment_status`
8. Customer redirected back to `order-success.php`

### Important Constants
- `FELTEE_CURRENCY` and `FELTEE_CURRENCY_SYMBOL` — prefixed with `FELTEE_` to avoid collision with PHP intl extension's `CURRENCY_SYMBOL` constant (integer 262145)

## Infrastructure

### GCP VM
- **Project**: `feltee-store`
- **VM**: `feltee-web` (e2-micro, us-east1-b)
- **IP**: `34.139.223.4` (ephemeral — should reserve static IP)
- **OS**: Debian 12
- **Disk**: 30GB pd-standard

### CI/CD Pipeline
- Push to `master` → GitHub Actions → rsync over SSH to `/var/www/feltee/`
- `.env` generated from GitHub Secrets at deploy time
- Product images excluded from deploy (already on server)
- Files owned by `www-data:www-data` after deploy

### GitHub Secrets
| Secret | Description |
|--------|-------------|
| `GCP_HOST` | VM external IP |
| `GCP_USER` | SSH username |
| `GCP_SSH_PRIVATE_KEY` | Ed25519 deploy key |
| `DB_HOST` | Database host (localhost) |
| `DB_PORT` | Database port (3306) |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASS` | Database password |
| `SITE_URL` | Public site URL |
| `PAYU_POS_ID` | PayU merchant POS ID |
| `PAYU_MD5_KEY` | PayU second key (MD5) |
| `PAYU_CLIENT_ID` | PayU OAuth client ID |
| `PAYU_CLIENT_SECRET` | PayU OAuth client secret |
| `PAYU_BASE_URL` | PayU API base URL |
