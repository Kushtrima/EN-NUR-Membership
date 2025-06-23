# This is a dummy Dockerfile to satisfy Render's requirement
# The actual build process is handled by render.yaml

FROM php:8.2-cli

# Copy application files
COPY . /app
WORKDIR /app

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE $PORT

# Start command (will be overridden by render.yaml)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"] 