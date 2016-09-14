<?php
/**
 * Migrations Bootstrap of Blessing Skin Server
 */

require dirname(__DIR__)."/includes/bootstrap.php";

// If already installed
if (!check_table_exists()) {
    redirect_to('../index.php');
}

if (isset($_COOKIE['uid']) && isset($_COOKIE['token'])) {
    $_SESSION['uid'] = $_COOKIE['uid'];
    $_SESSION['token'] = $_COOKIE['token'];
}

// check permission
if (isset($_SESSION['uid'])) {
    $user = new App\Models\User($_SESSION['uid']);

    if ($_SESSION['token'] != $user->getToken())
        redirect_to('../../auth/login', '无效的 token，请重新登录~');

    if ($user->getPermission() != "2")
        abort(403, '此页面仅超级管理员可访问');

} else {
    redirect_to('../../auth/login', '非法访问，请先登录');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'index':
        View::show('setup.migrations.index');
        break;

    case 'import-v2-textures':
        View::show('setup.migrations.import-v2-textures');
        break;

    case 'import-v2-users':
        View::show('setup.migrations.import-v2-users');
        break;

    case 'import-v2-both':
        View::show('setup.migrations.import-v2-both');
        break;

    default:
        throw new App\Exceptions\PrettyPageException('非法参数', 1, true);
        break;
}

Session::save();
