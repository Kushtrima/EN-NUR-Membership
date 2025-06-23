#!/bin/bash

# EN NUR Membership - Robust Docker Startup Script
set -e

echo "🚀 Starting EN NUR Membership System..."

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
    echo 'Connected successfully'; 
    exit(0); 
} catch(PDOException \$e) { 
    echo 'Connection failed: ' . \$e->getMessage(); 
    exit(1); 
}" 2>/dev/null || [ $counter -eq $timeout ]; do
    echo "⏳ Database not ready yet, waiting... ($counter/$timeout)"
    echo "   DB_HOST: $DB_HOST"
    echo "   DB_PORT: $DB_PORT" 
    echo "   DB_DATABASE: $DB_DATABASE"
    echo "   DB_USERNAME: $DB_USERNAME"
    sleep 3
    counter=$((counter + 3))
done

if [ $counter -ge $timeout ]; then
    echo "❌ Database connection timeout after $timeout seconds"
    echo "Environment check:"
    echo "DB_HOST: $DB_HOST"
    echo "DB_PORT: $DB_PORT"
    echo "DB_DATABASE: $DB_DATABASE"
    echo "DB_USERNAME: $DB_USERNAME"
    echo "Attempting to continue anyway..."
else
    echo "✅ Database connection established!"
fi

# Clear any existing caches that might interfere
echo "🧹 Clearing existing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations with verbose output
echo "🔄 Running database migrations (with debug info)..."
echo "Available migrations:"
ls -la database/migrations/ || echo "No migrations directory found"

# Run migrations with verbose output
php artisan migrate --force --verbose || {
    echo "❌ Migrations failed! Let's debug..."
    echo "Database connection test:"
    php artisan tinker --execute="
        try {
            \$connection = DB::connection();
            \$pdo = \$connection->getPdo();
            echo 'PDO connection successful\n';
            \$tables = \$pdo->query('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'')->fetchAll();
            echo 'Existing tables: ' . count(\$tables) . '\n';
            foreach(\$tables as \$table) {
                echo '  - ' . \$table['tablename'] . '\n';
            }
        } catch (\Exception \$e) {
            echo 'Database error: ' . \$e->getMessage() . '\n';
        }
    " || echo "Failed to run database diagnostics"
    
    echo "Attempting to continue anyway..."
}

# List tables after migration
echo "📋 Checking created tables..."
php artisan tinker --execute="
try {
    \$tables = DB::select('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'');
    echo 'Tables in database: ' . count(\$tables) . '\n';
    foreach(\$tables as \$table) {
        echo '  ✓ ' . \$table->tablename . '\n';
    }
} catch (\Exception \$e) {
    echo 'Could not list tables: ' . \$e->getMessage() . '\n';
}
" || echo "Could not check tables"

# Seed database if needed
echo "👤 Checking for existing users..."
USER_COUNT=$(php artisan tinker --execute="
try {
    echo App\\Models\\User::count();
} catch (\Exception \$e) {
    echo '0';
}
" 2>/dev/null || echo "0")

echo "Found $USER_COUNT existing users"

if [ "$USER_COUNT" = "0" ]; then
    echo "👤 Setting up initial admin user..."
    php artisan db:seed --class=ProductionSeeder --force || {
        echo "⚠️ Seeding failed, but continuing..."
    }
else
    echo "👥 Users already exist ($USER_COUNT users), skipping seeding"
fi

# Optimize application (with error handling)
echo "🔧 Optimizing application..."
php artisan config:cache || echo "⚠️ Config cache failed, continuing..."

echo "✅ System ready! Starting Apache..."

# Ensure proper Apache configuration
export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_LOG_DIR=/var/log/apache2
export APACHE_LOCK_DIR=/var/lock/apache2
export APACHE_RUN_DIR=/var/run/apache2

# Start Apache in foreground
exec apache2-foreground 