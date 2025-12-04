FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git curl unzip

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY ../.. .

# Install Laravel dependencies
RUN composer install

CMD ["php-fpm"]

EXPOSE 9000
