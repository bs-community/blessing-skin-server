<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 15:03:40
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 21:05:51
 */
require "../libraries/session.inc.php";
if (!$user->is_admin) Utils::redirect('../index.php?msg=看起来你并不是管理员');
$action = isset($_GET['action']) ? $_GET['action'] : "";

$updater = new Updater(Option::get('current_version'));

if ($action == "check" && $updater->newVersionAvailable()) {
    exit(json_encode([
        'new_version_available' => true,
        'latest_version' => $updater->latest_version
    ]));
}

View::show('admin/header', array('page_title' => "检查更新"));

if ($action == "download") {
    View::show('admin/download');
} else {
    View::show('admin/check');
}

View::show('admin/footer'); ?>
