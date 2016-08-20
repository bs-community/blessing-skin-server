<?php
/**
 * @Author: printempw
 * @Date:   2016-08-09 21:44:13
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-20 20:49:37
 *
 * There are still some coupling relationships here but,
 * Just let it go :)
 */

if (!defined('BASE_DIR')) exit('Permission denied.');

$v2_table_name = $_POST['v2_table_name'];
$v3_table_name = Config::getDbConfig()['prefix']."textures";

$imported   = 0;
$duplicated = 0;

// use db helper instead of fat ORM
$db = Database::table($v2_table_name, true);

$steps = ceil($db->getRecordNum() / 250);

$public = isset($_POST['import_as_private']) ? '0' : '1';

// chunked (optionally)
for ($i = 0; $i <= $steps; $i++) {
    $start = $i * 250;

    $sql = "SELECT * FROM `$v2_table_name` ORDER BY `uid` LIMIT $start, 250";
    $result = $db->query($sql);

    while ($row = $result->fetch_array()) {
        // compile patterns
        $name = str_replace('{username}', $row['username'], $_POST['texture_name_pattern']);

        $models = ['steve', 'alex', 'cape'];

        foreach ($models as $model) {
            if ($row['hash_steve'] != "") {
                $name = str_replace('{model}', $model, $name);

                if (!$db->has('hash', $row["hash_$model"], $v3_table_name)) {
                    $db->insert([
                        'name'      => $name,
                        'type'      => $model,
                        'likes'     => 0,
                        'hash'      => $row["hash_$model"],
                        'size'      => Storage::size(BASE_DIR.'/textures/'.$row["hash_$model"]),
                        'uploader'  => $_POST['uploader_uid'],
                        'public'    => $public,
                        'upload_at' => Utils::getTimeFormatted()
                    ], $v3_table_name);

                    $imported++;
                    // echo $row['hash_steve']." saved. <br />";
                } else {
                    $duplicated++;
                    // echo $row['hash_steve']." duplicated. <br />";
                }
            }
        }
    }
}

return [
    'imported' => $imported,
    'duplicated' => $duplicated
];
