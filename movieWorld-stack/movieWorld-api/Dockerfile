FROM php:8.2-apache

# Install Apache configuration
RUN a2enmod rewrite
COPY ./movieWorld-api/docker-api-conf/site.conf /etc/apache2/sites-available/000-default.conf
COPY ./movieWorld-api/docker-api-conf/docker-php-memlimit.ini /usr/local/etc/php/conf.d/

# Install required packages
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpq-dev \
    cron \
    git \
    nano 


# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql zip
RUN docker-php-ext-install pdo pdo_mysql 
RUN docker-php-ext-install exif

# Install crontab
COPY ./movieWorld-api/docker-api-conf/laravel-scheduler.conf /etc/
RUN crontab /etc/laravel-scheduler.conf

# Install entrypoint script
COPY ./movieWorld-api/docker-api-conf/entrypoint.sh /
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
