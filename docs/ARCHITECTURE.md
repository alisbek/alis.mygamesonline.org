# Feltee E-Commerce Store — Project Documentation

...(existing content)...

### InPost Paczkomat Locker Delivery Integration
- **Frontend**: Locker delivery option in checkout, geowidget for locker selection, language support for PL, EN, RU, DE, FR
- **Backend**: Locker delivery tracking fields in orders (locker_id, locker_name, shipping_type), product dimension fields in products (weight_grams, length_mm, width_mm, height_mm)
- **Admin**: Locker details visible in order management, bulk edit for product dimensions, shipment management actions (label, tracking, status)
- **Integration**: Secure environment key handling via .env and GitHub Secrets (INPOST_API_KEY, INPOST_CUSTOMER_ID, INPOST_SECRET)
- **Code**: No Composer, plain PHP only, robust error logging via error_log, all credentials handled as per rule

### Updated GitHub Secrets
| Secret                | Description                               |
|-----------------------|-------------------------------------------|
| INPOST_API_KEY        | InPost API access key                     |
| INPOST_CUSTOMER_ID    | Your InPost customer identifier           |
| INPOST_SECRET         | InPost client secret (for webhook, auth)  |

### Database Schema (Supplement)
- Added: `weight_grams`, `length_mm`, `width_mm`, `height_mm` to `products` table (default: 300, 300, 200, 80 for 20 products)
- Added: `locker_id`, `locker_name`, `shipping_type`, `tracking_number` to `orders`

### Locker Delivery Flow
1. Customer selects locker delivery in checkout
2. Geowidget opens for locker selection (multi-language)
3. Locker details stored in order, displayed to admin
4. Admin manages shipment, prints label, tracks status

