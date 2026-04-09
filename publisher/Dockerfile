FROM php:8.2-cli-alpine

RUN docker-php-ext-install amqp

WORKDIR /app

COPY publisher.php .
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader

CMD ["php", "publisher.php"]
