<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 15:56:29
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 21:23:20
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (!Option::has('current_version') || Option::get('current_version') == "2.3.3") {
    Option::set('current_version', '2.3.4');
    echo "已成功升级至 v2.3.4";
}
