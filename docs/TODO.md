# Feltee Project — Remaining Tasks & Roadmap

## High Priority (Do Next)

### 1. Reserve Static IP on GCP
The current IP `34.139.223.4` is ephemeral — it changes when the VM restarts.
```bash
gcloud compute addresses create feltee-ip --region=us-east1 --project=feltee-store
gcloud compute instances delete-access-config feltee-web --zone=us-east1-b --access-config-name="External NAT"
gcloud compute instances add-access-config feltee-web --zone=us-east1-b --address=<STATIC_IP>
```
After reserving, update `SITE_URL` GitHub Secret and `.env` on server.

### 2. Register Domain
Options:
- `feltee.pl` — ideal for Polish market (~30 PLN/year)
- `feltee.eu` — good for European market
- Keep `alis.mygamesonline.org` as secondary/redirect

### 3. Configure DNS
Point domain A record to GCP static IP. Update:
- `SITE_URL` in GitHub Secrets and server `.env`
- Apache virtual host `ServerName`
- PayU `notifyUrl` and `continueUrl` will auto-update (they use `SITE_URL`)

### 4. Set Up SSL (Let's Encrypt)
```bash
sudo certbot --apache -d feltee.pl -d www.feltee.pl
```
Then re-enable HTTPS redirect in `.htaccess`.

### 5. Full End-to-End PayU Test
1. Add product to cart on the live site
2. Go through checkout, select PayU
3. Complete payment with test card: `4444333322221111`, exp `12/29`, CVV `123`
4. Verify redirect back to order-success page
5. Verify webhook updates order payment_status to "paid"
6. Check admin panel shows correct payment status

### 6. Switch PayU to Production
When ready for real payments:
1. Update GitHub Secrets:
   - `PAYU_POS_ID` → `4423831`
   - `PAYU_MD5_KEY` → `00672f15a8829692826593ae9b8dad24`
   - `PAYU_CLIENT_ID` → `4423831`
   - `PAYU_CLIENT_SECRET` → `f65b9f144b6d7fe5751ea394e555fc05`
   - `PAYU_BASE_URL` → `https://secure.payu.com`
2. Push any commit to trigger deploy
3. Test with a small real payment

## Medium Priority

### 7. Email Configuration
Currently using PHP `mail()` which may not work on GCP (no MTA configured).
Options:
- Install `postfix` as local MTA
- Use an SMTP service (SendGrid, Mailgun, Gmail SMTP)
- Skip for now (emails are nice-to-have, not critical)

### 8. Admin Security
- Change default admin password
- Consider IP whitelisting for admin panel
- Add rate limiting for login attempts

### 9. SEO & Analytics
- Update `robots.txt` and `sitemap.xml` with new domain
- Add Google Analytics or Plausible
- Submit to Google Search Console

### 10. Performance
- Enable PHP OPcache (check if enabled by default on Debian 12)
- Consider adding Redis/APCu for session storage
- Image optimization (WebP conversion)

## Low Priority / Future

### 11. Laravel Migration
Plan to migrate from plain PHP to Laravel for:
- Proper MVC architecture
- Eloquent ORM
- Blade templating
- Built-in CSRF, auth, validation
- Package ecosystem (payment SDKs, etc.)

### 12. Additional Features
- Order tracking page for customers
- Email notifications (order confirmation, shipping)
- Inventory management (stock tracking)
- Discount codes / promotions
- Product reviews
- Wishlist functionality

### 13. Monitoring & Backups
- Set up automated DB backups (cron + mysqldump to Cloud Storage)
- Set up uptime monitoring (UptimeRobot, free tier)
- Configure log rotation
- Set up error alerting

### 14. Legal/Compliance
- Privacy policy page (RODO/GDPR)
- Terms and conditions
- Cookie consent banner
- Return policy
