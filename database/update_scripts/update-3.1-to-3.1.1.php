<?php
/**
 * @Author: printempw
 * @Date:   2016-09-28 22:43:44
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-09-28 22:55:29
 */
Option::set('version', '3.1.1');

return [
    '如果你是从 v3.0.x 升级上来的，请进行下列操作：',
    '把 /textures 文件夹移动至 /storage 文件夹中',
    '删除 /config/routes.php，不然会出现奇怪的问题',
    '重新复制一份 .env.example，并适当修改其中的配置（尤其注意 PWD_METHOD 要和以前一样，否则将无法登录）',
];
