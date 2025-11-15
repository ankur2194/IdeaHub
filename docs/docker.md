# Docker Deployment Guide for IdeaHub

This guide explains how to run IdeaHub using Docker and Docker Compose.

## Prerequisites

- **Docker** 20.10+
- **Docker Compose** 2.0+

## Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/ideahub.git
cd ideahub
```

### 2. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit .env file with Docker-specific settings
nano .env
```

**Required environment variables for Docker:**

```env
APP_NAME=IdeaHub
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=ideahub
DB_USERNAME=ideahub
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Build and Start Containers

```bash
# Build images and start containers
docker-compose up -d

# View logs
docker-compose logs -f
```

### 4. Run Migrations

```bash
# Run database migrations
docker-compose exec app php artisan migrate --force

# (Optional) Seed database with demo data
docker-compose exec app php artisan db:seed --force
```

### 5. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Access Application

- **Frontend + API:** http://localhost:8000
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

**Demo Credentials:**
- Email: `admin@ideahub.test`
- Password: `password`

## Architecture

The Docker setup includes the following services:

### Services

| Service | Description | Port |
|---------|-------------|------|
| **app** | Laravel application with Nginx + PHP-FPM | 8000 |
| **mysql** | MySQL 8.0 database | 3306 |
| **redis** | Redis cache and queue | 6379 |
| **queue** | Laravel queue worker | - |

### Containers

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   app       │────▶│   mysql     │     │   redis     │
│ (Nginx+PHP) │     │ (Database)  │◀────│  (Cache)    │
└─────────────┘     └─────────────┘     └─────────────┘
       │
       ▼
┌─────────────┐
│   queue     │
│  (Worker)   │
└─────────────┘
```

## Docker Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart containers
docker-compose restart

# View running containers
docker-compose ps

# View logs
docker-compose logs -f [service_name]

# Example: View app logs
docker-compose logs -f app
```

### Application Commands

```bash
# Run artisan commands
docker-compose exec app php artisan [command]

# Examples:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan queue:work

# Access container shell
docker-compose exec app sh

# Run Composer
docker-compose exec app composer install
docker-compose exec app composer update
```

### Database Commands

```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u ideahub -psecret ideahub

# Backup database
docker-compose exec mysql mysqldump -u ideahub -psecret ideahub > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u ideahub -psecret ideahub < backup.sql

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

### Redis Commands

```bash
# Access Redis CLI
docker-compose exec redis redis-cli

# Clear Redis cache
docker-compose exec redis redis-cli FLUSHALL
```

## Volume Management

### Persistent Data

The following data is persisted in Docker volumes:

- **mysql_data:** Database files
- **redis_data:** Redis data
- **./storage:** Laravel storage (logs, uploads, cache)
- **./bootstrap/cache:** Laravel bootstrap cache

### Backup Volumes

```bash
# List volumes
docker volume ls

# Inspect volume
docker volume inspect ideahub_mysql_data

# Backup volume
docker run --rm -v ideahub_mysql_data:/data -v $(pwd):/backup \
  alpine tar czf /backup/mysql_backup.tar.gz /data
```

## Development vs Production

### Development Setup

For development, use bind mounts for live code updates:

```yaml
# Add to docker-compose.override.yml
version: '3.8'

services:
  app:
    volumes:
      - .:/var/www/html
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
```

```bash
# Run with override
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

### Production Setup

Use the default `docker-compose.yml` without bind mounts.

## Environment Variables

### Application

| Variable | Default | Description |
|----------|---------|-------------|
| APP_NAME | IdeaHub | Application name |
| APP_ENV | production | Environment |
| APP_DEBUG | false | Debug mode |
| APP_URL | http://localhost:8000 | Application URL |

### Database

| Variable | Default | Description |
|----------|---------|-------------|
| DB_CONNECTION | mysql | Database driver |
| DB_HOST | mysql | Database host |
| DB_DATABASE | ideahub | Database name |
| DB_USERNAME | ideahub | Database user |
| DB_PASSWORD | secret | Database password |

### Cache & Queue

| Variable | Default | Description |
|----------|---------|-------------|
| REDIS_HOST | redis | Redis host |
| CACHE_DRIVER | redis | Cache driver |
| SESSION_DRIVER | redis | Session driver |
| QUEUE_CONNECTION | redis | Queue driver |

## Optimization

### Production Optimizations

```bash
# Cache configuration
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan event:cache

# Optimize Composer
docker-compose exec app composer install --optimize-autoloader --no-dev
```

### Clear Caches

```bash
# Clear all caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## Troubleshooting

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Container Won't Start

```bash
# View detailed logs
docker-compose logs app

# Check container status
docker-compose ps

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database Connection Issues

```bash
# Check MySQL is running
docker-compose ps mysql

# Test database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Check database logs
docker-compose logs mysql
```

### Queue Not Processing

```bash
# Check queue worker status
docker-compose ps queue

# Restart queue worker
docker-compose restart queue

# View queue worker logs
docker-compose logs -f queue
```

## Scaling

### Scale Queue Workers

```bash
# Scale to 5 queue workers
docker-compose up -d --scale queue=5

# Check running workers
docker-compose ps queue
```

### Resource Limits

Add resource limits to `docker-compose.yml`:

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2.0'
          memory: 2G
        reservations:
          cpus: '0.5'
          memory: 512M
```

## Health Checks

### Application Health

```bash
# Health check endpoint
curl http://localhost:8000/api/health

# Should return: "healthy"
```

### Container Health

```bash
# Check container health
docker inspect --format='{{.State.Health.Status}}' ideahub_app
```

## Cleanup

### Remove Containers and Volumes

```bash
# Stop and remove containers
docker-compose down

# Remove containers and volumes
docker-compose down -v

# Remove everything including images
docker-compose down -v --rmi all
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Docker Build

on: [push]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Build Docker image
        run: docker-compose build
      - name: Run tests
        run: docker-compose run app php artisan test
```

## Security

### Best Practices

1. **Change default passwords** in `.env`
2. **Use secrets** for sensitive data
3. **Enable SSL/TLS** in production
4. **Keep images updated** regularly
5. **Scan for vulnerabilities**

```bash
# Scan for vulnerabilities
docker scan ideahub_app
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Best Practices](https://laravel.com/docs/12.x/deployment)

---

**Last Updated:** 2025-11-14
**Docker Version:** 20.10+
**Docker Compose Version:** 2.0+
