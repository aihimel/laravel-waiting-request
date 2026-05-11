FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    gd \
    intl \
    mbstring \
    pdo_sqlite \
    pcntl \
    xml \
    zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create a system user to run Composer and Artisan Commands
RUN addgroup -g 1000 laravel && adduser -u 1000 -G laravel -S laravel
USER laravel

# Fix git dubious ownership for the current user
RUN git config --global --add safe.directory /var/www/html

# Expose port for testbench serve
EXPOSE 8000

CMD ["php", "-a"]
