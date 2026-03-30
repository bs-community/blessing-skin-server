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
- PHP >= 8.1.0
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
