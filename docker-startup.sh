#!/bin/bash

# EN NUR Membership - Ultra-Robust Docker Startup Script
set -e

echo "🚀 Starting EN NUR Membership System - Ultra-Robust Mode..."

# Set permissions first (critical for Laravel)
echo "🔧 Setting proper permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force --no-interaction
else
    echo "🔑 Application key already set: ${APP_KEY:0:20}..."
fi

# Wait for database with timeout and better error handling
echo "📊 Waiting for database connection..."
timeout=120
counter=0
until php -r "
try { 
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo 'Database connected successfully';
    exit(0);
} catch (Exception \$e) { 
    echo 'Database connection failed: ' . \$e->getMessage();
    exit(1);
}" 2>/dev/null || [ $counter -eq $timeout ]; do
    echo "⏳ Database not ready yet, waiting... ($counter/$timeout)"
    sleep 2
    counter=$((counter + 1))
done

if [ $counter -eq $timeout ]; then
    echo "❌ Database timeout after $timeout seconds. Trying to continue anyway..."
fi

# Clear all caches to ensure fresh start
echo "🧹 Clearing all caches..."
php artisan config:clear || echo "Config clear failed - continuing..."
php artisan cache:clear || echo "Cache clear failed - continuing..."
php artisan route:clear || echo "Route clear failed - continuing..."
php artisan view:clear || echo "View clear failed - continuing..."

# Check if database is completely empty or has conflicts
echo "🔍 Checking database state..."
DB_STATE=$(php -r "
try {
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    
    // Check if migrations table exists
    \$result = \$pdo->query(\"SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'migrations'\");
    \$migrationsExists = \$result->fetchColumn() > 0;
    
    if (!\$migrationsExists) {
        echo 'FRESH';
        exit(0);
    }
    
    // Check if any user tables exist
    \$result = \$pdo->query(\"SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'users'\");
    \$usersExists = \$result->fetchColumn() > 0;
    
    if (!\$usersExists) {
        echo 'PARTIAL';
        exit(0);
    }
    
    // Check if users table has data
    \$result = \$pdo->query(\"SELECT COUNT(*) FROM users\");
    \$userCount = \$result->fetchColumn();
    
    if (\$userCount == 0) {
        echo 'EMPTY';
    } else {
        echo 'POPULATED';
    }
    
} catch (Exception \$e) {
    echo 'ERROR';
}
" 2>/dev/null)

echo "📋 Database state: $DB_STATE"

# Handle different database states
case $DB_STATE in
    "FRESH")
        echo "🆕 Fresh database - running initial migrations..."
        php artisan migrate --force
        ;;
    "PARTIAL"|"ERROR")
        echo "🔄 Partial/corrupted database - resetting and migrating..."
        php artisan migrate:reset --force 2>/dev/null || echo "Reset failed - continuing..."
        php artisan migrate --force
        ;;
    "EMPTY")
        echo "📝 Empty database - running migrations..."
        php artisan migrate --force
        ;;
    "POPULATED")
        echo "✅ Database already populated - running any pending migrations..."
        php artisan migrate --force
        ;;
esac

# Verify critical tables exist
echo "🔍 Verifying critical tables exist..."
TABLES_CHECK=$(php -r "
try {
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    
    \$required = ['users', 'sessions', 'cache', 'payments', 'membership_renewals'];
    \$missing = [];
    
    foreach (\$required as \$table) {
        \$result = \$pdo->query(\"SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '\$table'\");
        if (\$result->fetchColumn() == 0) {
            \$missing[] = \$table;
        }
    }
    
    if (empty(\$missing)) {
        echo 'ALL_GOOD';
    } else {
        echo 'MISSING:' . implode(',', \$missing);
    }
    
} catch (Exception \$e) {
    echo 'ERROR:' . \$e->getMessage();
}
" 2>/dev/null)

echo "📊 Tables check: $TABLES_CHECK"

# If tables are missing, force a complete reset
if [[ $TABLES_CHECK == MISSING:* ]]; then
    echo "🔄 Critical tables missing - forcing complete reset..."
    php artisan migrate:reset --force 2>/dev/null || echo "Reset failed - continuing..."
    php artisan migrate --force
fi

# Check if users exist, if not run seeder
USER_COUNT=$(php -r "
try {
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    \$result = \$pdo->query('SELECT COUNT(*) FROM users');
    echo \$result->fetchColumn();
} catch (Exception \$e) {
    echo '0';
}
" 2>/dev/null)

echo "👥 User count: $USER_COUNT"

if [ "$USER_COUNT" -eq 0 ]; then
    echo "🌱 No users found - running seeder..."
    php artisan db:seed --class=ProductionSeeder --force
else
    echo "✅ Users already exist - skipping seeder"
fi

# Optimize application
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set final permissions
echo "🔒 Setting final permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "🎉 EN NUR Membership System startup completed successfully!"
echo "📊 Database: $DB_STATE"
echo "📋 Tables: $TABLES_CHECK"
echo "👥 Users: $USER_COUNT"

# Start Apache
echo "🌐 Starting Apache server..."
exec apache2-foreground 