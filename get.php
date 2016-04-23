<?php
/**
 * @Author: printempw
 * @Date:   2016-02-02 20:56:42
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-23 13:02:24
 *
 * All textures requests of legacy link will be handle here.
 */

$dir = dirname(__FILE__);
require "$dir/libraries/autoloader.php";

if (isset($_GET['type']) && isset($_GET['uname'])) {
    // Use for checking rewrite rules when install
    if ($_GET['uname'] == "check_for_rewrite_rules") exit;

    $user = new User($_GET['uname']);
    if (!$user->is_registered) {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        throw new E('Non-existent user.', 1);
    }
    // Cache friendly
    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
                                strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : null;
    // Image bin data
    if ($_GET['type'] == "skin" || $_GET['type'] == "cape") {
        $model_preference = ($user->getPreference() == "default") ? "steve" : "alex";
        $model = (isset($_GET['model']) && $_GET['model'] == "") ? $model_preference : $_GET['model'];
        if ($if_modified_since >= $user->getLastModified()) {
            header('HTTP/1.0 304 Not Modified');
        } else {
            if ($_GET['type'] == "cape") {
                echo $user->getBinaryTexture('cape');
            } else {
                echo $user->getBinaryTexture($model);
            }
        }
    // JSON profile
    } else if ($_GET['type'] == "json") {
        if (isset($_GET['api'])) {
            echo $user->getJsonProfile(($_GET['api'] == 'csl') ? 0 : 1);
        } else {
            echo $user->getJsonProfile(Option::get('api_type'));
        }
    } else if ($_GET['type'] == "avatar") {
        $size = (isset($_GET['size']) && $_GET['size'] != "") ? (int)$_GET['size'] : 128;
        $user->getAvatar($size);
    } else {
        throw new E('Illegal parameters.', 1);
    }
} else {
    throw new E('Illegal parameters.', 1);
}
