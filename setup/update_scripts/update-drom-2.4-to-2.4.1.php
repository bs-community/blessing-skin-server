<?php
/**
 * @Author: printempw
 * @Date:   2016-04-04 08:47:35
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-04 08:48:06
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (Option::get('current_version') == "2.4") {
    Option::set('current_version', '2.4.1');
    echo "已成功升级至 v2.4.1";
}
