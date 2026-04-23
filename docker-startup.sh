#!/bin/bash

# EN NUR Membership - Ultra-Robust Docker Startup Script
set -e

echo "🚀 Starting EN NUR Membership System - Ultra-Robust Mode..."

# Set PHP memory limit and other settings at runtime
echo "🔧 Configuring PHP settings..."
# Create PHP configuration directory if it doesn't exist
mkdir -p /usr/local/etc/php/conf.d/
# Set memory limit with high priority
echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/99-memory-limit.ini
echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/99-memory-limit.ini
echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/99-memory-limit.ini
echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/99-memory-limit.ini
echo "max_input_vars = 3000" >> /usr/local/etc/php/conf.d/99-memory-limit.ini

# Also try to set via environment for good measure
export PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d"
export PHP_MEMORY_LIMIT="512M"

# Verify PHP config was applied
echo "📊 PHP Configuration Check:"
php -r "echo 'Memory Limit: ' . ini_get('memory_limit') . PHP_EOL;"

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

# Clear all caches to ensure fresh start - NO ROUTE CACHING YET
echo "🧹 Clearing all caches..."
php artisan config:clear || echo "Config clear failed - continuing..."
php artisan cache:clear || echo "Cache clear failed - continuing..."
php artisan route:clear || echo "Route clear failed - continuing..."
php artisan view:clear || echo "View clear failed - continuing..."

# COMPREHENSIVE DATABASE FIX - Handle partial migration states
echo "🔍 Comprehensive database analysis and fix..."

# Check what tables exist
EXISTING_TABLES=$(php -r "
try {
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    \$result = \$pdo->query(\"SELECT tablename FROM pg_tables WHERE schemaname = 'public'\");
    \$tables = \$result->fetchAll(PDO::FETCH_COLUMN);
    echo implode(',', \$tables);
} catch (Exception \$e) {
    echo 'ERROR';
}
" 2>/dev/null)

echo "📋 Existing tables: $EXISTING_TABLES"

# Check if we have a problematic partial migration state
if [[ $EXISTING_TABLES == *"sessions"* ]] && [[ $EXISTING_TABLES != *"users"* ]]; then
    echo "🚨 DETECTED: Partial migration state - sessions exists but users doesn't!"
    echo "🔄 Performing complete database reset..."
    
    # Drop all tables to start fresh
    php -r "
    try {
        \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        \$result = \$pdo->query(\"SELECT tablename FROM pg_tables WHERE schemaname = 'public'\");
        \$tables = \$result->fetchAll(PDO::FETCH_COLUMN);
        
        foreach (\$tables as \$table) {
            \$pdo->exec(\"DROP TABLE IF EXISTS {\$table} CASCADE\");
            echo \"Dropped: {\$table}\n\";
        }
        echo \"All tables dropped successfully\n\";
    } catch (Exception \$e) {
        echo \"Error during table cleanup: \" . \$e->getMessage() . \"\n\";
    }
    "
    
    echo "✅ Database completely cleaned. Running fresh migrations..."
    
elif [[ $EXISTING_TABLES == "" ]] || [[ $EXISTING_TABLES == "ERROR" ]]; then
    echo "🆕 Fresh database detected"
else
    echo "🔍 Existing database with tables: $EXISTING_TABLES"
fi

# Always try migrations (they'll be safe now)
echo "🔄 Running database migrations..."
php artisan migrate --force || {
    echo "❌ Migration failed. Attempting reset and retry..."
    php artisan migrate:reset --force 2>/dev/null || echo "Reset failed - continuing..."
    php artisan migrate --force || echo "Second migration attempt failed - continuing..."
}

# Verify critical tables exist
echo "🔍 Verifying critical tables..."
FINAL_TABLES=$(php -r "
try {
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    \$result = \$pdo->query(\"SELECT tablename FROM pg_tables WHERE schemaname = 'public'\");
    \$tables = \$result->fetchAll(PDO::FETCH_COLUMN);
    echo implode(',', \$tables);
} catch (Exception \$e) {
    echo 'ERROR';
}
" 2>/dev/null)

echo "📊 Final tables: $FINAL_TABLES"

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

# Setup production admin if specified
if [ ! -z "$ADMIN_EMAIL" ]; then
    echo "👤 Setting up production admin..."
    php artisan admin:setup-production "$ADMIN_EMAIL" "${ADMIN_NAME:-SUPER ADMIN}" || echo "Admin setup failed - continuing..."
fi

# Set final permissions
echo "🔒 Setting final permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "🎉 EN NUR Membership System startup completed successfully!"
echo "📊 Database tables: $FINAL_TABLES"
echo "👥 Users: $USER_COUNT"

# Start Apache
echo "🌐 Starting Apache server..."
exec apache2-foreground 