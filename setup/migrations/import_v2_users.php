<?php
/**
 * @Author: printempw
 * @Date:   2016-08-18 17:46:19
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-19 22:00:02
 */

if (!defined('BASE_DIR')) exit('Permission denied.');

$v2_table_name = $_POST['v2_table_name'];
$prefix        = Config::getDbConfig()['prefix'];
$v3_users      = $prefix."users";
$v3_players    = $prefix."players";
$v3_closets    = $prefix."closets";

$imported   = 0;
$duplicated = 0;

// use db helper instead of fat ORM
$db = Database::table($v2_table_name, true);

$steps = ceil($db->getRecordNum() / 250);

$score = Option::get('user_initial_score');

// chunked
for ($i = 0; $i <= $steps; $i++) {
    $start = $i * 250;

    $sql = "SELECT * FROM `$v2_table_name` ORDER BY `uid` LIMIT $start, 250";
    $result = $db->query($sql);

    while ($row = $result->fetch_array()) {
        if (!$db->has('player_name', $row['username'], $v3_players)) {
            // generate random nickname
            $nickname = $row['username']."_".time();

            $db->insert([
                'email'        => '',
                'nickname'     => $nickname,
                'score'        => $score,
                'password'     => $row['password'],
                'avatar'       => '0',
                'ip'           => $row['ip'],
                'permission'   => '0',
                'last_sign_at' => Utils::getTimeFormatted(time() - 86400),
                'register_at'  => Utils::getTimeFormatted()
            ], $v3_users);

            $uid = $db->select('nickname', $nickname, null, $v3_users)['uid'];

            $db->insert([
                'uid' => $uid,
                'player_name'   => $row['username'],
                'preference'    => 'steve',
                'last_modified' => Utils::getTimeFormatted()
            ], $v3_players);

            $db->insert([
                'uid'      => $uid,
                'textures' => ''
            ], $v3_closets);

            $imported++;
            // echo $row['username']." saved. <br />";
        } else {
            $duplicated++;
            // echo $row['username']." duplicated. <br />";
        }
    }
}

return [
    'imported' => $imported,
    'duplicated' => $duplicated
];
