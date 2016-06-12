<?php
/**
 * @Author: printempw
 * @Date:   2016-06-12 11:14:19
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-06-12 11:15:00
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (Option::get('current_version') == "2.4.4") {
    Option::set('current_version', '2.4.5');
    echo "已成功升级至 v2.4.5";
}
