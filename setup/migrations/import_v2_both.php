<?php
/**
 * @Author: printempw
 * @Date:   2016-08-18 17:46:19
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-10-16 20:23:12
 */

use App\Models\UserModel;
use App\Models\Player;
use App\Models\Closet;
use App\Models\Texture;

if (!defined('BASE_DIR')) exit('Permission denied.');

$v2_table_name = $_POST['v2_table_name'];
$prefix        = get_db_config()['prefix'];
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

        if (Player::where('player_name', $row['username'])->get()->isEmpty()) {
            $user = new UserModel;

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

                    $res = Texture::where('hash', $row["hash_$model"])->first();

                    if (!$res) {
                        $t = new Texture;
                        // file size in bytes
                        $size = Storage::disk('textures')->has($row["hash_$model"]) ? Storage::disk('textures')->size($row["hash_$model"]) : 0;

                        $t->name      = $name;
                        $t->type      = $model;
                        $t->likes     = 1;
                        $t->hash      = $row["hash_$model"];
                        $t->size      = ceil($size / 1024);
                        $t->uploader  = $user->uid;
                        $t->public    = $public;
                        $t->upload_at = $row['last_modified'] ? : Utils::getTimeFormatted();

                        $t->save();

                        $textures[$model] = $t->tid;

                        $texture_imported++;
                    } else {
                        $textures[$model] = $res->tid;
                        $texture_duplicated++;
                    }
                }
            }

            $p = new Player;

            $p->uid           = $user->uid;
            $p->player_name   = $row['username'];
            $p->preference    = $row['preference'];
            $p->last_modified = $row['last_modified'] ? : Utils::getTimeFormatted();

            $c = new Closet($user->uid);

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

            $c->setTextures(json_encode($items));

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
