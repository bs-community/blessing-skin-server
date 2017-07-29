- <b>简体中文</b>
- [English](https://github.com/printempw/blessing-skin-server/blob/master/README-en.md)

<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://travis-ci.org/printempw/blessing-skin-server"><img src="https://api.travis-ci.org/printempw/blessing-skin-server.svg?branch=master" alt="Travis Building Status"></a>
<a href="https://github.com/printempw/blessing-skin-server/releases"><img src="https://poser.pugx.org/printempw/blessing-skin-server/version" alt="Latest Stable Version"></a>
<img src="https://img.shields.io/badge/PHP-5.5.9+-orange.svg" alt="PHP 5.5.9+">
<img src="https://poser.pugx.org/printempw/blessing-skin-server/license" alt="License">
<a href="https://twitter.com/printempw"><img src="https://img.shields.io/twitter/follow/printempw.svg?style=social&label=Follow" alt="Twitter Follow"></a>
</p>

优雅的开源 Minecraft 皮肤站，现在，回应您的等待。

Blessing Skin Server 是一款能让您上传、管理和分享您的 Minecraft 皮肤和披风的 Web 应用程序。与修改游戏材质包不同的是，所有人都能在游戏中看到各自的皮肤和披风（当然，前提是玩家们要使用同一个皮肤站）。

Blessing Skin Server 是一个开源的 PHP 项目，这意味着您可以自由地在您的服务器上部署它。这里有一个[演示站点](http://skin.prinzeugen.net/)。

特性
-----------
- 支持单用户多个角色
- 通过皮肤库来分享您的皮肤和披风！
- 易于使用
    - 可视化的用户、角色、材质管理页面
    - 详细的站点配置页面
- 安全
    - 只保存 Hash 后的用户密码
    - 注册可要求 Email 验证（以插件的形式实现）
    - 防止恶意请求的积分系统
- 可扩展
    - 多种多样的插件
    - 支持与 Authme/CrazyLogin/Discuz 的数据对接

环境要求
-----------
Blessing Skin Server 对您的服务器有一定的要求。在大多数情况下，下列所需的 PHP 扩展已经开启。

- 一台支持 URL 重写的主机，Nginx、Apache 或 IIS
- **PHP >= 5.5.9** （如果服务器不支持，你可以用 v2.x 版本）
- PHP 的 OpenSSL 扩展
- PHP 的 PDO 扩展
- PHP 的 Mbstring 扩展
- PHP 的 Tokenizer 扩展
- PHP 的 GD 扩展（用于生成验证码）

快速使用
-----------
1. 下载皮肤站的 [最新版本](https://github.com/printempw/blessing-skin-server/releases)，并解压到你想要安装到的位置。
2. 将 `.env.example` 重命名为 `.env` 并**配置你的数据库信息**。（Windows 用户请重命名为 `.env.`，最后的小数点会自动消失）
3. Nginx 用户请添加 [Rewrite 规则](##%E6%9C%8D%E5%8A%A1%E5%99%A8%E9%85%8D%E7%BD%AE) 到你的 Nginx 的配置文件中。
4. 在浏览器中打开 `http://your-domain.com/setup` 。如果出现 404，请检查 Rewrite 规则是否正确并有效。
5. 按照提示执行安装程序

自行构建
------------
如果你想为此项目作贡献，你应该先用 Git 上的代码部署。

**不推荐不熟悉 shell 操作以及不想折腾的用户使用。**

从 Git 上 clone 源码并安装依赖:

```
$ git clone https://github.com/printempw/blessing-skin-server.git
$ composer install
$ yarn install
```

运行自动化测试（可跳过）：

```
$ yarn run test
```

构建代码！

```
$ yarn run build
```

恭喜，构建完成！接下来请参考「快速使用」的第二点进行后续安装。

服务器配置
------------
如果你使用 Apache 或者 IIS 作为 web 服务器（大部分的虚拟主机），那么恭喜你，我已经帮你把重写规则写好啦，开箱即用，无需任何配置~

如果你使用 Nginx，请在你的 nginx.conf 中加入如下规则**（重要）**：

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Protect .env file
location ~ /\.env {
    deny all;
}
```

Mod 配置
------------
请前往 [Wiki - Mod Configuration](https://github.com/printempw/blessing-skin-server/wiki/Mod-Configuration)

![screenshot](https://img.blessing.studio/images/2017/07/29/2017-06-16_15.54.16.png)

FAQ
------------
阅读 [Wiki - FAQ](https://github.com/printempw/blessing-skin-server/wiki/FAQ) 并在报告问题之前再次确认 FAQ 中确实没有提到你的情况。

Bug 报告
------------
请带上你的日志文件（位于 `storage/logs/laravel.log`）联系我。你还应该提供错误发生时服务器的一些信息。Bug 将会被尽快解决。

版权
------------
Blessing Skin Server 程序是基于 GNU General Public License v3.0 开放源代码的自由软件，你可以遵照 GPLv3 协议来修改和重新发布这一程序。

程序原作者为 [@printempw](https://prinzeugen.net/)，转载请注明。
