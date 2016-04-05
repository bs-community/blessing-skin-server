<?php
/**
 * @Author: printempw
 * @Date:   2016-04-05 14:33:00
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-05 14:46:17
 */

if (!defined('BASE_DIR')) exit('请运行 /setup/update.php 来升级');

if (Option::get('current_version') == "2.4.1") {

    if (Option::get('encryption') == "") Option::set('encryption', 'MD5');

    $db = new Database('options');

    $table_name = DB_PREFIX."users";
    $sqls[0] = "ALTER TABLE `$table_name` CHANGE `hash_steve` `hash_steve` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT ''";
    $sqls[1] = "ALTER TABLE `$table_name` CHANGE `hash_alex`  `hash_alex`  VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT ''";
    $sqls[2] = "ALTER TABLE `$table_name` CHANGE `hash_cape`  `hash_cape`  VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT ''";
    $sqls[2] = "ALTER TABLE `$table_name` CHANGE `last_modified` `last_modified` DATETIME DEFAULT CURRENT_TIMESTAMP";

    foreach ($sqls as $sql) {
        $db->query($sql);
    }

    Option::set('current_version', '2.4.2');
    echo "已成功升级至 v2.4.2";
}
