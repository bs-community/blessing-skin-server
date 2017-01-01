<p align="center"><img src="https://img.blessing.studio/images/2017/01/01/bs-logo.png"></p>

<p align="center">
<a href="https://github.com/printempw/blessing-skin-server/releases"><img src="https://poser.pugx.org/printempw/blessing-skin-server/version" alt="Latest Stable Version"></a>
<img src="https://img.shields.io/badge/PHP-5.5.9+-orange.svg" alt="PHP 5.5.9+">
<img src="https://poser.pugx.org/printempw/blessing-skin-server/license" alt="License">
<a href="https://twitter.com/printempw"><img src="https://img.shields.io/twitter/follow/printempw.svg?style=social&label=Follow" alt="Twitter Follow"></a>
</p>

Are you puzzled by losing your custom skins in Minecraft servers runing in offline mode? Now you can easily get them back with the help of Blessing Skin Server!

Blessing Skin Server is a web application where you can upload, manage and share your custom skins & capes! Unlike modifying a resource pack, everyone in the game will see the different skins of each other (of course they should register at the same website too).

Blessing Skin Server is an open-source project written in PHP, which means you can deploy it freely on your own web server! Here is a [live demo](http://skin.prinzeugen.net/).

Features
-----------
- Multiple player names can be owned by one user on the website
- Share your skins and capes online with skin library!
- Easy-to-use
    - Visual page for user/player/texture management
    - Detailed option pages
- Security 
    - User passwords will be well encrypted 
    - Email verification for registration (available as plugin)
    - Score system for preventing evil requests
- Extensible
    - Plenty of plugins available 
    - Integration with Authme/CrazyLogin/Discuz

Requirements
-----------
Blessing Skin Server has a few system requirements. In most cases, these PHP extensions are already enabled.

- Web server with URL rewriting enabled
- **PHP >= 5.5.9**
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- GD PHP Extension (for generating CAPTCHA)

Quick Install
-----------
1. Download our [latest release](https://github.com/printempw/blessing-skin-server/releases), extract to where you like to installed on.
2. Rename `.env.example` to `.env` and configure your database information. (For windows users, just rename it to `.env.`, and the last dot will be removed automatically)
3. For Nginx users, add [rewrite rules](#configure-the-web-server) to your Nginx configuration
4. Navigate to `http://your-domain.com/setup` in your browser. If 404 is returned, please check whether the rewrite rules works correctly.
5. Follow the setup wizard and your website is ready-to-go.

Developer Install
------------
If you'd like make some contribution on the project, please deploy it from git first.

**You'd better have some experience on shell operations to continue.**

Clone the code from GitHub and install dependencies:

```
$ git clone https://github.com/printempw/blessing-skin-server.git
$ composer install
$ npm install
$ bower install
```

Build the things!

```
$ gulp
```

Congrats! You made it. More general install docs here in case you got stuck.

Configure the Web Server
------------
For Apache (most of the virtual hosts) and IIS users, there is already a pre-configured file for you. What you need is just to enjoy!

For Nginx users, **please add the following rules** to your Nginx configuration file:

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

![screenshot2](https://img.prinzeugen.net/image.php?di=42U6)

Copyright & License
------------
Copyright (c) 2016 [@printempw](https://prinzeugen.net/) - Released under the GUN General Public License v3.0.
