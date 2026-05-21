FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_sqlite

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
