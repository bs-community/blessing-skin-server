<?php
/**
 * @Author: printempw
 * @Date:   2016-03-18 20:49:48
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-19 09:53:49
 */

class View
{
    public static function show($tpl_name, $data=[]) {
        $filename = BASE_DIR."/templates/".$tpl_name.".tpl.php";
        if (file_exists($filename)) {
            include $filename;
        } else {
            Utils::showErrorPage('2', "未找到模板文件 ".$tpl_name.".tpl.php");
        }
    }
}
