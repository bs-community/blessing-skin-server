<?php
/**
 * @Author: printempw
 * @Date:   2016-06-12 11:14:51
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-06-12 11:15:10
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (Option::get('current_version') == "2.4.3") {
    Option::set('current_version', '2.4.4');
    echo "已成功升级至 v2.4.4";
}
