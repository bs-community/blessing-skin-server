- <b>简体中文</b>
- [English](./README_EN.md)

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://circleci.com/gh/bs-community/blessing-skin-server"><img src="https://flat.badgen.net/circleci/github/bs-community/blessing-skin-server" alt="Circle CI Status"></a>
<a href="https://codecov.io/gh/bs-community/blessing-skin-server/branch"><img src="https://flat.badgen.net/codecov/c/github/bs-community/blessing-skin-server" alt="Codecov" /></a>
<a href="https://github.com/bs-community/blessing-skin-server/releases"><img src="https://flat.badgen.net/github/release/bs-community/blessing-skin-server" alt="Latest Stable Version"></a>
<img src="https://flat.badgen.net/badge/PHP/7.2.0+/orange" alt="PHP 7.2.0+">
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
- **PHP >= 7.2.0** [（服务器不支持？）](https://blessing.netlify.com/versions.html)
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

当然，您也可以添加您自己的语言。请参阅 [添加其它语言](https://blessing.netlify.com/i18n.html)

如果您愿意将您的翻译贡献出来，欢迎参与 [我们的 Crowdin 项目](https://crowdin.com/project/bs-i18n)。

## 问题报告

请参阅 [报告问题的正确姿势](https://blessing.netlify.com/report.html)。

## 版权

MIT License

Copyright (c) 2016-present The Blessing Skin Community

程序原作者为 [@printempw](https://blessing.studio/)，转载请注明。
