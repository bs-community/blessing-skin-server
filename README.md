- <b>简体中文</b>
- [English](./README_EN.md)

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img src="https://github.com/bs-community/blessing-skin-server/workflows/CI/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.2.5+/orange" alt="PHP 7.2.5+">
<img src="https://flat.badgen.net/github/license/bs-community/blessing-skin-server" alt="License">
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

Blessing Skin 对您的服务器有一定的要求。_在大多数情况下，下列所需的 PHP 扩展已经开启。_

- 一台支持 URL 重写的主机，Nginx 或 Apache
- **PHP >= 7.2.5** [（服务器不支持？）](https://blessing.netlify.com/versions.html)
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

请参阅 [安装指南](https://blessing.netlify.com/setup.html)。

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

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
        <a href="https://afdian.net/@8mi_admin">
          <img src="https://pic1.afdiancdn.com/user/3beb3fc626e411e98d8852540025c377/avatar/282f4cd47f763244f85b0c1d2693f727_w640_h640_s17.jpg" width="120" height="120">
          <br>
          八蓝米科技丶以勒
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

## 自行构建

详情可阅读 [这里](https://blessing.netlify.com/build.html)。

> 您可以订阅我们的 Telegram 频道 [Blessing Skin News](https://t.me/blessing_skin_news) 来获取最新开发动态。当有新的 Commit 被推送时，我们的机器人将会在频道内发送一条消息来提示您能否拉取最新代码，以及拉取后应该做什么。

## 国际化（i18n）

Blessing Skin 可支持多种语言，当前支持英语（`en`）和简体中文（`zh_CN`）。

当然，您也可以添加您自己的语言。请参阅 [添加其它语言](https://blessing.netlify.com/i18n.html)

如果您愿意将您的翻译贡献出来，欢迎参与 [我们的 Crowdin 项目](https://crowdin.com/project/blessing-skin)。

## 问题报告

请参阅 [报告问题的正确姿势](https://blessing.netlify.com/report.html)。

## 版权

MIT License

Copyright (c) 2016-present The Blessing Skin Team

程序原作者为 [@printempw](https://blessing.studio/)，转载请注明。
