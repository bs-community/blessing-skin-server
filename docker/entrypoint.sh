#!/bin/sh
#
# Prepares a container before handing over to Apache.
#
# Everything here is deliberately done at run time rather than at build time:
# the image must not carry an APP_KEY, and the database it talks to only
# exists once the stack is up.
set -e

APP_DIR=/app
ENV_FILE="$APP_DIR/storage/.env"

# storage/ is a volume, so on a fresh deployment it may be empty.
mkdir -p "$APP_DIR/storage/plugins" \
    "$APP_DIR/storage/framework/cache" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/storage/textures"

# The web installer writes to .env itself, so it has to be a real file living
# on the volume. It is seeded once and never overwritten afterwards.
if [ ! -f "$ENV_FILE" ]; then
    echo "[entrypoint] seeding storage/.env"
    cp "$APP_DIR/.env.example" "$ENV_FILE"
fi

# Mirror the container's environment into the file on every boot so that
# changing DB credentials in compose actually takes effect.
set_env() {
    key=$1
    value=$2

    [ -z "$value" ] && return 0

    if grep -qE "^${key}=" "$ENV_FILE"; then
        # A literal value may contain "/", so use a delimiter that cannot.
        sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
    else
        printf '%s=%s\n' "$key" "$value" >>"$ENV_FILE"
    fi
}

set_env APP_ENV "${APP_ENV:-production}"
set_env APP_DEBUG "${APP_DEBUG:-false}"
set_env APP_URL "$APP_URL"
set_env DB_CONNECTION "${DB_CONNECTION:-mysql}"
set_env DB_HOST "$DB_HOST"
set_env DB_PORT "$DB_PORT"
set_env DB_DATABASE "$DB_DATABASE"
set_env DB_USERNAME "$DB_USERNAME"
set_env DB_PASSWORD "$DB_PASSWORD"
set_env DB_PREFIX "$DB_PREFIX"
set_env PLUGINS_DIR "${PLUGINS_DIR:-$APP_DIR/storage/plugins}"

# A key baked into the image would be shared by every deployment that pulls
# it, so one is generated per volume instead.
if ! grep -qE '^APP_KEY=base64:' "$ENV_FILE"; then
    echo "[entrypoint] generating APP_KEY"
    php artisan key:generate --force --no-interaction
fi

chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

if [ "${DB_CONNECTION:-mysql}" != "sqlite" ]; then
    echo "[entrypoint] waiting for ${DB_HOST}:${DB_PORT:-3306}"
    tries=0
    until php -r '
        $h = getenv("DB_HOST"); $p = getenv("DB_PORT") ?: 3306;
        $u = getenv("DB_USERNAME"); $w = getenv("DB_PASSWORD");
        try { new PDO("mysql:host=$h;port=$p", $u, $w); } catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "[entrypoint] database did not become reachable" >&2
            exit 1
        fi
        sleep 2
    done
fi

# Deferred from the build, where no database is reachable.
php artisan package:discover --ansi

# Only migrate an already-installed instance; a fresh one is set up through
# the web installer, which creates storage/install.lock itself.
if [ -f "$APP_DIR/storage/install.lock" ]; then
    echo "[entrypoint] running migrations"
    php artisan migrate --force --no-interaction
fi

exec "$@"
