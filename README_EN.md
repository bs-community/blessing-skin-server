- [简体中文](./README.md)
- <b>English</b>

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img src="https://github.com/bs-community/blessing-skin-server/workflows/CI/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/branch"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.2.5+/orange" alt="PHP 7.2.5+">
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
- **PHP >= 7.2.5**
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
        <a href="https://afdian.net/@hempflower">
          <img src="https://pic1.afdiancdn.com/user/0f396eb2a37c11e8b93452540025c377/avatar/63368e1c4455486c96d4e789fda50bed_w160_h160_s0.jpg" width="120" height="120">
          <br>
          麻花
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@8mi_admin">
          <img src="https://pic1.afdiancdn.com/user/3beb3fc626e411e98d8852540025c377/avatar/282f4cd47f763244f85b0c1d2693f727_w640_h640_s17.jpg" width="120" height="120">
          <br>
          八蓝米科技丶以勒
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@xiaoye">
          <img src="https://pic1.afdiancdn.com/user/3cab3390efed11e88ad552540025c377/avatar/23b9e2fabc1c11019cc67cc075673544_w640_h640_s32.jpg" width="120" height="120">
          <br>
          星域联盟_晓夜
        </a>
      </td>
      <td align=center>
        <a href="">
          <img src="https://pic1.afdiancdn.com/default/avatar/default-avatar@2x.png" width="120" height="120">
          <br>
          爱发电用户_xQKh
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@Kxnrl">
          <img src="https://pic1.afdiancdn.com/user/f3a0367a79b911ea883352540025c377/avatar/c37aef9b387742ad1e3033f4c57a0028_w801_h801_s500.jpg" width="120" height="120">
          <br>
          Kyle
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
        <a href="">
          <img src="https://pic1.afdiancdn.com/default/avatar/default-avatar@2x.png" width="75" height="75">
          <br>
          爱发电用户_4ft3
        </a>
      </td>
      <td align=center>
        <a href="">
          <img src="https://pic1.afdiancdn.com/user/68d07bf851fc11e98e5652540025c377/avatar/48538be153c8eebc3eb5cb6bc085cde9_w574_h574_s173.jpg" width="75" height="75">
          <br>
          dz_paji
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@whitecola620">
          <img src="https://pic1.afdiancdn.com/user/960addda8f5411e9a08f52540025c377/avatar/db3f11ba4f4dbfa27dde17fbf16948d4_w1080_h1920_s1047.jpg" width="75" height="75">
          <br>
          白可乐乐乐乐
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@zsn741656478">
          <img src="https://pic1.afdiancdn.com/user/d15ed87897f211e8a63852540025c377/avatar/0aabb840877dbf4bd87201d0166f3891_w350_h350_s8.jpg" width="75" height="75">
          <br>
          酷车手BB弹
        </a>
      </td>
    </tr>
  </tbody>
</table>

## Developer Install

Please refer to [here](https://blessing.netlify.com/build.html).

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

If you are willing to contribute your translation, welcome to join [our Crowdin project](https://crowdin.com/project/blessing-skin).

## Report Bugs

Read [FAQ](https://blessing.netlify.com/faq.html) and double check if your situation doesn't suit any case mentioned there before reporting.

When reporting a problem, please attach your log file (located at `storage/logs/laravel.log`) and the information of your server where the error occured on. You should also read this [guide](https://blessing.netlify.com/report.html) before reporting a problem.

## Copyright & License

MIT License

Copyright (c) 2016-present The Blessing Skin Team
