# Blessing Skin Server

Blessing Skin Server is a web application for storing and managing skins in Minecraft. You can get your skin back easily even in an offline Minecraft server with Blessing Skin Server.

The framework used by this project is Laravel. Older versions of Blessing Skin Server are available on other branches.

中文版 README 在[这里](https://github.com/printempw/blessing-skin-server/wiki/README---zh_CN)。

![screenshot](https://img.prinzeugen.net/image.php?di=VH7Z)

Feature
-----------
- Support [UniSkinAPI](https://github.com/RecursiveG/UniSkinServer/blob/master/doc/UniSkinAPI_zh-CN.md), [CustomSkinLoader API](https://github.com/xfl03/CustomSkinLoaderAPI/blob/master/CustomSkinAPI/CustomSkinAPI_en.md) and legacy links
- Adapt to Authme, CrazyLogin, Discuz and so on
- One user, many players
- Skin library and user closets
- Score system to provent evil requests
- Easy-using user management and option pages
- Many color schemes
- Avatars generated from skins

Requirements
-----------
Blessing Skin Server has a few system requirements.

1. Web server which supports URL rewriting
2. **PHP version >= 5.5.9**
3. GD PHP Extension
4. Writeable directory

Quick Start Install
-----------
1. Download the latest release and unzip it to the location you want to install
2. Rename `.env.example` to `.env` and configure your database there.(For windows, just rename it to `.env.`, the last dot will be removed automatically)
3. For Nginx users, add rewrite rules to your `nginx.conf`
4. Access `/setup/index.php` in your browser
5. Congratulations! Upload your skins and have fun!

Developer Install
------------
Download and deploy from git only if you want to modify something in Blessing Skin Server.

**You'd better have some experience on shell to continue.**

Clone the code from GitHub:

```
$ git clone https://github.com/printempw/blessing-skin-server.git
```

Install php dependencies using composer:

```
$ composer install
```

Install front-end dependencies using bower:

```
$ bower install
```

Build the things!

```
$ gulp
```

Congrats! You made it. More general install docs here in case you got stuck.

Configure the Web Server
------------
For Apache(most of the virtual hosts) and IIS users, there is already some pre-configured files for you, and what you need to do is just to enjoy!

For Nginx users, **please add the below rules** to your `nginx.conf` :

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Protect .env file
location ~ /\.env {
    deny all;
}
```

Mod Configuration
------------
See [Wiki - Mod Configuration](https://github.com/printempw/blessing-skin-server/wiki/Mod-Configuration)

> BTW, generating configs is available at user center~

![screenshot2](https://img.prinzeugen.net/image.php?di=42U6)

Copyright & License
------------
Copyright (c) 2016 [@printempw](https://prinzeugen.net/) - Released under the GUN General Public License v3.0.
