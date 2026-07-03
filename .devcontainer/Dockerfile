# [Choice] PHP version (use -bullseye variants on local arm64/Apple Silicon): 8.3, 8.2, 8.3-bullseye, 8.2-bullseye, 8.3-buster, 8.2-buster
ARG VARIANT=8.2-bullseye
FROM mcr.microsoft.com/vscode/devcontainers/php:0-${VARIANT}

# Install MariaDB client
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client zlib1g-dev libpng-dev libzip-dev libwebp-dev \
      libonig-dev libcurl4-openssl-dev libmagickwand-dev imagemagick \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql curl gd zip mbstring \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# [Choice] Node.js version: none, lts/*, 16, 14, 12, 10
ARG NODE_VERSION="none"
RUN if [ "${NODE_VERSION}" != "none" ]; then su vscode -c "umask 0002 && . /usr/local/share/nvm/nvm.sh && nvm install ${NODE_VERSION} 2>&1"; fi

# Enable Apache rewrite module
RUN a2enmod rewrite

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1
