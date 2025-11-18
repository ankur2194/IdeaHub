# Docker Setup Guide for IdeaHub

This guide provides comprehensive instructions for running IdeaHub using Docker. We provide two Docker configurations:
1. **Production setup** (`docker-compose.yml`) - Full production stack with MySQL, Redis, and optimized builds
2. **Development setup** (`docker-compose.dev.yml`) - Fast development with hot reloading and SQLite

## Prerequisites

- Docker Engine 20.10+ ([Install Docker](https://docs.docker.com/engine/install/))
- Docker Compose 2.0+ ([Install Docker Compose](https://docs.docker.com/compose/install/))
- Git

## Quick Start (Production)

Get IdeaHub running in production mode with MySQL and Redis:

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/IdeaHub.git
cd IdeaHub

# 2. Create environment file
cp .env.docker.example .env

# 3. (Optional) Edit .env to customize settings
nano .env

# 4. Build and start all services
docker-compose up -d --build

# 5. Wait for initialization (migrations will run automatically)
# Check logs to see progress:
docker-compose logs -f app

# 6. (Optional) Seed the database with sample data
docker-compose exec app php artisan db:seed

# 7. Access the application
# Frontend + Backend: http://localhost:8000
# WebSocket Server: http://localhost:6001
```

That's it! IdeaHub is now running with:
- ✅ Laravel backend with optimized production build
- ✅ React frontend (pre-built and served)
- ✅ MySQL 8.0 database
- ✅ Redis cache and queue
- ✅ Soketi WebSocket server
- ✅ Automatic database migrations
- ✅ Queue workers

## Quick Start (Development)

For local development with hot reloading:

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/IdeaHub.git
cd IdeaHub

# 2. Start development services
docker-compose -f docker-compose.dev.yml up -d --build

# 3. Wait for services to start
docker-compose -f docker-compose.dev.yml logs -f

# 4. Access the application
# Backend API: http://localhost:8000
# Frontend: http://localhost:5173 (with hot reload)
# WebSocket: http://localhost:6001
# MailHog UI: http://localhost:8025
```

Development setup includes:
- ✅ Laravel with SQLite (no MySQL needed)
- ✅ Vite dev server with hot module reload
- ✅ MailHog for email testing
- ✅ Soketi for WebSocket testing
- ✅ Automatic database seeding
- ✅ Code mounted as volumes (instant updates)

## Architecture Overview

### Production Setup (`docker-compose.yml`)

```
┌─────────────────────────────────────────────────┐
│  IdeaHub Application (Port 8000)                │
│  - Nginx + PHP-FPM                              │
│  - Pre-built React frontend                     │
│  - Laravel backend API                          │
└───────────────┬─────────────────────────────────┘
                │
     ┌──────────┴───────────┬──────────────┐
     │                      │              │
┌────▼─────┐       ┌───────▼────┐    ┌───▼─────┐
│  MySQL   │       │   Redis    │    │ Soketi  │
│  (DB)    │       │ (Cache/Q)  │    │  (WS)   │
└──────────┘       └────────────┘    └─────────┘
```

**Services:**
- **app** - Main application container (Laravel + React)
- **mysql** - MySQL 8.0 database
- **redis** - Redis 7 for cache, sessions, and queues
- **queue** - Dedicated queue worker
- **soketi** - WebSocket server for real-time features

### Development Setup (`docker-compose.dev.yml`)

```
┌──────────────────┐       ┌──────────────────┐
│  Backend (8000)  │       │  Frontend (5173) │
│  - Laravel CLI   │       │  - Vite HMR      │
│  - SQLite DB     │       │  - Hot Reload    │
└────────┬─────────┘       └──────────────────┘
         │
    ┌────┴────┬────────────┬──────────┐
    │         │            │          │
┌───▼────┐ ┌─▼──────┐ ┌───▼─────┐ ┌─▼───────┐
│ Queue  │ │ Soketi │ │MailHog  │ │         │
│Worker  │ │  (WS)  │ │(Email)  │ │         │
└────────┘ └────────┘ └─────────┘ └─────────┘
```

## Detailed Setup Instructions

### Environment Configuration

The `.env` file controls Docker behavior. Key variables:

```bash
# Application
APP_NAME=IdeaHub
APP_ENV=production          # or "local" for dev
APP_DEBUG=false             # true for dev
APP_URL=http://localhost:8000
APP_PORT=8000               # External port mapping

# Database
DB_CONNECTION=mysql         # or "sqlite" for dev
DB_DATABASE=ideahub
DB_USERNAME=ideahub
DB_PASSWORD=secret          # CHANGE THIS IN PRODUCTION!

# Cache & Queue
CACHE_STORE=redis           # or "database" for dev
SESSION_DRIVER=redis        # or "database" for dev
QUEUE_CONNECTION=redis      # or "database" for dev

# Seeding
SEED_DATABASE=false         # Set to "true" on first run
```

### First Time Setup

#### Production

```bash
# 1. Clone and enter directory
git clone <repository-url>
cd IdeaHub

# 2. Create environment file
cp .env.docker.example .env

# 3. Edit environment (IMPORTANT: Change passwords!)
nano .env

# 4. Set seed flag for initial data
echo "SEED_DATABASE=true" >> .env

# 5. Build and start
docker-compose up -d --build

# 6. Wait for initialization (check logs)
docker-compose logs -f app

# 7. Once you see "initialization complete", access:
# http://localhost:8000
```

#### Development

```bash
# 1. Clone and enter directory
git clone <repository-url>
cd IdeaHub

# 2. Start development environment
docker-compose -f docker-compose.dev.yml up -d --build

# 3. Backend will be at http://localhost:8000
# 4. Frontend will be at http://localhost:5173
# 5. MailHog UI at http://localhost:8025
```

## Docker Commands Reference

### Starting Services

```bash
# Production
docker-compose up -d              # Start in background
docker-compose up                 # Start with logs

# Development
docker-compose -f docker-compose.dev.yml up -d
```

### Stopping Services

```bash
# Production
docker-compose down               # Stop containers
docker-compose down -v            # Stop and remove volumes (⚠️ deletes data)

# Development
docker-compose -f docker-compose.dev.yml down
```

### Viewing Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f mysql
docker-compose logs -f queue

# Development
docker-compose -f docker-compose.dev.yml logs -f frontend
```

### Executing Commands

```bash
# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan queue:work

# Run composer
docker-compose exec app composer install
docker-compose exec app composer update

# Access shell
docker-compose exec app sh
docker-compose exec mysql mysql -uroot -p
```

### Development Commands

```bash
# Install frontend dependencies
docker-compose -f docker-compose.dev.yml exec frontend npm install

# Run tests
docker-compose -f docker-compose.dev.yml exec backend php artisan test

# Access backend shell
docker-compose -f docker-compose.dev.yml exec backend sh
```

## Database Management

### Running Migrations

```bash
# Automatic (on container start)
# Migrations run automatically via entrypoint script

# Manual
docker-compose exec app php artisan migrate

# Fresh migration (⚠️ deletes all data)
docker-compose exec app php artisan migrate:fresh --seed
```

### Seeding Database

```bash
# Method 1: Set SEED_DATABASE=true in .env, then restart
docker-compose restart app

# Method 2: Manual seeding
docker-compose exec app php artisan db:seed

# Specific seeder
docker-compose exec app php artisan db:seed --class=BadgeSeeder
```

### Database Backup

```bash
# MySQL backup
docker-compose exec mysql mysqldump -uideahub -psecret ideahub > backup.sql

# Restore
docker-compose exec -T mysql mysql -uideahub -psecret ideahub < backup.sql
```

## Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs app

# Check container status
docker-compose ps

# Restart specific service
docker-compose restart app
```

### Database connection errors

```bash
# Check MySQL is healthy
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql

# Verify credentials in .env match docker-compose.yml
cat .env | grep DB_
```

### Permission errors

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### App key not set

```bash
# Generate new key
docker-compose exec app php artisan key:generate

# Restart app
docker-compose restart app
```

### Clear all caches

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Reset everything (⚠️ DELETES ALL DATA)

```bash
# Stop and remove everything
docker-compose down -v

# Remove built images
docker-compose down --rmi all -v

# Start fresh
docker-compose up -d --build
```

## Performance Optimization

### Production Optimizations

These are automatically applied by the entrypoint script:

```bash
# Already done by entrypoint, but can be run manually:
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Scaling Queue Workers

```bash
# Scale queue workers to 4 instances
docker-compose up -d --scale queue=4
```

## Security Best Practices

### For Production Deployment

1. **Change default passwords:**
   ```bash
   # In .env, change:
   DB_PASSWORD=<strong-password>
   DB_ROOT_PASSWORD=<strong-root-password>
   ```

2. **Use environment-specific .env:**
   - Never commit `.env` to version control
   - Use secrets management (Docker Swarm secrets, Kubernetes secrets, etc.)

3. **Enable HTTPS:**
   - Use a reverse proxy (Nginx, Traefik, Caddy)
   - Configure SSL certificates

4. **Limit exposed ports:**
   ```yaml
   # Remove public MySQL port in production
   # ports:
   #   - "3306:3306"  # Comment this out
   ```

5. **Use Docker secrets:**
   ```bash
   echo "my_secret_password" | docker secret create db_password -
   ```

## Advanced Configuration

### Custom Domain

```bash
# 1. Update .env
APP_URL=https://ideahub.example.com

# 2. Add reverse proxy (Nginx example)
# See: nginx-proxy or traefik for automated SSL
```

### Email Configuration

```bash
# For production, configure SMTP in .env:
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### Custom Ports

```bash
# Change in .env:
APP_PORT=9000
MYSQL_PORT=3307
REDIS_PORT=6380

# Then restart:
docker-compose down && docker-compose up -d
```

## Monitoring

### Health Checks

```bash
# Check container health
docker-compose ps

# Application health endpoint
curl http://localhost:8000/api/health
```

### Resource Usage

```bash
# View resource usage
docker stats

# Specific containers
docker stats ideahub_app ideahub_mysql
```

## Updating IdeaHub

```bash
# 1. Pull latest changes
git pull origin main

# 2. Rebuild containers
docker-compose up -d --build

# 3. Run new migrations
docker-compose exec app php artisan migrate

# 4. Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
```

## Uninstalling

```bash
# Stop and remove containers
docker-compose down

# Remove volumes (deletes all data)
docker-compose down -v

# Remove images
docker-compose down --rmi all -v

# Remove project directory
cd ..
rm -rf IdeaHub
```

## Support

- **Issues:** [GitHub Issues](https://github.com/yourusername/IdeaHub/issues)
- **Documentation:** [Main README](README.md)
- **Contributing:** [CONTRIBUTING.md](CONTRIBUTING.md)

## FAQ

**Q: Can I use PostgreSQL instead of MySQL?**
A: Yes, modify `docker-compose.yml` to use `postgres:14` image and update `DB_CONNECTION=pgsql` in `.env`.

**Q: How do I access the database directly?**
A: `docker-compose exec mysql mysql -uideahub -psecret ideahub`

**Q: Can I run this in production?**
A: Yes, but ensure you change default passwords, enable HTTPS, and follow security best practices above.

**Q: How do I enable HTTPS?**
A: Use a reverse proxy like Nginx Proxy Manager, Traefik, or Caddy in front of the app container.

**Q: Does this work on Windows/Mac?**
A: Yes! Docker Desktop runs on Windows, Mac, and Linux. Performance may vary.

---

**Last Updated:** 2025-11-18
**Docker Version:** 20.10+
**Compose Version:** 2.0+
