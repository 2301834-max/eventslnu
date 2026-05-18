FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install build dependencies first
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    mysql-dev

# Install runtime dependencies
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    vim \
    supervisor \
    mysql-client \
    openssl \
    ca-certificates \
    oniguruma-dev \
    sqlite-dev \
    libzip-dev \
    libpng-dev \
    freetype-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    bcmath \
    mbstring \
    pcntl \
    zip \
    gd

# Remove build dependencies to reduce image size
RUN apk del .build-deps

# PHP runtime defaults that prevent early header flushing (required for sessions / redirects / CSRF).
RUN { \
      echo "implicit_flush=Off"; \
      echo "output_buffering=4096"; \
      echo "upload_max_filesize=20M"; \
      echo "post_max_size=24M"; \
      echo "memory_limit=256M"; \
    } > /usr/local/etc/php/conf.d/99-lnu.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install Laravel dependencies (skip scripts since .env doesn't exist in build).
# Override COMPOSER_INSTALL_FLAGS in Docker test builds to include dev tools.
ARG COMPOSER_INSTALL_FLAGS="--no-dev --no-interaction --prefer-dist --no-scripts"
RUN composer install $COMPOSER_INSTALL_FLAGS && \
    composer clear-cache

# Create necessary directories and set permissions
RUN mkdir -p storage/logs && \
    mkdir -p storage/framework/cache && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/testing && \
    mkdir -p storage/framework/views && \
    mkdir -p bootstrap/cache && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage && \
    chmod -R 775 bootstrap/cache

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
