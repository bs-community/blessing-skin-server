- [简体中文](./README.md)
- <b>English</b>

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img src="https://github.com/bs-community/blessing-skin-server/workflows/CI/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server" alt="Codecov" /></a>
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

Blessing Skin has only a few system requirements. In most cases, these PHP extensions are already enabled.

- Web server with URL rewriting enabled (Nginx or Apache)
- PHP >= 7.2.5
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
        <a href="https://afdian.net/@hempflower">
          <img src="https://pic1.afdiancdn.com/user/0f396eb2a37c11e8b93452540025c377/avatar/63368e1c4455486c96d4e789fda50bed_w160_h160_s0.jpg" width="120" height="120">
          <br>
          麻花
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@ValiantShishu976400">
          <img src="https://pic1.afdiancdn.com/user/178a08963a5e11e9addd52540025c377/avatar/ece9f089aaf2c2f83204a8de11697caf_w350_h350_s16.jpg" width="120" height="120">
          <br>
          飒爽师叔
        </a>
      </td>
      <td align=center>
        <a href="">
          <img src="https://pic1.afdiancdn.com/user/5b1b5ef6a23c11ea90a952540025c377/avatar/5c02c61401606370e1b088955c1a10fc_w342_h342_s33.jpg" width="120" height="120">
          <br>
          诶呀好气呀
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@HyperCol_Studio">
          <img src="https://pic1.afdiancdn.com/user/ad213afe31b311e991c252540025c377/avatar/cb8f7ef0832124d336839cdb4a784e14_w2000_h2000_s1992.jpg" width="120" height="120">
          <br>
          HyperCol
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
          <img src="https://pic1.afdiancdn.com/default/avatar/avatar-purple.png" width="120" height="120">
          <br>
          爱发电用户_9JTs
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@Kxnrl">
          <img src="https://pic1.afdiancdn.com/user/f3a0367a79b911ea883352540025c377/avatar/c37aef9b387742ad1e3033f4c57a0028_w801_h801_s500.jpg" width="120" height="120">
          <br>
          Kyle
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@mengluorj">
          <img src="https://pic1.afdiancdn.com/user/ffc6500452ed11e9994e52540025c377/avatar/ae9c5ec36b51e8314787cc19acf2d12e_w815_h815_s459.jpg" width="120" height="120">
          <br>
          MengLuoRJ
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
          <img src="https://pic1.afdiancdn.com/user/68d07bf851fc11e98e5652540025c377/avatar/48538be153c8eebc3eb5cb6bc085cde9_w574_h574_s173.jpg" width="75" height="75">
          <br>
          dz_paji
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
