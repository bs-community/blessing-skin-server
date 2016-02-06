<?php
/**
 * @Author: prpr
 * @Date:   2016-02-02 20:56:42
 * @Last Modified by:   prpr
 * @Last Modified time: 2016-02-06 21:51:19
 *
 * All textures requests of legacy link will be handle here.
 */

$dir = dirname(__FILE__);
require "$dir/includes/autoload.inc.php";

if (isset($_GET['type']) && isset($_GET['uname'])) {
    $user = new user($_GET['uname']);
    if (!$user->is_registered) utils::raise(1, 'Non-existent user.');
    // Cache friendly
    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : null;

    if ($_GET['type'] == "skin" || $_GET['type'] == "cape") {
        $model_preferrnce = ($user->getPreference() == "default") ? "steve" : "alex";
        $model = (isset($_GET['model']) && $_GET['model'] == "") ? $model_preferrnce : $_GET['model'];
        if ($if_modified_since >= $user->getLastModified()) {
            header('HTTP/1.0 304 Not Modified');
        } else {
            if ($_GET['type'] == "cape") {
                echo $user->getBinaryTexture('cape');
            } else {
                echo $user->getBinaryTexture($model);
            }
        }
    } else if ($_GET['type'] == "json") {
        if (isset($_GET['api'])) {
            echo $user->getJsonProfile(($_GET['api'] == 'csl') ? 0 : 1);
        } else {
            echo $user->getJsonProfile(API_TYPE);
        }
    } else {
        utils::raise(1, 'Illegal parameters.');
    }
} else {
    utils::raise(1, 'Illegal parameters.');
}
