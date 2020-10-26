- [简体中文](./README.md)
- **English**

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/workflow/status/bs-community/blessing-skin-server/CI?style=flat-square"></a>
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
  - Integration with Authme/CrazyLogin/Discuz (available as plugin)
  - Support custom Yggdrasil API authentication (available as plugin)

## Requirements

Blessing Skin has only a few system requirements. In most cases, these PHP extensions are already enabled.

- Web server with URL rewriting enabled (Nginx or Apache)
- PHP >= 7.4.0
- PHP Extensions
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - GD
  - XML
  - Ctype
  - JSON
  - fileinfo
  - zip

## Quick Install

Please read [Installation Guide](https://blessing.netlify.app/en/setup.html).

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
        <a href="https://afdian.net/@ValiantShishu976400">
          <img src="https://pic1.afdiancdn.com/user/178a08963a5e11e9addd52540025c377/avatar/ece9f089aaf2c2f83204a8de11697caf_w350_h350_s16.jpg" width="120" height="120">
          <br>
          飒爽师叔
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@Luohuayu">
          <img src="https://pic1.afdiancdn.com/user/66c740fad75011ea9fce52540025c377/avatar/870ee9ea29a1c179c435f1ad64aee79b_w640_h640_s52.jpg" width="120" height="120">
          <br>
          落花雨
        </a>
      </td>
      <td align=center>
        <a href="">
          <img src="https://pic1.afdiancdn.com/default/avatar/avatar-purple.png" width="120" height="120">
          <br>
          graytoowolf
        </a>
      </td>
      <td align=center>
        <a href="">
          <img src="https://pic1.afdiancdn.com/user/e227ea708ac911eaa6c852540025c377/avatar/a56a7ceafa12e96ba6750e880f04b7e4_w1024_h1024_s883.jpg" width="120" height="120">
          <br>
          mcha0
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@miaowoo">
          <img src="https://pic1.afdiancdn.com/user/efd59774327511e9bf3d52540025c377/avatar/8ac4598ea31f02db2666810518ea1b5e_w3000_h3000_s1022.jpg" width="120" height="120">
          <br>
          MiaoWoo
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
        <a href="https://afdian.net/@mfwg6">
          <img src="https://pic1.afdiancdn.com/user/18ad3338e58a11e9b29352540025c377/avatar/eb04b4b54975d0d229e77fbcd4220dc4_w1080_h1920_s541.jpg" width="75" height="75">
          <br>
          皮皮帕
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@fsj_mc">
          <img src="https://pic1.afdiancdn.com/user/5ae206b6573c11e9b32352540025c377/avatar/27be12f855c0d52ee4a3abeb8e5e9274_w900_h900_s710.jpg" width="75" height="75">
          <br>
          fsj
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
        <a href="">
          <img src="https://pic1.afdiancdn.com/user/b68f3a9aaef511e9826f52540025c377/avatar/03b244e92f9c4198672ce46e3fd7e100_w690_h690_s129.jpeg" width="75" height="75">
          <br>
          神奇威廉
        </a>
      </td>
      </tr>
    </tbody>
</table>

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
