FROM composer:latest AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# Retried, and without --prefer-dist: that flag turns a transient 504 from
# api.github.com's zipball endpoint into a hard build failure by disabling the
# fallback to cloning from source. composer.json still prefers dist through
# its config.preferred-install setting.
RUN for attempt in 1 2 3; do \
        composer install \
            --no-dev \
            --no-progress \
            --no-autoloader \
            --no-scripts \
            --no-interaction \
            --ignore-platform-reqs \
        && installed=yes && break; \
        echo "composer install failed (attempt ${attempt}), retrying..."; \
        sleep 15; \
    done; \
    [ "$installed" = yes ]

# Pinned: node:alpine no longer ships yarn, and Corepack resolves the exact
# version from package.json's "packageManager" field.
FROM node:22-alpine AS frontend

ENV COREPACK_ENABLE_DOWNLOAD_PROMPT=0

WORKDIR /app

RUN corepack enable

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

FROM composer:latest AS builder

WORKDIR /app

COPY . ./

COPY --from=vendor /app ./
COPY --from=frontend /app/public ./public
COPY --from=frontend /app/resources/views/assets ./resources/views/assets

# --no-scripts: package:discover boots the application, which needs a database
# that does not exist yet at build time. The entrypoint runs it instead.
#
# .env is a symlink onto the storage volume because the web installer writes
# to it at runtime. It is created by the entrypoint, not baked in: an APP_KEY
# generated here would be identical in every container started from the image.
RUN composer dump-autoload -o --no-dev -n --no-scripts && \
    rm -rf *.config.js *.config.ts tsconfig.* \
      package.json yarn.lock node_modules/ \
      resources/assets/ resources/misc resources/misc/backgrounds/ \
      tools/ && \
    ln -s storage/.env .env

FROM php:8.3-apache

# Pinned rather than "latest" so a rebuild of an old commit installs the same
# extension versions it originally shipped with.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/2.11.12/install-php-extensions /usr/local/bin/

# imagick is required by composer.json and is the default driver in
# config/image.php; the vendor stage only gets away without it because it
# installs with --ignore-platform-reqs. pdo_mysql is not present in the base
# image, so without it the application cannot reach MySQL or MariaDB at all.
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd zip imagick pdo_mysql opcache

WORKDIR /app

COPY --from=builder /app ./

ENV APACHE_DOCUMENT_ROOT=/app/public
RUN chown -R www-data:www-data . && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf && \
    a2enmod rewrite headers

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Plain HTTP only. TLS is expected to be terminated by a reverse proxy in
# front of this container.
EXPOSE 80

VOLUME ["/app/storage"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/setup") === false ? 1 : 0);'

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
