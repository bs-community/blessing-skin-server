<?php
/**
 * @Author: printempw
 * @Date:   2016-08-18 17:46:19
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-08-20 20:49:29
 */

if (!defined('BASE_DIR')) exit('Permission denied.');

$v2_table_name = $_POST['v2_table_name'];
$prefix        = Config::getDbConfig()['prefix'];
$v3_users      = $prefix."users";
$v3_players    = $prefix."players";
$v3_closets    = $prefix."closets";
$v3_textures   = $prefix."textures";

$user_imported      = 0;
$user_duplicated    = 0;
$texture_imported   = 0;
$texture_duplicated = 0;

// use db helper instead of fat ORM in some operations :(
$db = Database::table($v2_table_name, true);

$steps = ceil($db->getRecordNum() / 250);

$score = Option::get('user_initial_score');

$public = isset($_POST['import_as_private']) ? '0' : '1';

// chunked (optionally)
for ($i = 0; $i <= $steps; $i++) {
    $start = $i * 250;

    $sql = "SELECT * FROM `$v2_table_name` ORDER BY `uid` LIMIT $start, 250";
    $result = $db->query($sql);

    while ($row = $result->fetch_array()) {
        // compile patterns
        $name = str_replace('{username}', $row['username'], $_POST['texture_name_pattern']);

        if (!$db->has('player_name', $row['username'], $v3_players)) {
            $user = new App\Models\UserModel;

            $user->email        = '';
            $user->nickname     = $row['username'];
            $user->score        = $score;
            $user->password     = $row['password'];
            $user->avatar       = '0';
            $user->ip           = $row['ip'];
            $user->permission   = '0';
            $user->last_sign_at = Utils::getTimeFormatted(time() - 86400);
            $user->register_at  = Utils::getTimeFormatted();

            $user->save();

            $models = ['steve', 'alex', 'cape'];

            $textures = [];

            foreach ($models as $model) {
                if ($row["hash_$model"] != "") {
                    $name = str_replace('{model}', $model, $name);

                    if (!$db->has('hash', $row["hash_$model"], $v3_textures)) {
                        $t = new App\Models\Texture;

                        $t->name      = $name;
                        $t->type      = $model;
                        $t->likes     = 1;
                        $t->hash      = $row["hash_$model"];
                        $t->size      = Storage::size(BASE_DIR.'/textures/'.$row["hash_$model"]);
                        $t->uploader  = $user->uid;
                        $t->public    = $public;
                        $t->upload_at = $row['last_modified'] ? : Utils::getTimeFormatted();

                        $t->save();

                        $textures[$model] = $t->tid;

                        $texture_imported++;
                    } else {
                        $texture_duplicated++;
                    }
                }
            }

            $p = new App\Models\PlayerModel;

            $p->uid           = $user->uid;
            $p->player_name   = $row['username'];
            $p->preference    = $row['preference'];
            $p->last_modified = $row['last_modified'] ? : Utils::getTimeFormatted();

            $c = new App\Models\ClosetModel;

            $c->uid      = $user->uid;
            $c->textures = '';

            $items = [];

            foreach ($textures as $model => $tid) {
                $property = "tid_$model";
                $p->$property = $tid;

                $items[] = array(
                    'tid'    => $tid,
                    'name'   => $name,
                    'add_at' => $row['last_modified'] ? : Utils::getTimeFormatted()
                );
            }

            $c->textures = json_encode($items);

            $p->save();
            $c->save();

            $user_imported++;
            // echo $row['username']." saved. <br />";
        } else {
            $user_duplicated++;
            // echo $row['username']." duplicated. <br />";
        }


    }
}

return [
    'user' => [
        'imported' => $user_imported,
        'duplicated' => $user_duplicated
    ],
    'texture' => [
        'imported' => $texture_imported,
        'duplicated' => $texture_duplicated
    ]
];
