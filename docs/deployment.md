# Deployment

This document provides instructions for deploying the Choices application.

## Prerequisites

### Server Requirements
- PHP >= 8.2
- MySQL >= 5.7
- Composer
- Node.js >= 16
- NPM >= 7
- Git

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

## Deployment Steps

### 1. Server Setup

```bash
# Update system packages
sudo apt update
sudo apt upgrade

# Install required packages
sudo apt install php8.2 php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip
sudo apt install mysql-server
sudo apt install nginx
sudo apt install git
sudo apt install composer
sudo apt install nodejs npm
```

### 2. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE choices;
CREATE USER 'choices'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON choices.* TO 'choices'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Application Setup

```bash
# Clone repository
git clone https://github.com/your-username/choices.git
cd choices

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies
npm install
npm run build

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure environment variables
nano .env
```

### 4. Environment Configuration

Update the following in `.env`:

```env
APP_NAME=Choices
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=choices
DB_USERNAME=choices
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Application Configuration

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/choices
sudo chmod -R 775 /var/www/choices/storage
sudo chmod -R 775 /var/www/choices/bootstrap/cache

# Run migrations
php artisan migrate --force

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Nginx Configuration

Create `/etc/nginx/sites-available/choices`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/choices/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/choices /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. SSL Configuration

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com
```

### 8. Queue Worker Setup

```bash
# Create systemd service
sudo nano /etc/systemd/system/choices-queue.service
```

Add the following:

```ini
[Unit]
Description=Choices Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/choices
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl enable choices-queue
sudo systemctl start choices-queue
```

### 9. Scheduled Tasks

```bash
# Add cron job
sudo crontab -e
```

Add the following line:

```bash
* * * * * cd /var/www/choices && php artisan schedule:run >> /dev/null 2>&1
```

## Monitoring

### Logs
- Application logs: `storage/logs/laravel.log`
- Nginx logs: `/var/log/nginx/`
- PHP-FPM logs: `/var/log/php8.2-fpm.log`

### Performance Monitoring
- Laravel Telescope (development only)
- New Relic
- Laravel Horizon

## Backup Strategy

### Database Backups
```bash
# Daily backup script
#!/bin/bash
mysqldump -u choices -p'your_password' choices > /backup/choices-$(date +%Y%m%d).sql
find /backup -type f -mtime +7 -delete
```

### File Backups
```bash
# Daily backup script
#!/bin/bash
tar -czf /backup/choices-files-$(date +%Y%m%d).tar.gz /var/www/choices
find /backup -type f -mtime +7 -delete
```

## Security Considerations

### File Permissions
- Web root: 755
- Storage directory: 775
- Bootstrap/cache: 775
- .env: 640

### Firewall Configuration
```bash
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow ssh
sudo ufw enable
```

### Regular Updates
```bash
# Weekly update script
#!/bin/bash
cd /var/www/choices
git pull
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Common Issues
1. Permission errors
   - Check file ownership
   - Verify directory permissions
   - Review SELinux/AppArmor settings

2. Database connection issues
   - Verify credentials
   - Check MySQL service status
   - Review firewall settings

3. Nginx configuration errors
   - Check syntax: `sudo nginx -t`
   - Review error logs
   - Verify PHP-FPM status

### Debug Mode
- Enable debug mode temporarily in `.env`
- Check logs in `storage/logs`
- Use Laravel Telescope for detailed debugging 