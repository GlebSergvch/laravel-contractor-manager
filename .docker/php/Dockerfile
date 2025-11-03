FROM php:8.4-fpm

WORKDIR /var/www/backend

RUN apt-get update && apt-get install -y \
    git \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    wget \
    --no-install-recommends \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo_pgsql -j$(nproc) gd curl mbstring exif

RUN apt-get update && apt-get install -y curl && \
  curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install pdo_pgsql pgsql

RUN apt-get install -y postgresql-client

COPY ./backend ./

RUN composer install --optimize-autoloader --no-scripts

RUN echo "postgresql:54321:contractor_test:postgres:postgres" > /var/local/.pgpass

RUN chown -R $USER:www-data storage
RUN chown -R $USER:www-data bootstrap/cache
RUN chown -R $USER:www-data /var/local/.pgpass

RUN chmod -R 777 storage
RUN chmod -R 777 bootstrap/cache
RUN chmod 0600 /var/local/.pgpass

EXPOSE 9000
