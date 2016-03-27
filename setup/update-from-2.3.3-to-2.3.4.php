<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 15:56:29
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-27 16:30:44
 */
require "../libraries/session.inc.php";

if (!Option::has('current_version') || Option::get('current_version') == "2.3.3") {
    Option::set('current_version', '2.3.4');
    echo "升级成功。";
} else {
    echo "已升级至 v2.3.4，请不要重复运行。";
}
