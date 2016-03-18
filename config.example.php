<?php
/* MySQL 数据库名 */
define('DB_NAME', 'skin');

/* MySQL 数据库用户名 */
define('DB_USER', 'root');

/* MySQL 连接密码 */
define('DB_PASSWD', 'root');

/* MySQL 端口，默认 3306 */
define('DB_PORT', 3306);

/* MySQL 主机 */
define('DB_HOST', 'localhost');

/**
 * 数据表前缀
 *
 * 如果您有在同一数据库内安装多个 Blessing Skin Server 的需求，
 * 或者需要与 Authme、Discuz 等程序对接时，请为每个皮肤站设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
define('DB_PREFIX', '');

/* 盐，用于 token 加密，修改为任意随机字符串 */
define('SALT', '9tvsE+1._%R4@VLaX(I|.U+h_d*s');

/* 调试模式，开启后将会显示所有错误提示 */
define('DEBUG_MODE', false);
