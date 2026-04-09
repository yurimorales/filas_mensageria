FROM composer:1.10 AS builder

WORKDIR /app

COPY publisher/composer.json ./
RUN composer update --no-dev --optimize-autoloader

FROM php:8.2-cli-alpine

WORKDIR /app

COPY --from=builder /app/vendor ./vendor
COPY publisher/publisher.php .

CMD ["php", "publisher.php"]
