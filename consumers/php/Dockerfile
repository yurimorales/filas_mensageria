FROM php:8.2-cli-alpine

RUN docker-php-ext-install amqp

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY consumer.php .

CMD ["php", "consumer.php"]
