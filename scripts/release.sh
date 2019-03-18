#!/bin/bash
set -e
composer install --no-dev
rm -rf vendor/bin
yarn
yarn build
cp .env.example .env
zip -9 -r blessing-skin-server-$RELEASE_TAG.zip \
  app \
  bootstrap \
  config \
  database \
  plugins \
  public \
  resources/lang \
  resources/views \
  routes \
  storage \
  vendor \
  .env \
  artisan \
  LICENSE \
  README.md \
  README_EN.md
