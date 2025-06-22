#!/bin/bash

# EN NUR - MEMBERSHIP System Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

ENVIRONMENT=${1:-production}

echo "🚀 Starting deployment for $ENVIRONMENT environment..."

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if .env file exists
if [ ! -f .env ]; then
    print_error ".env file not found!"
    print_warning "Please create .env file from .env.example"
    exit 1
fi

# Check if APP_ENV is set correctly
APP_ENV=$(grep "APP_ENV=" .env | cut -d '=' -f2)
if [ "$ENVIRONMENT" = "production" ] && [ "$APP_ENV" != "production" ]; then
    print_error "APP_ENV is not set to production in .env file!"
    print_warning "Please update APP_ENV=production in your .env file"
    exit 1
fi

print_status "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

print_status "Generating application key (if needed)..."
php artisan key:generate --no-interaction

print_status "Caching configuration files..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "Setting up storage permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

print_status "Creating storage directories..."
mkdir -p storage/app/backups
mkdir -p storage/app/exports
mkdir -p storage/app/receipts
mkdir -p storage/logs

print_status "Running database migrations..."
if [ "$ENVIRONMENT" = "production" ]; then
    php artisan migrate --force --no-interaction
else
    php artisan migrate --no-interaction
fi

# Ask if user wants to seed the database
echo ""
read -p "Do you want to seed the database with admin user? (y/N): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Seeding database with production admin user..."
    php artisan db:seed --class=ProductionSeeder --no-interaction
fi

print_status "Optimizing application..."
php artisan optimize

print_status "Clearing old caches..."
php artisan cache:clear

print_success "Deployment completed successfully!"

echo ""
echo "📋 POST-DEPLOYMENT CHECKLIST:"
echo "================================"
echo "✅ 1. Verify .env configuration"
echo "✅ 2. Database migrations completed"
echo "✅ 3. Application optimized"
echo ""
echo "⚠️  IMPORTANT - MANUAL STEPS REQUIRED:"
echo "1. 🔐 Change default admin password immediately"
echo "2. 🌐 Configure web server (Nginx/Apache)"
echo "3. 🔒 Set up SSL certificates"
echo "4. 💳 Configure payment gateway webhooks:"
echo "   - Stripe: /webhook/stripe"
echo "   - PayPal: Configure IPN notifications"
echo "5. 📧 Test email configuration"
echo "6. 💾 Set up automated backups"
echo "7. 📊 Configure monitoring and logging"
echo ""
echo "🔗 Application should be accessible at: $(grep APP_URL .env | cut -d '=' -f2)"
echo ""

# Security reminder
if [ "$ENVIRONMENT" = "production" ]; then
    print_warning "SECURITY REMINDER:"
    echo "- Ensure APP_DEBUG=false in .env"
    echo "- Use HTTPS in production"
    echo "- Set up firewall rules"
    echo "- Configure rate limiting"
    echo "- Enable database backups"
    echo "- Monitor application logs"
fi

print_success "Deployment script completed! 🎉" 