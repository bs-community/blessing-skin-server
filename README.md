# Blessing Skin Server

优雅的开源 PHP Minecraft 皮肤站，现已更新至 v3。

注意现在 master 分支的框架已改为 Laravel，旧版请查看分支 v3.0。

![screenshot](https://img.prinzeugen.net/image.php?di=VH7Z)

特性：
-----------
- 支持 [UniSkinAPI](https://github.com/RecursiveG/UniSkinServer/blob/master/doc/UniSkinAPI_zh-CN.md)
- 支持 [CustomSkinLoader API](https://github.com/xfl03/CustomSkinLoaderAPI/blob/master/CustomSkinAPI/CustomSkinAPI_en.md)
- 同时支持旧版样式链接
- ~~支持与 Authme、CrazyLogin、Discuz 等程序进行数据对接~~ V3 的数据对接还在开发中
- 支持一个用户多个角色
- 皮肤库、衣柜功能
- 积分系统，防止用户恶意上传/添加角色
- 完善的用户管理后台以及配置页面
- 多种后台配色
- 可以获取由皮肤生成的头像

面向开发者们的特性：
-----------
- MVC 设计模式，使用强大的 Blade 模板引擎和 Eloquent ORM
- 使用 composer、bower 等包管理器管理依赖
- 几乎所有请求都使用 ajax 发送
- 使用 CSS 预处理器 Sass
- 使用 gulp 作为前端构建工具


环境要求：
-----------
1. 一台支持 URL 重写的主机，Nginx、Apache 或 IIS
2. **PHP 版本 >= 5.5.9**
3. PHP 安装 GD 扩展库
4. 目录的写权限
5. 不支持安装在子目录

快速使用：
-----------
1. 下载发布的打包版源码，重命名 `.env.example` 为 `.env` 并配置你的数据库连接信息（如果是 windows 就重命名为 `.env.`，后面那个点会自动去掉的）
2. 访问 `/setup/index.php` 进行安装
3. 如果你是用的是 Nginx，请配置你的 `nginx.conf` 并加入重写规则
4. 访问你的站点，注册一个新账户或者使用 `安装时所配置的账户` 登录
5. （在数据库的 `users` 表中将你的用户 permission 字段设置为 `1` 即可获取管理员权限， 设置为 `2` 即为超级管理员）
6. 在角色管理面板使用你的 Minecraft 角色名添加一个新角色
7. 在皮肤库上传你的皮肤 & 披风（可设为私有）并添加至衣柜
8. 应用皮肤 & 披风到你的角色
9. 在你所使用的皮肤 Mod 配置文件中加入你的地址
10. 完成啦~

自行构建：
------------
普通用户下载打包版并按照 `快速使用` 中的方法配置即可，但是如果你想要自定义 Blessing Skin Server 的一些内容的话，就需要自己用源码构建啦。

**不推荐不熟悉 shell 操作以及不想折腾的用户使用。**

先从 git 上 clone 源码：

```
$ git clone https://github.com/printempw/blessing-skin-server.git
```

使用 composer 安装 PHP 依赖：

```
$ composer install
```

使用 bower 安装前端依赖库：

```
$ bower install
```

使用 gulp 构建前端代码：

```
$ gulp
```

可以开始使用啦~

服务器配置：
------------
如果你使用 Apache 或者 IIS 作为 web 服务器（大部分的虚拟主机），那么恭喜你，我已经帮你把重写规则写好啦，开箱即用，无需任何配置~

如果你使用 Nginx，请在你的 `nginx.conf` 中加入如下规则**（重要）**：

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Protect .env file
location ~ /\.env
{
    deny all;
}
```

现在你可以访问 `http://example.com/{ player_name }.json` 来得到你的首选 API（可在后台配置）的 JSON 用户数据。另外一个 API 的 JSON 数据可以通过访问 `http://example.com/(usm|csl)/{ player_name }.json` 得到。

上传完皮肤后，你就可以访问 `http://example.com/skin/{ player_name }.png` 得到你的首选模型皮肤啦。 披风图片在这里：`http://example.com/cape/{ player_name }.png` 。

客户端配置：
------------
[Wiki - Mod 配置](https://github.com/printempw/blessing-skin-server/wiki/Mod-%E9%85%8D%E7%BD%AE)

> 顺带一提用户中心有一个自动生成配置的功能哦~

![screenshot2](https://img.prinzeugen.net/image.php?di=42U6)

常见问题：
------------

#### 访问 `example.com/skin/xxx.png ` 404?

请确认你的伪静态（URL 重写）是否配置正确。

#### 500 错误？

本程序使用了一些 PHP 5.4 的新特性，请确保你的 PHP 版本 >= 5.4

#### 游戏中皮肤不显示？

请先确认你的皮肤站 URL 重写规则已经配置正确，并且可以正常获取皮肤图片。

如果还是不能显示皮肤，请阅读您所使用的皮肤 Mod 的 FAQ。

还是不行的话，请在启动器开启调试模式，并且查看所有关于 skin 的日志， CSL 的日志位于 `.minecraft/CustomSkinLoader/CustomSkinLoader.log`。

一般来说看了就可以明白了，如果还是不明白请邮件 [联系我](mailto:h@prinzeugen.net)（带上你的日志）。

版权：
------------
Blessing Skin Server 程序是基于 GUN General Public License v3.0 开放源代码的自由软件，你可以遵照 GPLv3 协议来修改和重新发布这一程序。

程序原作者为 [@printempw](https://prinzeugen.net/)，转载请注明。
