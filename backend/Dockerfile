# Dockerfile
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    ffmpeg \
    cron \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions, including exif
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip exif

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -E -i -e 's/post_max_size = 8M/post_max_size = 128M/' $PHP_INI_DIR/php.ini
RUN sed -E -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 128M/' $PHP_INI_DIR/php.ini
RUN sed -E -i -e 's/max_execution_time = 30/max_execution_time = 60/' $PHP_INI_DIR/php.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#Add cron file
COPY cron/media-cleanup-cron.txt /etc/cron.d/media-cleanup-cron

#Set permissions for cron file
RUN chmod 0644 /etc/cron.d/

#Apply cron job
RUN crontab /etc/cron.d/media-cleanup-cron

# Set working directory
WORKDIR /var/www/symfony

# Copy the project files
COPY . .

# Ensure Symfony Flex is installed
RUN composer require symfony/flex --no-scripts

# Install Symfony dependencies with superuser permission
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install


# Set the correct file permissions
RUN chown -R www-data:www-data /var/www/symfony

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["sh", "-c", "service cron start && php-fpm"]
