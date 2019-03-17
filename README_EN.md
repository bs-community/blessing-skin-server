- [简体中文](./README.md)
- <b>English</b>

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://circleci.com/gh/bs-community/blessing-skin-server/tree/v4"><img src="https://flat.badgen.net/circleci/github/bs-community/blessing-skin-server/v4" alt="Circle CI Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/branch/v4"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server/v4" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.1.8+/orange" alt="PHP 7.1.8+">
<img src="https://flat.badgen.net/github/license/bs-community/blessing-skin-server" alt="License">
</p>

**NOTE: The code of the current branch is of Blessing Skin v4.**

Are you puzzled by losing your custom skins in Minecraft servers runing in offline mode? Now you can easily get them back with the help of Blessing Skin!

Blessing Skin is a web application where you can upload, manage and share your custom skins & capes! Unlike modifying a resource pack, everyone in the game will see the different skins of each other (of course they should register at the same website too).

Blessing Skin is an open-source project written in PHP, which means you can deploy it freely on your own web server!

Features
-----------
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
    - Integration with Authme/CrazyLogin/Discuz (available as plugin)
    - Support custom Yggdrasil API authentication (available as plugin)

Requirements
-----------
Blessing Skin has only a few system requirements. _In most cases, these PHP extensions are already enabled._

- Web server with URL rewriting enabled
- **PHP >= 7.1.8** (use v2.x branch if your server doesn't meet the requirements)
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- GD PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- fileinfo PHP Extension

Quick Install
-----------
1. Download our [latest release](https://github.com/bs-community/blessing-skin-server/releases), extract to where you like to installed on.
2. Rename `.env.example` to `.env` and configure your database information. (For windows users, just rename it to `.env.`, and the last dot will be removed automatically)
3. For Nginx users, add [rewrite rules](#configure-the-web-server) to your Nginx configuration
4. Navigate to `http://your-domain.com/setup` in your browser. If 404 is returned, please check whether the rewrite rules works correctly.
5. Follow the setup wizard and your website is ready-to-go.

Plugin System
------------

Blessing Skin provides an elegant and powerful plugin system, and you can attach plenty of functions and customization to your site via installing plugins.

For more information, please refer to [Wiki - Introducing plugin system](https://github.com/bs-community/blessing-skin-server/wiki/%E6%8F%92%E4%BB%B6%E7%B3%BB%E7%BB%9F%E4%BB%8B%E7%BB%8D).

Developer Install
------------
If you'd like make some contribution on the project, please deploy it from GitHub first.

**You'd better have some experience on shell operations to continue.**

Please make sure you have installed the tools below:

- [Git](https://git-scm.org)
- [Node.js](https://nodejs.org)
- [Yarn](https://yarnpkg.com)
- [Composer](https://getcomposer.org)

Clone the code from GitHub and install dependencies:

```bash
git clone https://github.com/bs-community/blessing-skin-server.git
cd blessing-skin-server
composer install
yarn
```

Build the things!

```bash
yarn build
```

Congrats! You made it. Next, please refer to No.2 of **Quick Install** section.

Configure the Web Server
------------
For Apache (most of the virtual hosts) and IIS users, there is already a pre-configured file for you. What you need is just to enjoy!

For Nginx users, **please add the following rules** to your Nginx configuration file:

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ /\.env {
    deny all;
}
```

Mod Configuration
------------
Please refer to [Wiki - Mod Configuration](https://github.com/bs-community/blessing-skin-server/wiki/%E5%A6%82%E4%BD%95%E9%85%8D%E7%BD%AE%E7%9A%AE%E8%82%A4-Mod).

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

Internationalization (i18n)
------------
Blessing Skin can support multiple languages, currently support English (`en`) and Simplfied Chinese (`zh_CN`).

Of course, you can add your own language. Please check [Wiki - Add other language [i18n]](https://github.com/bs-community/blessing-skin-server/wiki/%E6%B7%BB%E5%8A%A0%E5%85%B6%E4%BB%96%E8%AF%AD%E8%A8%80-%5Bi18n%5D) (Simplfied Chinese only).

If you are willing to contribute your translation, welcome to join [our Crowdin project](https://crowdin.com/project/bs-i18n).

Report Problems
------------
Read [Wiki - FAQ](https://github.com/bs-community/blessing-skin-server/wiki/FAQ) and double check if your situation doesn't suit any case mentioned there before reporting.

When reporting a problem, please attach your log file (located at `storage/logs/laravel.log`) and the information of your server where the error occured on. You should also read this [guide](https://github.com/bs-community/blessing-skin-server/wiki/%E6%8A%A5%E5%91%8A%E9%97%AE%E9%A2%98%E7%9A%84%E6%AD%A3%E7%A1%AE%E5%A7%BF%E5%8A%BF) before reporting a problem.

Copyright & License
------------
Copyright 2016-2019 [printempw](https://blessing.studio/) and contributors.

Blessing Skin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3.

**Exception**: Any plugin developed for Blessing Skin, is not required to adopt GPLv3 License nor release its source code, provided no source code from Blessing Skin is contained in the plugin.
