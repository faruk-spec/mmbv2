# MMB Platform Installation Guide

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer (for testing and development)
- Tesseract OCR with language data
- ImageMagick
- Poppler utils (for PDF processing)

## Step 1: Install System Dependencies

### Ubuntu/Debian:
```bash
# PHP and extensions
sudo apt-get update
sudo apt-get install php8.1 php8.1-cli php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-gd php8.1-zip

# Tesseract OCR
sudo apt-get install tesseract-ocr tesseract-ocr-eng
# Additional languages (optional)
sudo apt-get install tesseract-ocr-spa tesseract-ocr-fra tesseract-ocr-deu

# ImageMagick for image preprocessing
sudo apt-get install imagemagick

# Poppler utils for PDF processing
sudo apt-get install poppler-utils

# Composer (if not installed)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### CentOS/RHEL:
```bash
# PHP and extensions
sudo yum install php php-cli php-mysqlnd php-mbstring php-xml php-curl php-gd php-zip

# Tesseract OCR
sudo yum install epel-release
sudo yum install tesseract tesseract-langpack-eng

# ImageMagick
sudo yum install ImageMagick ImageMagick-devel

# Poppler utils
sudo yum install poppler-utils

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Step 2: Clone Repository

```bash
cd /var/www
git clone https://github.com/faruk-spec/mmb.git
cd mmb
```

## Step 3: Install PHP Dependencies

```bash
# Install Composer dependencies (for testing and development)
composer install

# Or for production (without dev dependencies)
composer install --no-dev --optimize-autoloader
```

## Step 4: Set Permissions

```bash
# Make storage directories writable
chmod -R 775 storage/
chmod -R 775 public/uploads/
chmod -R 775 storage/cache/
chmod -R 775 storage/logs/

# Set ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data public/uploads/
```

## Step 5: Database Setup

### 5.1 Create Main Database

```bash
mysql -u root -p
```

```sql
-- Create main database (example name: testuser)
CREATE DATABASE testuser CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant privileges
CREATE USER 'testuser'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON testuser.* TO 'testuser'@'localhost';
FLUSH PRIVILEGES;
```

### 5.2 Import Main Schema

```bash
mysql -u testuser -p testuser < install/schema.sql
```

### 5.3 Apply Performance Optimizations

```bash
mysql -u testuser -p testuser < install/migrations/phase8_database_optimization.sql
```

### 5.4 Create Project Databases

For each project (CodeXPro, ImgTxt, ProShare), create separate databases:

```sql
-- CodeXPro
CREATE DATABASE codexpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'codexpro'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON codexpro.* TO 'codexpro'@'localhost';

-- ImgTxt
CREATE DATABASE imgtxt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'imgtxt'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON imgtxt.* TO 'imgtxt'@'localhost';

-- ProShare
CREATE DATABASE proshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'proshare'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON proshare.* TO 'proshare'@'localhost';

FLUSH PRIVILEGES;
```

Import project schemas (these will be created during admin panel configuration).

## Step 6: Configuration

### 6.1 Main Database Configuration

Create `/config/database.php`:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'testuser',  // Your main database name
    'username' => 'testuser',   // Your database username
    'password' => 'secure_password', // Your database password
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

### 6.2 Mail Configuration

Create `/config/mail.php`:

```php
<?php
return [
    'driver' => 'smtp',
    'host' => 'smtp.mailtrap.io',  // Your SMTP host
    'port' => 2525,
    'username' => 'your_username',
    'password' => 'your_password',
    'encryption' => 'tls',
    'from' => [
        'address' => 'noreply@yourdomain.com',
        'name' => 'MMB Platform'
    ],
    'queue' => [
        'enabled' => true,
        'batch_size' => 50,
        'retry_attempts' => 3
    ]
];
```

## Step 7: Web Server Configuration

### Apache (.htaccess already included)

Make sure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx

Use the provided configuration:
```bash
sudo cp nginx.conf.example /etc/nginx/sites-available/mmb
sudo ln -s /etc/nginx/sites-available/mmb /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Step 8: WebSocket Server (Optional but Recommended)

### 8.1 Start WebSocket Server

```bash
# Test run
php websocket-server.php 0.0.0.0 8080

# Or run in background
nohup php websocket-server.php 0.0.0.0 8080 > /dev/null 2>&1 &
```

### 8.2 Setup as System Service (Recommended)

Create `/etc/systemd/system/mmb-websocket.service`:

```ini
[Unit]
Description=MMB WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/mmb
ExecStart=/usr/bin/php /var/www/mmb/websocket-server.php 0.0.0.0 8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable mmb-websocket
sudo systemctl start mmb-websocket
sudo systemctl status mmb-websocket
```

## Step 9: Cron Jobs

Add to crontab (`crontab -e`):

```bash
# Process email queue every 5 minutes
*/5 * * * * cd /var/www/mmb && php -r "require 'core/Email.php'; Email::processQueue(50);"

# Clean up expired cache every hour
0 * * * * cd /var/www/mmb && php -r "require 'core/Cache.php'; Cache::cleanup();"

# Process OCR batch jobs
*/2 * * * * cd /var/www/mmb && php cron/process-ocr-batches.php

# Clean up old logs (daily at 3 AM)
0 3 * * * find /var/www/mmb/storage/logs -name "*.log" -mtime +30 -delete
```

## Step 10: Testing Installation

### 10.1 Run Unit Tests

```bash
composer test

# Or with coverage
composer test:coverage
```

### 10.2 Check Static Analysis

```bash
composer analyze
```

### 10.3 Check Code Style

```bash
composer lint
```

## Step 11: Access the Platform

1. **Main Admin Panel**: `https://yourdomain.com/admin`
2. **CodeXPro**: `https://yourdomain.com/codexpro`
3. **ImgTxt**: `https://yourdomain.com/imgtxt`
4. **ProShare**: `https://yourdomain.com/proshare`

Default admin credentials (change immediately):
- Username: `admin`
- Password: `admin123`

## Step 12: Post-Installation Configuration

### 12.1 Configure Projects in Admin Panel

1. Navigate to Admin Panel → Projects → Database Setup
2. Configure each project's database connection
3. Create project-specific tables from the admin interface

### 12.2 Generate API Keys

1. Go to Admin Panel → Settings → API Settings
2. Generate API keys for external integrations
3. Configure rate limits

### 12.3 Configure Email Templates

1. Admin Panel → Settings → Email Templates
2. Customize email templates for your brand

### 12.4 Set Up WebSocket Integration

1. Admin Panel → Settings → Real-time Features
2. Configure WebSocket server URL
3. Test real-time features

## Troubleshooting

### Common Issues

**1. Permission Denied**
```bash
sudo chmod -R 775 storage/ public/uploads/
sudo chown -R www-data:www-data storage/ public/uploads/
```

**2. Database Connection Failed**
- Check `/config/database.php` credentials
- Ensure MySQL service is running: `sudo systemctl status mysql`
- Test connection: `mysql -u testuser -p -h localhost`

**3. Composer Command Not Found**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**4. Tesseract Not Found**
```bash
which tesseract  # Should show path
tesseract --list-langs  # Should show available languages
```

**5. WebSocket Connection Failed**
- Check if port 8080 is open: `sudo ufw allow 8080`
- Verify WebSocket server is running: `ps aux | grep websocket-server`
- Check firewall settings

**6. Email Not Sending**
- Verify SMTP credentials in `/config/mail.php`
- Check email queue: View in Admin Panel → Logs → Email Queue
- Process queue manually: `php -r "require 'core/Email.php'; Email::processQueue(10);"`

**7. OCR Not Working**
- Verify Tesseract installation: `tesseract --version`
- Check language data: `tesseract --list-langs`
- Install missing languages: `sudo apt-get install tesseract-ocr-eng`
- Check ImageMagick: `convert --version`
- Check Poppler: `pdftoppm -v`

## Security Recommendations

1. **Change Default Credentials**: Update admin password immediately
2. **Use HTTPS**: Configure SSL/TLS certificate
3. **Firewall**: Configure firewall rules
4. **Database Security**: Use strong passwords, limit remote access
5. **File Permissions**: Ensure proper ownership and permissions
6. **Regular Updates**: Keep system packages and dependencies updated
7. **Backup**: Set up regular database and file backups

## Production Optimization

1. **Enable OPcache**: Configure PHP OPcache
2. **Use CDN**: Configure CDN for static assets
3. **Redis**: Use Redis for caching instead of file-based cache
4. **Database**: Optimize queries and add indexes
5. **Load Balancer**: Use load balancer for high traffic
6. **Monitor**: Set up monitoring (Prometheus, Grafana, etc.)

## Support

For issues and questions:
- GitHub Issues: https://github.com/faruk-spec/mmb/issues
- Documentation: Check all PHASE_*_GUIDE.md files
- Admin Panel: Built-in help and diagnostics

## Version Information

- Platform Version: 1.0.0
- PHP Minimum: 8.0
- MySQL Minimum: 5.7
- Implemented Phases: 1-12 (8 major phases completed)
