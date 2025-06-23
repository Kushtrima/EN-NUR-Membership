# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions required for Laravel 11
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    && docker-php-ext-enable \
    pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Create temporary .env for build process
RUN echo 'APP_NAME="EN NUR Membership"' > .env \
    && echo 'APP_ENV=production' >> .env \
    && echo 'APP_KEY=base64:dGhpc2lzYWR1bW15a2V5Zm9yZG9ja2VyYnVpbGQ=' >> .env \
    && echo 'APP_DEBUG=false' >> .env \
    && echo 'APP_URL=http://localhost' >> .env \
    && echo 'DB_CONNECTION=sqlite' >> .env \
    && echo 'DB_DATABASE=":memory:"' >> .env \
    && echo 'LOG_CHANNEL=single' >> .env \
    && echo 'CACHE_DRIVER=array' >> .env \
    && echo 'SESSION_DRIVER=array' >> .env \
    && echo 'QUEUE_CONNECTION=sync' >> .env

# Create Laravel directories if they don't exist
RUN mkdir -p storage/logs \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache

# Set proper permissions AFTER creating directories
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Generate APP_KEY for build (will be overridden at runtime)
RUN php artisan key:generate --force --no-interaction

# Configure Apache with enhanced security
RUN a2enmod rewrite headers deflate expires
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Clean up temporary build .env file (runtime env vars will be injected by Render)
RUN rm -f .env

# Copy startup and health check scripts
COPY docker-startup.sh /usr/local/bin/startup
COPY docker-health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/startup /usr/local/bin/health-check

# Expose port
EXPOSE 80

# Enhanced health check with longer startup time
HEALTHCHECK --interval=30s --timeout=10s --start-period=120s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Use startup script that handles database initialization
CMD ["/usr/local/bin/startup"] 