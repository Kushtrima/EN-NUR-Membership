# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Set PHP memory limit and other optimizations
ENV PHP_MEMORY_LIMIT=512M
ENV PHP_MAX_EXECUTION_TIME=300
ENV PHP_UPLOAD_MAX_FILESIZE=64M
ENV PHP_POST_MAX_SIZE=64M

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

# Install PHP dependencies (skip scripts that contain artisan commands)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Configure Apache with enhanced security
RUN a2enmod rewrite headers deflate expires
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# All Laravel configuration and optimization happens at runtime via startup script

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