# IdeaHub Deployment Guide

This guide covers deploying IdeaHub to production environments.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Environment Setup](#environment-setup)
- [Deployment Options](#deployment-options)
  - [Traditional Server (VPS)](#traditional-server-vps)
  - [Docker Deployment](#docker-deployment)
  - [Cloud Platforms](#cloud-platforms)
- [Configuration](#configuration)
- [Security Hardening](#security-hardening)
- [Performance Optimization](#performance-optimization)
- [Monitoring](#monitoring)
- [Backup Strategy](#backup-strategy)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Server Requirements

**Minimum Specifications:**
- **CPU:** 2 cores
- **RAM:** 2GB (4GB recommended)
- **Storage:** 20GB SSD
- **OS:** Ubuntu 22.04 LTS / Debian 11+ / CentOS 8+

**Software Requirements:**
- **PHP:** 8.2 or higher
- **Web Server:** Nginx 1.18+ or Apache 2.4+
- **Database:** MySQL 8.0+ / PostgreSQL 14+ / MariaDB 10.6+
- **Node.js:** 18.x or higher
- **Composer:** 2.x
- **Redis:** 6.x+ (recommended for caching and queues)
- **Supervisor:** For queue workers
- **SSL Certificate:** Let's Encrypt or commercial

---

## Environment Setup

### 1. Server Preparation

#### Update System

```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y git curl unzip software-properties-common
```

#### Install PHP 8.2

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-pgsql php8.2-sqlite3 php8.2-zip \
    php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-redis php8.2-intl

# Verify installation
php -v
```

#### Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
composer --version
```

#### Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

#### Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

#### Install MySQL

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE ideahub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ideahub'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON ideahub.* TO 'ideahub'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Install Redis

```bash
sudo apt install -y redis-server
sudo systemctl start redis
sudo systemctl enable redis
redis-cli ping  # Should return PONG
```

#### Install Supervisor

```bash
sudo apt install -y supervisor
sudo systemctl start supervisor
sudo systemctl enable supervisor
```

---

## Deployment Options

### Traditional Server (VPS)

#### 1. Clone Repository

```bash
# Create application directory
sudo mkdir -p /var/www/ideahub
sudo chown -R $USER:$USER /var/www/ideahub

# Clone repository
cd /var/www
git clone https://github.com/yourusername/ideahub.git
cd ideahub
```

#### 2. Install Dependencies

```bash
# Backend dependencies
composer install --no-dev --optimize-autoloader

# Frontend dependencies
cd frontend
npm ci --production
npm run build
cd ..
```

#### 3. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

**Production .env Configuration:**

```env
APP_NAME=IdeaHub
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ideahub
DB_USERNAME=ideahub
DB_PASSWORD=secure_password_here

# Cache & Session (Redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

#### 4. Run Migrations

```bash
php artisan migrate --force

# Optional: Seed database (only for demo)
# php artisan db:seed --force
```

#### 5. Optimize Application

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize Composer autoloader
composer dump-autoload --optimize
```

#### 6. Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/ideahub

# Set directory permissions
sudo find /var/www/ideahub -type d -exec chmod 755 {} \;
sudo find /var/www/ideahub -type f -exec chmod 644 {} \;

# Storage and cache writable
sudo chmod -R 775 /var/www/ideahub/storage
sudo chmod -R 775 /var/www/ideahub/bootstrap/cache
```

#### 7. Configure Nginx

Create Nginx configuration:

```bash
sudo nano /etc/nginx/sites-available/ideahub
```

**Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/ideahub/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript
               application/x-javascript application/xml+rss
               application/javascript application/json;

    # Request limits
    client_max_body_size 20M;

    # Logs
    access_log /var/log/nginx/ideahub_access.log;
    error_log /var/log/nginx/ideahub_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/ideahub /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 8. Install SSL Certificate

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically
# Test renewal
sudo certbot renew --dry-run
```

#### 9. Configure Queue Workers

Create Supervisor configuration:

```bash
sudo nano /etc/supervisor/conf.d/ideahub-worker.conf
```

**Supervisor Configuration:**

```ini
[program:ideahub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ideahub/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ideahub/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ideahub-worker:*
```

#### 10. Configure Scheduled Tasks

Add Laravel scheduler to cron:

```bash
sudo crontab -e -u www-data
```

Add this line:

```cron
* * * * * cd /var/www/ideahub && php artisan schedule:run >> /dev/null 2>&1
```

#### 11. Deploy Frontend

The frontend is built and served from the `frontend/dist` directory. Configure Nginx to serve it:

**Option A: Serve from Same Domain (API Proxy)**

```nginx
# Add to your server block
location /api {
    try_files $uri $uri/ /index.php?$query_string;
}

location / {
    root /var/www/ideahub/frontend/dist;
    try_files $uri $uri/ /index.html;
}
```

**Option B: Serve from Subdomain**

Create separate Nginx config for `app.yourdomain.com`:

```nginx
server {
    listen 443 ssl http2;
    server_name app.yourdomain.com;

    root /var/www/ideahub/frontend/dist;
    index index.html;

    # SSL configuration...

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

---

### Docker Deployment

See `Dockerfile` and `docker-compose.yml` in the root directory.

#### Quick Start with Docker

```bash
# Clone repository
git clone https://github.com/yourusername/ideahub.git
cd ideahub

# Copy environment files
cp .env.example .env
cp frontend/.env.example frontend/.env

# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Build frontend
docker-compose exec frontend npm run build
```

---

### Cloud Platforms

#### AWS (Elastic Beanstalk)

1. Install EB CLI
2. Initialize EB application
3. Configure environment variables
4. Deploy:

```bash
eb init
eb create ideahub-production
eb deploy
```

#### DigitalOcean (App Platform)

1. Connect GitHub repository
2. Configure build settings
3. Set environment variables
4. Deploy from dashboard

#### Heroku

```bash
heroku create ideahub-production
heroku addons:create heroku-postgresql
heroku addons:create heroku-redis
git push heroku main
heroku run php artisan migrate --force
```

---

## Configuration

### Environment Variables Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` set to your domain
- [ ] Database credentials configured
- [ ] Redis configured for cache/session/queue
- [ ] Mail server configured
- [ ] `SANCTUM_STATEFUL_DOMAINS` configured
- [ ] `SESSION_DOMAIN` configured

### Frontend Configuration

Update `frontend/.env`:

```env
VITE_API_URL=https://yourdomain.com
VITE_APP_NAME=IdeaHub
```

Rebuild frontend:

```bash
cd frontend
npm run build
```

---

## Security Hardening

### 1. Firewall Configuration

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### 2. Fail2Ban

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Disable Directory Listing

Already handled in Nginx configuration.

### 4. Secure PHP

Edit `/etc/php/8.2/fpm/php.ini`:

```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### 5. Regular Updates

```bash
# Create update script
cat > /usr/local/bin/update-ideahub.sh << 'EOF'
#!/bin/bash
cd /var/www/ideahub
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart ideahub-worker:*
EOF

sudo chmod +x /usr/local/bin/update-ideahub.sh
```

---

## Performance Optimization

### 1. Enable OPcache

Edit `/etc/php/8.2/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
```

### 2. Configure PHP-FPM

Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### 3. Database Optimization

```sql
-- Add indexes
CREATE INDEX idx_ideas_status ON ideas(status);
CREATE INDEX idx_ideas_category ON ideas(category_id);
CREATE INDEX idx_comments_idea ON comments(idea_id);
```

### 4. Redis Optimization

Edit `/etc/redis/redis.conf`:

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

---

## Monitoring

### 1. Application Monitoring

Consider using:
- **Laravel Telescope** (development/staging)
- **Laravel Horizon** (queue monitoring)
- **Sentry** (error tracking)
- **New Relic** (APM)

### 2. Server Monitoring

```bash
# Install htop
sudo apt install htop

# Monitor processes
htop
```

### 3. Log Monitoring

```bash
# View Laravel logs
tail -f /var/www/ideahub/storage/logs/laravel.log

# View Nginx logs
tail -f /var/log/nginx/ideahub_access.log
tail -f /var/log/nginx/ideahub_error.log
```

---

## Backup Strategy

### 1. Database Backups

Create backup script:

```bash
#!/bin/bash
BACKUP_DIR="/backups/ideahub"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

mysqldump -u ideahub -p'password' ideahub | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
```

Add to cron:

```cron
0 2 * * * /usr/local/bin/backup-ideahub.sh
```

### 2. File Backups

Backup storage directory and uploads:

```bash
tar -czf /backups/ideahub/storage_$DATE.tar.gz /var/www/ideahub/storage
```

### 3. Offsite Backups

Use AWS S3, DigitalOcean Spaces, or similar for offsite storage.

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Check:**
- `storage/logs/laravel.log`
- Nginx error logs
- PHP-FPM logs
- File permissions

**Solution:**
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data /var/www/ideahub
php artisan cache:clear
php artisan config:clear
```

### Issue: Queue Not Processing

**Check:**
```bash
sudo supervisorctl status ideahub-worker:*
```

**Restart:**
```bash
sudo supervisorctl restart ideahub-worker:*
```

### Issue: Frontend Can't Connect to API

**Check:**
- CORS configuration in `config/cors.php`
- `VITE_API_URL` in frontend `.env`
- Network/firewall settings

### Issue: Session/Auth Issues

**Check:**
- `SESSION_DOMAIN` in `.env`
- `SANCTUM_STATEFUL_DOMAINS` in `.env`
- Cookie settings in browser

---

## Rollback Procedure

If deployment fails:

```bash
# Revert code
git reset --hard HEAD~1

# Restore dependencies
composer install --no-dev

# Rollback migrations
php artisan migrate:rollback

# Clear caches
php artisan cache:clear
php artisan config:clear

# Restart services
sudo supervisorctl restart ideahub-worker:*
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
```

---

## Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/12.x/deployment)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Certbot Documentation](https://certbot.eff.org/)
- [Supervisor Documentation](http://supervisord.org/)

---

**Last Updated:** 2025-11-14
**Deployment Guide Version:** 1.0.0
