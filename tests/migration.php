<?php
/**
 * @Author: printempw
 * @Date:   2016-07-24 11:11:30
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-07-24 11:11:44
 */

$db = new \Database('users2');
$sql = "SELECT * FROM `users2` ORDER BY `uid` LIMIT 7250, 1000";
$result = $db->query($sql);
while ($row = $result->fetch_array()) {
    if ($row['hash_steve'] != "") {
        $t = new Models\Texture();
        $t->name = $row['username']."-steve";
        $t->type = "steve";
        $t->hash = $row['hash_steve'];
        $t->size = 0;
        $t->uploader = 0;
        $t->public = "1";
        $t->upload_at = \Utils::getTimeFormatted();
        $t->save();
        echo $row['hash_steve']." saved. <br />";
    }

    if ($row['hash_alex'] != "") {
        $t = new Models\Texture();
        $t->name = $row['username']."-alex";
        $t->type = "alex";
        $t->hash = $row['hash_alex'];
        $t->size = 0;
        $t->uploader = 0;
        $t->public = "1";
        $t->upload_at = \Utils::getTimeFormatted();
        $t->save();
        echo $row['hash_alex']." saved. <br />";
    }

    if ($row['hash_cape'] != "") {
        $t = new Models\Texture();
        $t->name = $row['username']."-cape";
        $t->type = "cape";
        $t->hash = $row['hash_cape'];
        $t->size = 0;
        $t->uploader = 0;
        $t->public = "1";
        $t->upload_at = \Utils::getTimeFormatted();
        $t->save();
        echo $row['hash_cape']." saved. <br />";
    }
}

exit;
