- <b>简体中文</b>
- [English](./README_EN.md)

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/workflow/status/bs-community/blessing-skin-server/CI?style=flat-square"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server"><img alt="Codecov" src="https://img.shields.io/codecov/c/github/bs-community/blessing-skin-server?style=flat-square"></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img alt="GitHub release (latest SemVer including pre-releases)" src="https://img.shields.io/github/v/release/bs-community/blessing-skin-server?include_prereleases&style=flat-square"></a>
<a href="https://github.com/bs-community/blessing-skin-server/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/github/license/bs-community/blessing-skin-server?style=flat-square"></a>
</p>

优雅的开源 Minecraft 皮肤站，现在，回应您的等待。

Blessing Skin 是一款能让您上传、管理和分享您的 Minecraft 皮肤和披风的 Web 应用程序。与修改游戏材质包不同的是，所有人都能在游戏中看到各自的皮肤和披风（当然，前提是玩家们要使用同一个皮肤站）。

Blessing Skin 是一个开源的 PHP 项目，这意味着您可以自由地在您的服务器上部署它。

## 特性

- 完整实现了一个皮肤站该有的功能
- 支持单用户多个角色
- 通过皮肤库来分享您的皮肤和披风！
- 易于使用
    - 可视化的用户、角色、材质管理页面
    - 详细的站点配置页面
    - 多处 UI/UX 优化只为更好的用户体验
- 安全
    - 支持多种安全密码 Hash 算法
    - 注册可要求 Email 验证
    - 防止恶意请求的积分系统
- 强大的可扩展性
    - 多种多样的插件
    - 支持与 Authme/Discuz 等程序的用户数据对接（插件）
    - 支持自定义 Yggdrasil API 外置登录系统（插件）

## 环境要求

Blessing Skin 对您的服务器有一定的要求。在大多数情况下，下列所需的 PHP 扩展已经开启。

- 一台支持 URL 重写的主机，Nginx 或 Apache
- PHP >= 7.2.5
- 安装并启用如下 PHP 扩展：
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

## 快速使用

请参阅 [安装指南](https://blessing.netlify.app/setup.html)。

## 插件系统

Blessing Skin 提供了强大的插件系统，您可以通过添加多种多样的插件来为您的皮肤站添加功能。

## 支持并赞助 Blessing Skin

如果您觉得这个软件对您很有帮助，欢迎通过赞助来支持开发！

目前可在 [爱发电](https://afdian.net/@blessing-skin) 上赞助。

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
      </tr>
    <tr>
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

## 自行构建

详情可阅读 [这里](https://blessing.netlify.com/build.html)。

> 您可以订阅我们的 Telegram 频道 [Blessing Skin News](https://t.me/blessing_skin_news) 来获取最新开发动态。当有新的 Commit 被推送时，我们的机器人将会在频道内发送一条消息来提示您能否拉取最新代码，以及拉取后应该做什么。

## 国际化（i18n）

Blessing Skin 可支持多种语言，当前支持英语、简体中文和西班牙语。

如果您愿意将您的翻译贡献出来，欢迎参与 [我们的 Crowdin 项目](https://crowdin.com/project/blessing-skin)。

## 问题报告

请参阅 [报告问题的正确姿势](https://blessing.netlify.com/report.html)。

## 相关链接

- [用户手册](https://blessing.netlify.app/)
- [插件开发文档](https://bs-plugin.netlify.app/)

## 版权

MIT License

Copyright (c) 2016-present The Blessing Skin Team

程序原作者为 [@printempw](https://blessing.studio/)，转载请注明。
