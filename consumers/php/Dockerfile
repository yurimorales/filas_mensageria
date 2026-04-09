FROM composer:latest AS builder

WORKDIR /app

COPY composer.json ./
RUN composer update --no-dev --optimize-autoloader

FROM php:8.2-cli-alpine

WORKDIR /app

COPY --from=builder /app/vendor ./vendor
COPY consumer.php .

CMD ["php", "consumer.php"]
