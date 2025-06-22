#!/bin/bash

# Database Backup & Monitoring Setup Script
# For EN NUR - MEMBERSHIP Laravel Application

echo "🔧 Setting up Database Backup & Monitoring System..."
echo "=================================================="

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p storage/app/backups
mkdir -p storage/logs
chmod 755 storage/app/backups
chmod 755 storage/logs

# Test database backup
echo "🗄️  Testing database backup..."
php artisan db:backup

# Test compressed backup
echo "🗜️  Testing compressed backup..."
php artisan db:backup --compress

# Test database monitoring
echo "📊 Testing database monitoring..."
php artisan db:monitor

# Check Laravel scheduler
echo "⏰ Checking Laravel scheduler..."
php artisan schedule:list

# Create cron job entry
echo "📅 Setting up cron job..."
echo "# Laravel Scheduler for EN NUR - MEMBERSHIP"
echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "⚠️  IMPORTANT: Add the above line to your crontab:"
echo "   Run: crontab -e"
echo "   Add the line above"
echo ""

# Display backup files
echo "📋 Current backup files:"
ls -lh storage/app/backups/

# Display monitoring commands
echo ""
echo "🛠️  Available Commands:"
echo "   php artisan db:backup              - Create database backup"
echo "   php artisan db:backup --compress   - Create compressed backup"
echo "   php artisan db:backup --cloud      - Upload to cloud (if configured)"
echo "   php artisan db:monitor             - Monitor database health"
echo "   php artisan db:monitor --alert     - Monitor with alerts"
echo "   php artisan log:clear              - Clean old log files"
echo ""

# Display scheduled tasks
echo "📅 Scheduled Tasks:"
echo "   Daily 2:00 AM    - Database backup (compressed, keep 30)"
echo "   Weekly Sunday 3:00 AM - Cloud backup (if configured)"
echo "   Every hour       - Database monitoring"
echo "   Daily 8:00 AM    - Comprehensive monitoring report"
echo "   Weekly Monday 4:00 AM - Log cleanup"
echo ""

echo "✅ Database Backup & Monitoring setup complete!"
echo "🎉 Your database is now protected with automated backups and monitoring." 