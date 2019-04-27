#!/bin/bash
set -e

export APP_ENV="testing"
export DB_CONNECTION="sqlite"
export DB_DATABASE="/tmp/test.db"

if [ -e $DB_DATABASE ]; then rm $DB_DATABASE; fi;
touch $DB_DATABASE
if [ ! -e .env ]; then cp .env.testing .env ; fi;

php artisan bs:install ibara.mayaka@api.test 12345678 hyouka --quiet
php artisan serve --port 32123 --quiet &
cargo test -- --test-threads=1

kill $(ps -ef | grep 'php artisan serve' | awk '{print $2}' | awk -F"/" '{print $1}' | head -n 1)
kill $(ps -ef | grep 'php -S' | awk '{print $2}' | awk -F"/" '{print $1}' | head -n 1)
