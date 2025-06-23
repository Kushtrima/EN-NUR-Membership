#!/bin/bash

# EN NUR Membership - Docker Startup Script
# This script handles database initialization and starts Apache

set -e

echo "🚀 Starting EN NUR Membership System..."

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

# Clear and optimize caches
echo "🔧 Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ System ready! Starting Apache..."

# Start Apache in foreground
exec apache2-foreground 