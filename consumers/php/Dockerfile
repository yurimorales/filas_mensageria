FROM php:8.2-cli-alpine

RUN apk add --no-cache curl && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json ./
RUN composer update --no-dev --optimize-autoloader

COPY consumer.php .

CMD ["php", "consumer.php"]
