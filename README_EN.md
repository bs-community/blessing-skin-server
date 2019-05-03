- [简体中文](./README.md)
- <b>English</b>

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://circleci.com/gh/bs-community/blessing-skin-server"><img src="https://flat.badgen.net/circleci/github/bs-community/blessing-skin-server" alt="Circle CI Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/branch"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.1.8+/orange" alt="PHP 7.1.8+">
<img src="https://flat.badgen.net/github/license/bs-community/blessing-skin-server" alt="License">
</p>

Are you puzzled by losing your custom skins in Minecraft servers runing in offline mode? Now you can easily get them back with the help of Blessing Skin!

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
    - Integration with Authme/CrazyLogin/Discuz (available as plugin)
    - Support custom Yggdrasil API authentication (available as plugin)

## Requirements

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

## Quick Install

1. Download our [latest release](https://github.com/bs-community/blessing-skin-server/releases), extract to where you like to installed on.
2. Rename `.env.example` to `.env` and configure your database information. (For windows users, just rename it to `.env.`, and the last dot will be removed automatically)
3. For Nginx users, add [rewrite rules](#configure-the-web-server) to your Nginx configuration
4. Navigate to `http://your-domain.com/setup` in your browser. If 404 is returned, please check whether the rewrite rules works correctly.
5. Follow the setup wizard and your website is ready-to-go.

## Plugin System

Blessing Skin provides an elegant and powerful plugin system, and you can attach plenty of functions and customization to your site via installing plugins.

For more information, please refer to [Wiki - Introducing plugin system](https://github.com/bs-community/blessing-skin-server/wiki/%E6%8F%92%E4%BB%B6%E7%B3%BB%E7%BB%9F%E4%BB%8B%E7%BB%8D).

## Supporting Blessing Skin

Welcome to sponsoring Blessing Skin if this software is useful for you!

Currently you can sponsor us via [爱发电](https://afdian.net/@blessing-skin).

### Sponsors

<table>
<tbody>
    <tr>
        <td align=center>
            <a href="https://afdian.net/u/68d07bf851fc11e98e5652540025c377">
                <img src="https://pic.afdiancdn.com/user/68d07bf851fc11e98e5652540025c377/avatar/59b21c3d053a595086d4b6cf88877bfa_w640_h640_s57.jpg?imageView2/1/w/120/h/120">
                <br>
                dz_paji
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/@ExDragine">
                <img src="https://pic.afdiancdn.com/user/ad213afe31b311e991c252540025c377/avatar/33d21c924f446a41073caa5d88be69b8_w200_h200_s36.jpg?imageView2/1/w/120/h/120">
                <br>
                ExDragine
            </a>
        </td>
    </tr>
</tbody>
</table>

### Backers

<table>
<tbody>
    <tr>
        <td align=center>
            <a href="https://afdian.net/u/a08078a051fc11e9ab4c52540025c377">
                <img src="https://pic.afdiancdn.com/default/avatar/default-avatar@2x.png?imageView2/1/w/75/h/75">
                <br>
                pppwaw
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/@tnqzh123">
                <img src="https://pic.afdiancdn.com/user/97a0416ca47211e8849452540025c377/avatar/d2f6d8d489cb952ff29740e715b067c0_w768_h768_s211.jpg?imageView2/1/w/75/h/75">
                <br>
                Little_Qiu
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/@hempflower">
                <img src="https://pic.afdiancdn.com/user/0f396eb2a37c11e8b93452540025c377/avatar/bee35eb0f5cd2a506eb34c6e13de1154_w160_h160_s0.jpg?imageView2/1/w/75/h/75">
                <br>
                麻花
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/@mgcraft">
                <img src="https://pic.afdiancdn.com/user/de46a20a56f111e981a452540025c377/avatar/ab13b606230af1b5f5879538d9e37c43_w640_h640_s22.jpeg?imageView2/1/w/75/h/75">
                <br>
                Mangocraft
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/@acilicraft">
                <img src="https://pic.afdiancdn.com/user/63d4adac633311e98d9d52540025c377/avatar/50c279016873b7907ce7b901de1f560c_w577_h525_s248.jpg?imageView2/1/w/75/h/75">
                <br>
                Andy_Chuck
            </a>
        </td>
    </tr>
</tbody>
</table>

## Developer Install

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

## Configure the Web Server

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

## Mod Configuration

Please refer to [Wiki - Mod Configuration](https://github.com/bs-community/blessing-skin-server/wiki/%E5%A6%82%E4%BD%95%E9%85%8D%E7%BD%AE%E7%9A%AE%E8%82%A4-Mod).

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

## Internationalization

Blessing Skin supports multiple languages, while currently supporting English (`en`) and Simplified Chinese (`zh_CN`).

Of course, you can add your own language. Please check [Wiki - Add other language [i18n]](https://github.com/bs-community/blessing-skin-server/wiki/%E6%B7%BB%E5%8A%A0%E5%85%B6%E4%BB%96%E8%AF%AD%E8%A8%80-%5Bi18n%5D) (Simplified Chinese only).

If you are willing to contribute your translation, welcome to join [our Crowdin project](https://crowdin.com/project/bs-i18n).

## Report Bugs

Read [Wiki - FAQ](https://github.com/bs-community/blessing-skin-server/wiki/FAQ) and double check if your situation doesn't suit any case mentioned there before reporting.

When reporting a problem, please attach your log file (located at `storage/logs/laravel.log`) and the information of your server where the error occured on. You should also read this [guide](https://github.com/bs-community/blessing-skin-server/wiki/%E6%8A%A5%E5%91%8A%E9%97%AE%E9%A2%98%E7%9A%84%E6%AD%A3%E7%A1%AE%E5%A7%BF%E5%8A%BF) before reporting a problem.

## Copyright & License

MIT License

Copyright (c) 2016-present The Blessing Skin Community
