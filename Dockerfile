FROM composer:latest as vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --prefer-dist \
    --no-dev \
    --no-suggest \
    --no-progress \
    --no-autoloader \
    --no-scripts \
    --no-interaction \
    --ignore-platform-reqs

FROM node:alpine as frontend

WORKDIR /app

COPY package.json yarn.lock ./
RUN yarn install --frozen-lockfile

COPY postcss.config.js tsconfig.build.json tsconfig.json webpack.config.ts ./
COPY tools/*Plugin.ts ./tools/

COPY resources ./resources

RUN yarn build && \
    cp resources/assets/src/images/bg.webp public/app/ && \
    cp resources/assets/src/images/favicon.ico public/app/ && \
    # Strip unused files
    rm -rf *.config.js *.config.ts tsconfig.* \
      package.json yarn.lock node_modules/ \
      resources/assets/ resources/lang resources/misc resources/misc/backgrounds/ \
      tools/

FROM composer:latest as builder

WORKDIR /app

COPY . ./

COPY --from=vendor /app ./
COPY --from=frontend /app/public ./public
COPY --from=frontend /app/resources/views/assets ./resources/views/assets

RUN composer dump-autoload -o --no-dev -n && \
    rm -rf *.config.js *.config.ts tsconfig.* \
      package.json yarn.lock node_modules/ \
      resources/assets/ resources/misc resources/misc/backgrounds/ \
      tools/ && \
    mv .env.example .env && \
    php artisan key:generate && \
    mv .env storage/ && \
    ln -s storage/.env .env && \
    touch storage/database.db && \
    mkdir storage/plugins && \
    sed 's/PLUGINS_DIR=null/PLUGINS_DIR=\/app\/storage\/plugins/' -i storage/.env && \
    sed 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' -i storage/.env && \
    sed 's/DB_DATABASE=blessingskin/DB_DATABASE=\/app\/storage\/database\.db/' -i storage/.env

FROM php:8-apache

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd zip

WORKDIR /app

COPY --from=builder /app ./

ENV APACHE_DOCUMENT_ROOT /app/public
RUN chown -R www-data:www-data . && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    a2enmod rewrite headers

EXPOSE 80

VOLUME ["/app/storage"]
