#!/bin/bash

# Production deployment hook script
# This runs automatically when code is deployed to production

echo "🚀 Starting production deployment..."

# Run database migrations
echo "📦 Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Deployment completed successfully!" 