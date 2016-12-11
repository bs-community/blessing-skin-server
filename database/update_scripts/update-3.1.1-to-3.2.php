<?php
/**
 * @Author: printempw
 * @Date:   2016-11-18 16:25:35
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-12-11 22:42:31
 */

Option::set('update_source', config('option')['update_source']);
Option::set('version', '3.2');

return [
    'v3.2 新加入了插件系统，支持的插件请去程序发布帖查看'
];
