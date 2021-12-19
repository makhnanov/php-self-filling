FROM php:8.1.1-cli-alpine3.15
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
      && pecl install xdebug \
      && docker-php-ext-enable xdebug \
      && apk del pcre-dev ${PHPIZE_DEPS}
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
