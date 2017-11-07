- [简体中文](https://github.com/printempw/blessing-skin-server/blob/master/README.md)
- <b>English</b>

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://travis-ci.org/printempw/blessing-skin-server"><img src="https://api.travis-ci.org/printempw/blessing-skin-server.svg?branch=master" alt="Travis Building Status"></a>
<a href="https://github.com/printempw/blessing-skin-server/releases"><img src="https://poser.pugx.org/printempw/blessing-skin-server/version" alt="Latest Stable Version"></a>
<img src="https://img.shields.io/badge/PHP-5.5.9+-orange.svg" alt="PHP 5.5.9+">
<img src="https://poser.pugx.org/printempw/blessing-skin-server/license" alt="License">
<a href="https://twitter.com/printempw"><img src="https://img.shields.io/twitter/follow/printempw.svg?style=social&label=Follow" alt="Twitter Follow"></a>
</p>

Are you puzzled by losing your custom skins in Minecraft servers runing in offline mode? Now you can easily get them back with the help of Blessing Skin Server!

Blessing Skin Server is a web application where you can upload, manage and share your custom skins & capes! Unlike modifying a resource pack, everyone in the game will see the different skins of each other (of course they should register at the same website too).

Blessing Skin Server is an open-source project written in PHP, which means you can deploy it freely on your own web server! Here is a [live demo](http://skin.prinzeugen.net/).

Features
-----------
- Multiple player names can be owned by one user on the website
- Share your skins and capes online with skin library!
- Easy-to-use
    - Visual page for user/player/texture management
    - Detailed option pages
- Security
    - User passwords will be well encrypted
    - Email verification for registration (available as plugin)
    - Score system for preventing evil requests
- Extensible
    - Plenty of plugins available
    - Integration with Authme/CrazyLogin/Discuz

Requirements
-----------
Blessing Skin Server has a few system requirements. In most cases, these PHP extensions are already enabled.

- Web server with URL rewriting enabled
- **PHP >= 5.5.9** (use v2.x branch if your server doesn't meet the requirements)
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- GD PHP Extension (for generating CAPTCHA)

Quick Install
-----------
1. Download our [latest release](https://github.com/printempw/blessing-skin-server/releases), extract to where you like to installed on.
2. Rename `.env.example` to `.env` and configure your database information. (For windows users, just rename it to `.env.`, and the last dot will be removed automatically)
3. For Nginx users, add [rewrite rules](#configure-the-web-server) to your Nginx configuration
4. Navigate to `http://your-domain.com/setup` in your browser. If 404 is returned, please check whether the rewrite rules works correctly.
5. Follow the setup wizard and your website is ready-to-go.

Plugin System
------------

Blessing Skin Server provides an elegant and powerful plugin system, and you can attach plenty of functions and customization to your site via installing plugins.

- [Official Plugins List](https://github.com/printempw/blessing-skin-server/wiki/%E5%AE%98%E6%96%B9%E6%8F%92%E4%BB%B6%E5%88%97%E8%A1%A8)
- [Example Plugin](https://coding.net/u/printempw/p/blessing-skin-plugins/git/tree/master/example-plugin)
- [Plugin Documentation](https://github.com/g-plane/blessing-skin-plugin-docs)

Developer Install
------------
If you'd like make some contribution on the project, please deploy it from git first.

**You'd better have some experience on shell operations to continue.**

Clone the code from GitHub and install dependencies:

```
$ git clone https://github.com/printempw/blessing-skin-server.git
$ composer install
$ yarn install
```

Run the tests (can be skipped):

```
$ yarn run test
```

Build the things!

```
$ yarn run build
```

Congrats! You made it. Next, please refer to No.2 of [Quick Install].

Configure the Web Server
------------
For Apache (most of the virtual hosts) and IIS users, there is already a pre-configured file for you. What you need is just to enjoy!

For Nginx users, **please add the following rules** to your Nginx configuration file:

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Protect .env file
location ~ /\.env {
    deny all;
}
```

Mod Configuration
------------
See [Wiki - Mod Configuration](https://github.com/printempw/blessing-skin-server/wiki/Mod-Configuration)

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

FAQ
------------
Read [Wiki - FAQ](https://github.com/printempw/blessing-skin-server/wiki/FAQ) and double check if your situation doesn't suit any case mentioned there before reporting.

Report Bugs
------------
Please attach your log file (located at `storage/logs/laravel.log`) when reporting a bug. You should also provide the information of your server where the error occured on. Bugs will be addressed ASAP.

Copyright & License
------------
Copyright (c) 2017 [@printempw](https://prinzeugen.net/) - Released under the GNU General Public License v3.0.

**Exception**: Any plugin developed for Blessing Skin, is not required to adopt GPLv3 License nor release its source code provided no source code from Blessing Skin is contained in the plugin.
