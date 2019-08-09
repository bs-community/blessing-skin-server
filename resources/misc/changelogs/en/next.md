## Added

- Plugin system: `config.blade.php` as default config file name.
- Allow to enable a plugin by running `php artisan plugin:enable {name}`.
- Allow to disable a plugin by running `php artisan plugin:disable {name}`.

## Tweaked

- Tweaked policy of retrieve CA cert for GuzzleHttp.
- Refactor account system.

## Fixed

- Some fields at administration panel shouldn't be sortable.
- Add missing l10n text.
- Fixed that model was reset after resetting skin previewing.

## Removed

- Removed Artisan command `php artisan key:random`.
- Removed Artisan commands of migration for v3 to v4.
