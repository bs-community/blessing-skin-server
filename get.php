<?php
/**
 * @Author: prpr
 * @Date:   2016-02-02 20:56:42
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-02 21:20:29
 */

$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";

if (isset($_GET['type']) && isset($_GET['uname'])) {
    $user = new user($_GET['uname']);
    if (!$user->is_registered) utils::raise(1, 'Non-existent user.');
    if ($_GET['type'] == "skin") {
        header('Content-Type: image/png');
        echo $user->getBinaryTexture('skin');
    } else if ($_GET['type'] == "cape") {
        header('Content-Type: image/png');
        echo $user->getBinaryTexture('cape');
    } else {
        header('Content-type: application/json');
        echo $user->getJsonProfile();
    }
} else {
    utils::raise(1, 'Illegal parameters.');
}
