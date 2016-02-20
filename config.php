<?php
/* MySQL 数据库名 */
define('DB_NAME', 'skin');

/* MySQL 用户名 */
define('DB_USER', 'root');

/* MySQL 连接密码 */
define('DB_PASSWD', 'root');

/* MySQL 主机 */
define('DB_HOST', 'localhost');

/* 盐，用于 token 加密，修改为任意随机字符串 */
define('SALT', '9tvsh55d*s');

/* 同一 IP 最大可注册账户数 */
define('REGS_PER_IP', 2);

/* 优先使用的 Json API，0 为 CustomSkinLoader API, 1 为 UniSkinAPI */
define('API_TYPE', 1);

/* 站点名称，推荐英文（字体原因） */
define('SITE_TITLE', 'Blessing Skin Server');
