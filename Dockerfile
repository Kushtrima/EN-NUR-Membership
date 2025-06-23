# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application files
COPY . /var/www/html

# Create Laravel required directories
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/app/public \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p storage/app/backups

# Set permissions for Laravel directories
RUN chmod -R 777 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Configure Apache to listen on the PORT provided by Render\n\
if [ -n "$PORT" ]; then\n\
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf\n\
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/000-default.conf\n\
fi\n\
\n\
# Run Laravel setup commands\n\
php artisan key:generate --force || echo "Key generation failed"\n\
php artisan config:clear || echo "Config clear failed"\n\
php artisan cache:clear || echo "Cache clear failed"\n\
php artisan route:clear || echo "Route clear failed"\n\
php artisan view:clear || echo "View clear failed"\n\
php artisan migrate --force || echo "Migration failed"\n\
php artisan config:cache || echo "Config cache failed"\n\
php artisan view:cache || echo "View cache failed"\n\
php artisan storage:link || echo "Storage link failed"\n\
\n\
# Set final permissions\n\
chmod -R 777 storage bootstrap/cache\n\
\n\
# Start Apache\n\
exec apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Expose port (will be overridden by Render)
EXPOSE 80

# Use startup script
CMD ["/usr/local/bin/start.sh"] 