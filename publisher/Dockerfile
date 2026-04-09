FROM composer:latest AS builder

WORKDIR /app

COPY composer.json ./
RUN composer update --no-dev --optimize-autoloader --ignore-platform-req=ext-sockets

FROM php:8.2-cli-alpine

WORKDIR /app

COPY --from=builder /app/vendor ./vendor
COPY publisher.php .

CMD ["php", "publisher.php"]
