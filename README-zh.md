- **简体中文**
- [English](./README.md)

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/bs-community/blessing-skin-server/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/workflow/status/bs-community/blessing-skin-server/CI?style=flat-square"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server"><img alt="Codecov" src="https://img.shields.io/codecov/c/github/bs-community/blessing-skin-server?style=flat-square"></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img alt="GitHub release (latest SemVer including pre-releases)" src="https://img.shields.io/github/v/release/bs-community/blessing-skin-server?include_prereleases&style=flat-square"></a>
<a href="https://github.com/bs-community/blessing-skin-server/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/github/license/bs-community/blessing-skin-server?style=flat-square"></a>
<a href="https://discord.com/invite/QAsyEyt"><img alt="Discord" src="https://discord.com/api/guilds/761226550921658380/widget.png"></a>
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
- PHP >= 7.4.0
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
        <a href="https://afdian.net/@kcraft233">
          <img src="https://pic1.afdiancdn.com/user/2aac23481b1b11ea9f6e52540025c377/avatar/14ed7996f36eec4d9feec0fe613663cf_w512_h512_s68.jpg" width="120" height="120">
          <br>
          gao_cai_sheng
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
        <a href="https://afdian.net/@LD_fantasy">
          <img src="https://pic1.afdiancdn.com/user/9bed7bb454f011eb821652540025c377/avatar/cb679e3eac693e0eea2eac527c7954e0_w700_h1307_s137.jpg" width="120" height="120">
          <br>
          K_LazyCat
        </a>
      </td>
      <td align=center>
        <a href="https://afdian.net/@nmzy2018">
          <img src="https://pic1.afdiancdn.com/user/a66f79d2f5a311e9af4e52540025c377/avatar/e7f69cacfb8d77f431eb97293e569c72_w500_h500_s21.jpg" width="120" height="120">
          <br>
          虎牙伊南
        </a>
      </td>
      <td align=center>
        <a href="">
          <img src="https://pic1.afdiancdn.com/default/avatar/avatar-blue.png" width="120" height="120">
          <br>
          家乐
        </a>
      </td>
      </tr>
    <tr>
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
        <a href="https://afdian.net/@ValiantShishu976400">
          <img src="https://pic1.afdiancdn.com/user/178a08963a5e11e9addd52540025c377/avatar/ece9f089aaf2c2f83204a8de11697caf_w350_h350_s16.jpg" width="75" height="75">
          <br>
          飒爽师叔
        </a>
      </td>
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

程序原作者为 [@printempw](https://printempw.github.io/)，转载请注明。
