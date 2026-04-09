FROM php:8.2-cli-alpine

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY consumer.php .

CMD ["php", "consumer.php"]
