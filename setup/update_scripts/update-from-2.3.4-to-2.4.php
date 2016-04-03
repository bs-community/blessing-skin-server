<?php
/**
 * @Author: printempw
 * @Date:   2016-04-03 19:48:02
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 21:23:17
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (Option::get('current_version') == "2.3.4") {
    Option::add('upload_max_size',   '1024');
    Option::add('custom_css',        '');
    Option::add('custom_js',         '');
    Option::add('google_font_cdn',   'moefont');
    Option::add('user_default_skin', '');
    Option::add('encryption',        'MD5');
    Option::add('update_url',        'https://work.prinzeugen.net/update.json');

    Option::set('current_version', '2.4');
    echo "已成功升级至 v2.4";
}
