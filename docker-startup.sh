#!/bin/bash

# EN NUR Membership - Docker Startup Script
# This script handles database initialization and starts Apache

set -e

echo "🚀 Starting EN NUR Membership System..."

# Generate APP_KEY if not set (for Docker environments)
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force --no-interaction
else
    echo "🔑 Application key already set"
fi

# Wait for database to be ready
echo "📊 Waiting for database connection..."
until php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected';" 2>/dev/null; do
    echo "⏳ Database not ready yet, waiting 5 seconds..."
    sleep 5
done

echo "✅ Database connection established!"

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Check if we need to seed the database (only if no users exist)
USER_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "👤 Setting up initial admin user..."
    php artisan db:seed --class=ProductionSeeder --force
else
    echo "👥 Users already exist, skipping seeding"
fi

# Clear and optimize caches (with better error handling)
echo "🔧 Optimizing application..."
php artisan config:clear || echo "⚠️ Config clear failed, continuing..."
php artisan config:cache || echo "⚠️ Config cache failed, continuing..."

# Only cache routes and views if no errors
if php artisan route:list >/dev/null 2>&1; then
    php artisan route:cache || echo "⚠️ Route cache failed, continuing..."
else
    echo "⚠️ Skipping route cache due to route errors"
fi

if php artisan view:clear >/dev/null 2>&1; then
    php artisan view:cache || echo "⚠️ View cache failed, continuing..."
else
    echo "⚠️ Skipping view cache due to view errors"
fi

echo "✅ System ready! Starting Apache..."

# Ensure Apache binds to 0.0.0.0 and correct port for Render
export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_LOG_DIR=/var/log/apache2
export APACHE_LOCK_DIR=/var/lock/apache2
export APACHE_RUN_DIR=/var/run/apache2

# Start Apache in foreground
exec apache2-foreground 