- [简体中文](./README-zh.md)
- **English**

<p align="center"><img src="https://media.githubusercontent.com/media/bs-community/logo/main/logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/bs-community/blessing-skin-server/CI.yml?branch=dev&style=flat-square"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server"><img alt="Codecov" src="https://img.shields.io/codecov/c/github/bs-community/blessing-skin-server?style=flat-square"></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img alt="GitHub release (latest SemVer including pre-releases)" src="https://img.shields.io/github/v/release/bs-community/blessing-skin-server?include_prereleases&style=flat-square"></a>
<a href="https://github.com/bs-community/blessing-skin-server/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/github/license/bs-community/blessing-skin-server?style=flat-square"></a>
<a href="https://discord.com/invite/QAsyEyt"><img alt="Discord" src="https://discord.com/api/guilds/761226550921658380/widget.png"></a>
</p>

Puzzled by losing your custom skins in Minecraft servers runing in offline mode? Now you can easily get them back with the help of Blessing Skin!

Blessing Skin is a web application where you can upload, manage and share your custom skins & capes! Unlike modifying a resource pack, everyone in the game will see the different skins of each other (of course they should register at the same website too).

Blessing Skin is an open-source project written in PHP, which means you can deploy it freely on your own web server!

## Features

- A fully functional skin hosting service
- Multiple player names can be owned by one user on the website
- Share your skins and capes online with skin library!
- Easy-to-use
  - Visual page for user/player/texture management
  - Detailed option pages
  - Many tweaks for a better UI/UX
- Security
  - Support many secure password hash algorithms
  - Email verification for registration
  - Score system for preventing evil requests
- Incredibly extensible
  - Plenty of plugins available
  - Integration with Authme/Discuz (available as plugin)
  - Support custom Yggdrasil API authentication (available as plugin)

## Requirements

Blessing Skin has only a few system requirements. In most cases, these PHP extensions are already enabled.

- Web server with URL rewriting enabled (Nginx or Apache)
- PHP >= 8.3.0
- PHP Extensions
  - OpenSSL >= 1.1.1 (TLS 1.3)
  - PDO
  - Mbstring
  - Tokenizer
  - GD
  - XML
  - Ctype
  - JSON
  - fileinfo
  - zip
  - Imagick

## Quick Install

Please read [Installation Guide](https://blessing.netlify.app/en/setup.html).

## Run With Docker

The image serves plain HTTP on port 80 and is meant to sit behind a reverse
proxy that terminates TLS.

### With MariaDB

```bash
cp .env.docker.example .env.docker   # set DB_PASSWORD
docker compose --env-file .env.docker up -d --build
```

### With SQLite

No database container; the database file lives on the storage volume.

```bash
docker compose -f docker-compose.sqlite.yml up -d --build
```

Either way the site is then reachable on <http://127.0.0.1:8080>. Point your
reverse proxy at that address and finish setup in the browser.

### Pre-built image

Images are published to GitHub Container Registry on every push to the default
branch and on every `v*` tag:

```bash
docker pull ghcr.io/mk990/blessing-skin-server:latest
```

### Notes

- The port is bound to `127.0.0.1`, assuming the proxy runs on the same host.
  Change it to `"8080:80"` in the compose file if the proxy is elsewhere.
- `APP_KEY` is generated on first boot and stored in the `storage` volume, so
  it survives upgrades. Do not delete that volume unless you intend to lose
  sessions, and keep a backup of it along with your database.
- Application configuration lives at `storage/.env` inside the volume. The
  `DB_*` and `APP_*` values from compose are re-applied to it on every start.
- The application reads the client address from the connecting socket, so
  behind a proxy every user appears to come from the proxy's address. Forward
  the real address at the proxy if you rely on per-IP registration limits.

## Plugin System

Blessing Skin provides an elegant and powerful plugin system, and you can attach plenty of functions and customization to your site via installing plugins.

## Build From Source

Please refer to [Manual Build](https://blessing.netlify.app/build.html).

## Internationalization

Blessing Skin supports multiple languages, while currently supporting English, Simplified Chinese and Spanish.

If you are willing to contribute your translation, welcome to join [our Crowdin project](https://crowdin.com/project/blessing-skin).

## Report Bugs

Read [FAQ](https://blessing.netlify.app/faq.html) and double check if your situation doesn't suit any case mentioned there before reporting.

When reporting a problem, please attach your log file (located at `storage/logs/laravel.log`) and the information of your server where the error occured on. You should also read this [guide](https://blessing.netlify.app/report.html) before reporting a problem.

## Related Links

- [User Manual](https://blessing.netlify.app/en/)
- [Plugins Development Documentation](https://bs-plugin.netlify.app/)

## Copyright & License

MIT License

Copyright (c) 2016-present The Blessing Skin Team
