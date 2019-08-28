## Added

- Plugin system: `config.blade.php` as default config file name.
- Plugin system: Allow to registering service providers automatically.
- Plugin system: Added Filters API.
- Allow to enable a plugin by running `php artisan plugin:enable {name}`.
- Allow to disable a plugin by running `php artisan plugin:disable {name}`.
- Allow to cache options by running `php artisan options:cache`.
- Support multiple plugins directories. (Splited by comma in ".env" file.)

## Tweaked

- Tweaked policy of retrieve CA cert for GuzzleHttp.
- Refactor account system.
- PHP version requirement is increased to 7.2.0.

## Fixed

- Some fields at administration panel shouldn't be sortable.
- Add missing l10n text.
- Fixed that model was reset after resetting skin previewing.
- Fixed that error stack doesn't show paths from plugins when AJAX has an error.

## Removed

- Removed Artisan command `php artisan key:random`.
- Removed Artisan commands of migration for v3 to v4.
- Dropped support of IIS.
