<?php
/**
 * @Author: prpr
 * @Date:   2016-02-02 20:56:42
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-02 21:33:12
 */

$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";

if (isset($_GET['type']) && isset($_GET['uname'])) {
    $user = new user($_GET['uname']);

    if (!$user->is_registered) utils::raise(1, 'Non-existent user.');

    if ($_GET['type'] == "skin") {
        echo $user->getBinaryTexture('skin');
    } else if ($_GET['type'] == "cape") {
        echo $user->getBinaryTexture('cape');
    } else if ($_GET['type'] == "json") {
        echo $user->getJsonProfile();
    } else {
        utils::raise(1, 'Illegal parameters.');
    }
} else {
    utils::raise(1, 'Illegal parameters.');
}
