# Use an official PHP image with Apache and required extensions
FROM php:7.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    libpq-dev \  
    postgresql-client \
    cron \
    vim

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents to the working directory
COPY . /var/www

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "php-fpm" ]

