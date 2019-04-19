- <b>简体中文</b>
- [English](./README_EN.md)

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://circleci.com/gh/bs-community/blessing-skin-server/tree/v4"><img src="https://flat.badgen.net/circleci/github/bs-community/blessing-skin-server/v4" alt="Circle CI Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/branch/v4"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server/v4" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.1.8+/orange" alt="PHP 7.1.8+">
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

- 一台支持 URL 重写的主机，Nginx、Apache 或 IIS
- **PHP >= 7.1.8** [（服务器不支持？）](https://github.com/bs-community/blessing-skin-server/wiki/%E7%89%88%E6%9C%AC%E8%AF%B4%E6%98%8E)
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

## 快速使用

请参阅 [Wiki - 快速安装向导](https://github.com/bs-community/blessing-skin-server/wiki/%E5%BF%AB%E9%80%9F%E5%AE%89%E8%A3%85%E5%90%91%E5%AF%BC)。

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

## 插件系统

Blessing Skin 提供了强大的插件系统，您可以通过添加多种多样的插件来为您的皮肤站添加功能。

详情请参阅 [Wiki - 插件系统介绍](https://github.com/bs-community/blessing-skin-server/wiki/%E6%8F%92%E4%BB%B6%E7%B3%BB%E7%BB%9F%E4%BB%8B%E7%BB%8D)。

## 支持并赞助 Blessing Skin

如果您觉得这个软件对您很有帮助，欢迎通过赞助来支持开发！

目前可在 [爱发电](https://afdian.net/@blessing-skin) 上赞助。

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
    </tr>
</tbody>
</table>

## 自行构建

如果你想为此项目作贡献，或者抢先尝试未发布的新功能，你应该先用 GitHub 上的代码部署。

**不推荐不熟悉 shell 操作以及不想折腾的用户使用。**

请先确保您安装好以下工具：

- [Git](https://git-scm.org)
- [Node.js](https://nodejs.org)
- [Yarn](https://yarnpkg.com)
- [Composer](https://getcomposer.org)

从 GitHub 上 clone 源码并安装依赖:

```bash
git clone https://github.com/bs-community/blessing-skin-server.git
cd blessing-skin-server
composer install
yarn
```

构建前端代码！

```bash
yarn build
```

接下来请参考「快速安装向导」进行后续安装。

## 国际化（i18n）

Blessing Skin 可支持多种语言，当前支持英语（`en`）和简体中文（`zh_CN`）。

当然，您也可以添加您自己的语言。请参阅 [Wiki - 添加其它语言 [i18n]](https://github.com/bs-community/blessing-skin-server/wiki/%E6%B7%BB%E5%8A%A0%E5%85%B6%E4%BB%96%E8%AF%AD%E8%A8%80-%5Bi18n%5D)

如果您愿意将您的翻译贡献出来，欢迎参与 [我们的 Crowdin 项目](https://crowdin.com/project/bs-i18n)。

## 问题报告

请参阅 [Wiki - 报告问题的正确姿势](https://github.com/bs-community/blessing-skin-server/wiki/%E6%8A%A5%E5%91%8A%E9%97%AE%E9%A2%98%E7%9A%84%E6%AD%A3%E7%A1%AE%E5%A7%BF%E5%8A%BF)。

## 版权

MIT License

Copyright (c) 2016-present The Blessing Skin Community

程序原作者为 [@printempw](https://blessing.studio/)，转载请注明。
