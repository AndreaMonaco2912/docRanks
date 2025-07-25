FROM php:8.2-cli

WORKDIR /usr/src/myapp
COPY . /usr/src/myapp

RUN docker-php-ext-install pdo_mysql mysqli

# Only for development purposes
CMD [ "php", "-S", "0.0.0.0:80" ]
