<?php
/**
 * @Author: printempw
 * @Date:   2016-04-11 17:34:30
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-11 17:35:20
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (Option::get('current_version') == "2.4.2") {
    Option::set('current_version', '2.4.3');
    echo "已成功升级至 v2.4.3";
}
