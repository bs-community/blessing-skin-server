<?php
/**
 * @Author: printempw
 * @Date:   2016-08-09 21:44:13
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-14 08:00:49
 *
 * There are still some coupling relationships here but,
 * Just let it go :)
 */

$v2_table_name = $_POST['v2_table_name'];
$v3_table_name = Config::getDbConfig()['prefix']."textures";

$imported   = 0;
$duplicated = 0;

// use db helper instead of fat ORM
$db = Database::table($v2_table_name, true);

$steps = ceil($db->getRecordNum() / 250);

// chunked
for ($i = 0; $i <= $steps; $i++) {
    $start = $i * 250;

    $sql = "SELECT * FROM `$v2_table_name` ORDER BY `uid` LIMIT $start, 250";
    $result = $db->query($sql);

    while ($row = $result->fetch_array()) {
        // compile patterns
        $name = str_replace('{username}', $row['username'], $_POST['texture_name_pattern']);

        if ($row['hash_steve'] != "") {
            $name = str_replace('{model}', 'steve', $name);

            if (!$db->has('hash', $row['hash_steve'], $v3_table_name)) {
                $db->insert([
                    'name'      => $name,
                    'type'      => 'steve',
                    'likes'     => 0,
                    'hash'      => $row['hash_steve'],
                    'size'      => 0,
                    'uploader'  => $_POST['uploader_uid'],
                    'public'    => '1',
                    'upload_at' => Utils::getTimeFormatted()
                ], $v3_table_name);

                $imported += 1;
                // echo $row['hash_steve']." saved. <br />";
            } else {
                $duplicated += 1;
                // echo $row['hash_steve']." duplicated. <br />";
            }
        }

        if ($row['hash_alex'] != "") {
            $name = str_replace('{model}', 'alex', $name);

            if (!$db->has('hash', $row['hash_alex'], $v3_table_name)) {
                $db->insert([
                    'name'      => $name,
                    'type'      => 'alex',
                    'likes'     => 0,
                    'hash'      => $row['hash_alex'],
                    'size'      => 0,
                    'uploader'  => $_POST['uploader_uid'],
                    'public'    => '1',
                    'upload_at' => Utils::getTimeFormatted()
                ], $v3_table_name);

                $imported += 1;
                // echo $row['hash_alex']." saved. <br />";
            } else {
                $duplicated += 1;
                // echo $row['hash_alex']." duplicated. <br />";
            }
        }

        if ($row['hash_cape'] != "") {
            $name = str_replace('{model}', 'cape', $name);

            if (!$db->has('hash', $row['hash_cape'], $v3_table_name)) {
                $db->insert([
                    'name'      => $name,
                    'type'      => 'cape',
                    'likes'     => 0,
                    'hash'      => $row['hash_cape'],
                    'size'      => 0,
                    'uploader'  => $_POST['uploader_uid'],
                    'public'    => '1',
                    'upload_at' => Utils::getTimeFormatted()
                ], $v3_table_name);

                $imported += 1;
                // echo $row['hash_cape']." saved. <br />";
            } else {
                $duplicated += 1;
                // echo $row['hash_cape']." duplicated. <br />";
            }
        }
    }
}

return [
    'imported' => $imported,
    'duplicated' => $duplicated
];
