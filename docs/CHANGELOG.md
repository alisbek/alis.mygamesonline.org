# Feltee Project — Completed Work Log

## Phase 1: Initial Setup & Core Site
- Created GitHub repository and CI/CD pipeline (SFTP to RunHosting)
- Built PHP e-commerce site from scratch (no framework)
- Created database schema: categories, products, orders, order_items, admin_users, settings
- Built admin panel: product management, category management, order management
- Added 20 real products across 5 categories with descriptions and pricing
- Downloaded 98 product images from Pakamera CDN

## Phase 2: Frontend & Multilingual
- Designed and built responsive frontend (CSS, mobile-friendly)
- Implemented 5-language support (PL, EN, RU, DE, FR)
- Created translation framework: `__()`, `url()`, `getCurrentLang()`, `loadLang()`
- Replaced all hardcoded text with translation keys
- Added hreflang tags, language switcher, canonical URLs
- Built cart system with AJAX API and session storage

## Phase 3: PayU Payment Integration
- Investigated RunHosting limitations — discovered outbound network blocking (fatal for payment APIs)
- Tested PayU sandbox OAuth locally — confirmed credentials work
- Built PayU helper library (`includes/payu.php`):
  - OAuth token acquisition with file-based caching
  - Order creation with correct redirect handling (302 response)
  - Webhook signature verification (MD5/SHA256)
  - Status mapping (PayU → internal)
- Built checkout flow with PayU, cash, and bank transfer options
- Built webhook endpoint (`payu-notify.php`) with idempotent status updates
- Built order success page with payment status display
- Updated admin panel with payment status column and manual update form
- Updated all 5 language files with PayU-related translation keys
- Updated .htaccess to skip language rewriting for webhook endpoint
- Added PayU credentials to GitHub Secrets

## Phase 4: GCP Migration
- Evaluated hosting options — chose GCP e2-micro (free tier, full SSH/outbound access)
- Installed gcloud CLI on local machine, authenticated
- Created GCP project `feltee-store` with billing account
- Created VM `feltee-web` (e2-micro, Debian 12, 30GB disk)
- Configured firewall rules for HTTP/HTTPS
- Installed LAMP stack via startup script (Apache, PHP 8.2, MariaDB, certbot)
- Uploaded site files to VM via `gcloud compute scp`
- Created and ran server setup script (`gcp-server-setup.sh`):
  - Created MySQL database and user
  - Imported schema with 20 products and 5 categories
  - Configured Apache virtual host
  - Created .env with sandbox PayU credentials
  - Set file permissions
- Cleaned up .git/ directory from server
- Temporarily disabled HTTPS redirect (no SSL cert yet)
- Verified all site pages work (homepage, products, cart, admin, language routing)
- Verified PayU outbound connectivity from GCP (was blocked on RunHosting)
- Tested PayU OAuth + order creation from GCP — fully working
- Verified webhook endpoint is reachable from internet
- Generated SSH deploy key pair (Ed25519)
- Added deploy public key to VM authorized_keys
- Rewrote CI/CD pipeline for GCP (rsync over SSH instead of SFTP)
- Updated all 14 GitHub Secrets for GCP deployment
- Removed old RunHosting FTP secrets

## Key Decisions Made
| Decision | Choice | Reason |
|----------|--------|--------|
| Hosting | GCP e2-micro | Free tier, full SSH, no outbound blocking |
| PayU mode | Sandbox first | Test before production |
| Payment flow | Redirect | Simplest, no PCI scope |
| Offline payments | Keep alongside PayU | Cash on delivery, bank transfer |
| Domain | Bare IP for now | DNS change deferred |
| Framework | Stay plain PHP | Ship now, plan Laravel migration later |
| Currency constants | FELTEE_ prefix | Avoid PHP intl extension collision |

## Metrics
- **PHP files**: 22+
- **Lines of code**: ~2,300+
- **Products**: 20 across 5 categories
- **Product images**: 98 (~18.4 MB)
- **Languages**: 5 (PL, EN, RU, DE, FR)
- **DB tables**: 6
