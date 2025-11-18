#!/bin/bash

set -e

echo "🚀 IdeaHub Docker Entrypoint"
echo "=============================="

# Function to wait for database
wait_for_db() {
    echo "⏳ Waiting for database to be ready..."

    for i in {1..30}; do
        if php artisan db:show > /dev/null 2>&1; then
            echo "✅ Database is ready!"
            return 0
        fi
        echo "   Attempt $i/30: Database not ready yet, waiting..."
        sleep 2
    done

    echo "❌ Database connection failed after 30 attempts"
    return 1
}

# Copy .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
    echo "✅ Application key generated"
else
    echo "✅ Application key already set"
fi

# Ensure storage directories exist and have proper permissions
echo "📁 Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✅ Storage directories configured"

# Wait for database to be ready
wait_for_db

# Run database migrations
echo "🗄️  Running database migrations..."
if php artisan migrate --force; then
    echo "✅ Migrations completed successfully"
else
    echo "⚠️  Migrations failed or already up to date"
fi

# Seed database if SEED_DATABASE is set
if [ "${SEED_DATABASE:-false}" = "true" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
    echo "✅ Database seeded"
fi

# Cache optimization
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Application optimized"

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
    echo "✅ Storage symlink created"
fi

echo "=============================="
echo "✅ IdeaHub initialization complete!"
echo "🌐 Application is ready to serve requests"
echo ""

# Execute the main container command
exec "$@"
