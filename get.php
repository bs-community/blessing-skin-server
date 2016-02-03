<?php
/**
 * @Author: prpr
 * @Date:   2016-02-02 20:56:42
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-03 13:26:30
 */

$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";
require "$dir/config.php";

if (isset($_GET['type']) && isset($_GET['uname'])) {
    $user = new user($_GET['uname']);
    if (!$user->is_registered) utils::raise(1, 'Non-existent user.');

    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : null;

    if ($_GET['type'] == "skin" || $_GET['type'] == "cape") {
        if ($if_modified_since >= $user->getLastModified()) {
            header('HTTP/1.0 304 Not Modified');
        } else {
            echo $user->getBinaryTexture($_GET['type']);
        }
    } else if ($_GET['type'] == "json") {
        echo $user->getJsonProfile();
    } else {
        utils::raise(1, 'Illegal parameters.');
    }
} else {
    utils::raise(1, 'Illegal parameters.');
}
