#!/bin/bash
set -e

echo "=== Feltee GCP Server Setup ==="

# 1. Create MySQL database and user
DB_NAME="feltee_db"
DB_USER="feltee_user"
DB_PASS="F3lt33_Str0ng_P@ss_2026!"

echo "Creating database and user..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
echo "Database created: $DB_NAME"
echo "User created: $DB_USER"

# 2. Import database schema
echo "Importing database schema..."
sudo mysql $DB_NAME < /var/www/feltee/database.sql
echo "Schema imported."

# 3. Configure Apache virtual host
echo "Configuring Apache..."
sudo tee /etc/apache2/sites-available/feltee.conf > /dev/null << 'VHOST'
<VirtualHost *:80>
    ServerName alis.mygamesonline.org
    ServerAlias 34.139.223.4
    DocumentRoot /var/www/feltee

    <Directory /var/www/feltee>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/feltee-error.log
    CustomLog ${APACHE_LOG_DIR}/feltee-access.log combined
</VirtualHost>
VHOST

sudo a2ensite feltee.conf
sudo a2dissite 000-default.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
echo "Apache configured."

# 4. Create .env file
echo "Creating .env file..."
sudo tee /var/www/feltee/.env > /dev/null << ENV
SITE_URL=http://34.139.223.4
DB_HOST=localhost
DB_PORT=3306
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
PAYU_POS_ID=300746
PAYU_MD5_KEY=b6ca15b0d1020e8094d9b5f8d163db54
PAYU_CLIENT_ID=300746
PAYU_CLIENT_SECRET=2ee86a66e5d97e3fadc400c9f19b065d
PAYU_BASE_URL=https://secure.snd.payu.com
ENV
echo ".env created."

# 5. Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/feltee
sudo chmod -R 755 /var/www/feltee
sudo chmod 600 /var/www/feltee/.env
echo "Permissions set."

echo ""
echo "=== Setup Complete ==="
echo "Database: $DB_NAME"
echo "DB User: $DB_USER"
echo "Site: http://34.139.223.4"
