FROM php:8.2-cli-alpine

RUN docker-php-ext-install amqp

WORKDIR /app

COPY consumer.php .

CMD ["php", "consumer.php"]
