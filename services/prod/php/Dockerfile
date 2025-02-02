FROM php:8.3-fpm

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    nodejs \
    libmemcached-dev \
    zlib1g-dev \
    libssl-dev \
    && docker-php-ext-install mysqli \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /var/log/php \
    && touch /var/log/php/error.log \
    && chown -R www-data:www-data /var/log/php \
    && chmod 777 /var/log/php/error.log

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./services/prod/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/imgdock

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm install

COPY --chown=www-data:www-data . .

RUN npm run build