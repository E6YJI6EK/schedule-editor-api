FROM php:8.4-apache
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo_mysql zip gd \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/html
COPY . /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN git config --global --add safe.directory /var/www/html \
  && composer install --no-interaction --prefer-dist --optimize-autoloader \
  && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
ENV APP_ENV=local APP_DEBUG=true DB_CONNECTION=mysql
EXPOSE 80
CMD ["apache2-foreground"]
