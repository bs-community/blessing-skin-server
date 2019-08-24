- [简体中文](./README.md)
- <b>English</b>

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://circleci.com/gh/bs-community/blessing-skin-server"><img src="https://flat.badgen.net/circleci/github/bs-community/blessing-skin-server" alt="Circle CI Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/branch"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.2.12+/orange" alt="PHP 7.2.12+">
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

- Web server with URL rewriting enabled (Nginx or Apache)
- **PHP >= 7.2.12** (use v2.x branch if your server doesn't meet the requirements)
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- GD PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- fileinfo PHP Extension
- zip PHP Extension

## Quick Install

1. Download our [latest release](https://github.com/bs-community/blessing-skin-server/releases), extract to where you like to installed on.
2. Rename `.env.example` to `.env` and configure your database information. (For windows users, just rename it to `.env.`, and the last dot will be removed automatically)
3. For Nginx users, add [rewrite rules](#configure-the-web-server) to your Nginx configuration
4. Navigate to `http://your-domain.com/setup` in your browser. If 404 is returned, please check whether the rewrite rules works correctly.
5. Follow the setup wizard and your website is ready-to-go.

## Plugin System

Blessing Skin provides an elegant and powerful plugin system, and you can attach plenty of functions and customization to your site via installing plugins.

## Supporting Blessing Skin

Welcome to sponsoring Blessing Skin if this software is useful for you!

Currently you can sponsor us via [爱发电](https://afdian.net/@blessing-skin).

### Sponsors

<table>
<tbody>
    <tr>
        <td align=center>
            <a href="https://afdian.net/@hyx5020">
                <img src="https://pic.afdiancdn.com/user/ff73629a6fa811e9abe252540025c377/avatar/b6c5f51467a2036d80d8103840aea9d4_w3264_h1836_s635.jpeg?imageView2/1/w/120/h/120">
                <br>
                hyx5020
            </a>
        </td>
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
        <td align=center>
            <a href="https://afdian.net/@akkariin">
                <img src="https://pic.afdiancdn.com/user/f3f747da859011e98ebe52540025c377/avatar/14752883229fa9f346884dec196a4b8a_w256_h256_s35.jpg?imageView2/1/w/120/h/120">
                <br>
                Akkariin
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/@xiaoye">
                <img src="https://pic.afdiancdn.com/user/3cab3390efed11e88ad552540025c377/avatar/23b9e2fabc1c11019cc67cc075673544_w640_h640_s32.jpg?imageView2/1/w/120/h/120">
                <br>
                星域联盟_晓夜
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
            <a href="https://afdian.net/u/4d9a803ea8a211e9ba9052540025c377">
                <img src="https://pic.afdiancdn.com/default/avatar/default-avatar@2x.png?imageView2/1/w/75/h/75">
                <br>
                爱发电用户_4ft3
            </a>
        </td>
        <td align=center>
            <a href="https://afdian.net/u/a08078a051fc11e9ab4c52540025c377">
                <img src="https://pic.afdiancdn.com/user/a08078a051fc11e9ab4c52540025c377/avatar/9e25e37208832a1a41893ad1bd30a398_w628_h626_s39.jpg?imageView2/1/w/75/h/75">
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

For Apache (most of the virtual hosts) users, there is already a pre-configured file for you. What you need is just to enjoy!

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

Please refer to [Mod Configuration](https://blessing.netlify.com/en/mod.html).

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

## Internationalization

Blessing Skin supports multiple languages, while currently supporting English (`en`) and Simplified Chinese (`zh_CN`).

Of course, you can add your own language. Please check [Add other language [i18n]](https://blessing.netlify.com/i18n.html) (Simplified Chinese only).

If you are willing to contribute your translation, welcome to join [our Crowdin project](https://crowdin.com/project/bs-i18n).

## Report Bugs

Read [FAQ](https://blessing.netlify.com/faq.html) and double check if your situation doesn't suit any case mentioned there before reporting.

When reporting a problem, please attach your log file (located at `storage/logs/laravel.log`) and the information of your server where the error occured on. You should also read this [guide](https://blessing.netlify.com/report.html) before reporting a problem.

## Copyright & License

MIT License

Copyright (c) 2016-present The Blessing Skin Community
